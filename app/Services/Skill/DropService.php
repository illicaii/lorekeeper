<?php

namespace App\Services\Skill;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Skill\Skill;
use App\Models\Character\CharacterSkill;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class DropService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Drop Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of Drop (Item Grant) type skills.
    |
    */

    /**
     * Retrieves any data that should be used in the skill tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        return [
            'currencies' => Currency::where('is_character_owned', 1)->orderBy('sort_character', 'DESC')->pluck('name', 'id'),
            'items'      => Item::orderBy('name')->pluck('name', 'id'),
            'tables'     => LootTable::orderBy('name')->pluck('name', 'id'),
            'skills'     => Skill::orderBy('id')->pluck('name', 'id'),
        ];
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param object $tag
     *
     * @return mixed
     */
    public function getTagData($tag) {
        if (!isset($tag->data)) {
            return null;
        }
        $data = [];
        foreach ($tag->data as $breakpoint){
            $rewards = [];
            $assets = parseAssetData($breakpoint['rewards']);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id'   => $id,
                        'quantity'        => $asset['quantity'],
                    ];
                }
            }
            $data[] = [
                'id'           => $breakpoint['id'],
                'min_lvl'      => $breakpoint['min_lvl'],
                'max_lvl'      => $breakpoint['max_lvl'],
                'charges'      => $breakpoint['charges'],
                'rewards'      => $rewards,
                ];
        }
        return $data;
    }

    /**
     * Processes the data attribute of the tag and returns it in the preferred format.
     *
     * @param object $tag
     * @param array  $data
     *
     * @return bool
     */
    public function updateData($tag, $data) {
        DB::beginTransaction();

        try {
            if (isset($data['breakpoint_id']) && $data['breakpoint_id'] != 0) {
                foreach ($data['breakpoint_id'] as $key=>$id) {
                    if ($id == null) {
                        // large number temp key. this will cause problems if you have 9999 or more breakpoints but also thats a lot of level breakpoints what are you doing
                        $id = 9999;
                    }
                    // The data will be stored as an asset table, json_encoded.
                    // First build the asset table, then prepare it for storage.
                    $assets = createAssetsArray();
                    if (isset($data['sublist_id'])){
                        foreach ($data['sublist_id'] as $sub_key => $r){
                            if (isset($data['rewardable_type'][$sub_key]) && $data['sublist_id'][$sub_key] == $id){
                                switch ($data['rewardable_type'][$sub_key]) {
                                    case 'Item':
                                        $type = 'App\Models\Item\Item';
                                        break;
                                    case 'Currency':
                                        $type = 'App\Models\Currency\Currency';
                                        break;
                                    case 'LootTable':
                                        $type = 'App\Models\Loot\LootTable';
                                        break;
                                    case 'Raffle':
                                        $type = 'App\Models\Raffle\Raffle';
                                        break;
                                }
                                $asset = $type::find($data['rewardable_id'][$sub_key]);
                                addAsset($assets, $asset, $data['quantity'][$sub_key]);
                            }
                        }
                    }
                    $assets = getDataReadyAssets($assets);

                    $data['data'][($key)] = [
                        'id'           => $id,
                        'min_lvl'      => $data['min_lvl'][$key],
                        'max_lvl'      => $data['max_lvl'][$key],
                        'charges'      => $data['charges'][$key],
                        'rewards'      => $assets,
                    ];
                }
            } else {
                return true;
            }

            $tag->update(['data' => json_encode($data['data'])]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the skill when used.
     *
     * @param mixed $ability
     * @param mixed $user
     * @param mixed $character
     * @param mixed $tag
     *
     * @return bool
     */
    public function act($ability, $user, $character, $tag) {
        DB::beginTransaction();

        try {
            // Check to make sure the user clicking the button is the owner of the character
            if ($character->user_id != $user->id) {
                throw new \Exception('This character does not belong to you.');
            }

            $recipient_stack = CharacterSkill::where([
                ['character_image_id', '=', $character->image->id],
                ['skill_id', '=', $ability->skill->id],
            ])->first();

            //Check if character skill has charges available
            $usableCharges = $ability->getAvailableCharges() - $ability->charges;
            $characterLevel = $ability->getlevel();
            if ($usableCharges > 0) {
                // Calculate reward pool
                $rewards = $this->getDropRewards($usableCharges, $characterLevel, $tag->data);

                //Distribute rewards and increase charge count
                if (!$rewards = fillCharacterAssets($rewards, $user, $character, 'Skill Drop', [
                    'data' => 'Received drop from '. $ability->skill->name,
                ])) {
                    throw new \Exception('Failed to use ability');
                }
                $recipient_stack->charges = $ability->getAvailableCharges();
                $recipient_stack->save();

            }
            DB::commit();
            return $this->getDropRewardsString(isset($rewards) ? $rewards : null);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Calculates what rewards should be dropped. This will always award 1 set of rewards (this is intentional)
     *
     * @param mixed $charges
     * @param mixed $level
     * @param mixed $data
     *
     * @return string
     */
    private function getDropRewards($charges, $level, $data) {
        $rewards = createAssetsArray(true);
        $rewardBatch = createAssetsArray(true);
        $cost = 0;
        //figure out what breakpoint the character meets
        foreach ($data as $breakpoint){
            if($level >= $breakpoint['min_lvl'] && $level < $breakpoint['max_lvl'] ){
                $cost += $breakpoint['charges'];
                $rewardBatch = mergeAssetsArrays($rewardBatch, parseAssetData($breakpoint['rewards'], true), true);
            }
        }
        // Duplicate reward string based on charges.
        $i = $charges;
        do {
            $rewards = mergeAssetsArrays($rewards, $rewardBatch, true);
            $i -= $cost;
        } while ($i > 0);

        return $rewards;
    }

    /**
     * Acts upon the skill when used.
     *
     * @param array $rewards
     *
     * @return string
     */
    private function getDropRewardsString($rewards) {
        if ($rewards == null){
            return 'Character has received: Nothing';

        }
        return 'Character has received: '.createRewardsString($rewards);
    }
}
