<?php

namespace App\Services\Skill;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Loot\LootTable;
use App\Models\Skill\Skill;
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

        return $tag->data;
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
                    $rewards = [
                        'rewardable_type' => [],
                        'rewardable_id'   => [],
                        'quantity'        => [],
                        'charges'         => [],
                    ];
                    if (isset($data['sublist_id'])) {
                        foreach ($data['sublist_id'] as $sub_key => $r) {
                            if (isset($data['rewardable_type'][$sub_key]) && $data['sublist_id'][$sub_key] == $id) {
                                $rewards['rewardable_type'][] = $data['rewardable_type'][$sub_key];
                                $rewards['rewardable_id'][] = $data['rewardable_id'][$sub_key];
                                $rewards['quantity'][] = $data['quantity'][$sub_key];
                                $rewards['charges'][] = $data['charges'][$sub_key];
                            }
                        }
                    }
                    $data['data'][($key)] = [
                        'id'           => $id,
                        'min_lvl'      => $data['min_lvl'][$key],
                        'max_lvl'      => $data['max_lvl'][$key],
                        'rewards'      => $rewards,
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

            // Check if character skill has charges available
            $usableCharges = $ability->getTotalCharges() - $ability->charges;
            $characterLevel = $ability->getlevel();
            if ($usableCharges > 0) {
                // Calculate reward pool
                $rewards = $this->getDropRewards($usableCharges, $characterLevel, $tag->data);

                // Distribute rewards and increase charge count
                // if (!$rewards = fillCharacterAssets(parseAssetData($rewards), $user, $character, 'Skill Drop', [
                //     'data' => 'Received drop from'. $character->name,
                // ])) {
                //     throw new \Exception('Failed to use ability');
                // }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Calculates what rewards should be dropped.
     *
     * @param mixed $charges
     * @param mixed $level
     * @param mixed $data
     *
     * @return string
     */
    private function getDropRewards($charges, $level, $data) {
        $rewards = [];
        $rewardBatch = [];
        $cost = 0;
        // figure out what breakpoint the character meets
        foreach ($data as $breakpoint) {
            if ($level >= $breakpoint['min_level'] && $level < $breakpoint['max_level']) {
                $cost += 1; // todo make every cost 1 just to test
            }
            $rewardBatch += $breakpoint['rewards'];
        }

        // Duplicate reward string based on charges.
        // This will always award 1 set of rewards (this is intentional)
        $i = $charges;
        do {
            $rewards += $rewardBatch;
            $i -= $cost;
        } while ($i > 0);

        // clean rewards array

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
        return 'You have received: '.createRewardsString($rewards);
    }
}
