<?php

namespace App\Services\Item;

use App\Models\Skill\Skill;
use App\Models\Character\CharacterSkill;
use App\Models\Character\Character;
use App\Services\InventoryManager;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class SkillService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Skill Service
    |--------------------------------------------------------------------------
    |
    | Handles the editing and usage of skill type items.
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        $item_types = ['0' => 'Skill Grant or Add XP/Level','1' => 'Set XP/Level', '2' => 'Reset XP/level', '3' => 'Remove Skill'];
        $grant_types = ['0' => 'Grant Single (Selector)','1' => 'Grant Random','2' => 'Grant All'];

        return [
            'skills' => Skill::orderBy('id')->pluck('name', 'id'),
            'item_types' => $item_types,
            'grant_types' => $grant_types,
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
        if (!isset($tag->data)){
            return null;
        }

        $rewards = [];
        if ($tag->data) {
            $assets = parseAssetData($tag->data);
            foreach ($assets as $type => $a) {
                $class = getAssetModelString($type, false);
                foreach ($a as $id => $asset) {
                    $rewards[] = (object) [
                        'rewardable_type' => $class,
                        'rewardable_id'   => $id,
                        'quantity'        => $asset['quantity'],
                    ];
                    $skill_opt[] = ['skill_id' => $id];
                }
            }
        }

        return [
            'skill_item_type' => $tag->data['skill_item_type'],
            'grant_level' => $tag->data['is_lvl'],
            'grant_type' => $tag->data['grant_type'],
            'error_on_missing' => $tag->data['error_on_missing'],
            'rewards' => $rewards,
            'skill_opt' => $skill_opt,
        ];
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
            // If there's no data, return.
            if (!isset($data['rewardable_id'])) {
                throw new \Exception('No Skills Added');
            }

            switch ($data['skill_item_type']) {
                case 0:       //GRANT Skill/XP/Level (fallthrough)
                case 1:       //SET XP or Level
                    // The data will be stored as an asset table, json_encode()d.
                    // First build the asset table, then prepare it for storage.
                    $assets = createAssetsArray();
                    foreach ($data['rewardable_id'] as $key => $r) {
                        $asset = Skill::find($data['rewardable_id'][$key]);
                        addAsset($assets, $asset, $data['quantity'][$key]);
                    }
                    $assets = getDataReadyAssets($assets);
                    if (isset($data['grant_level'])){
                        $assets += ['is_lvl' => $data['grant_level']];
                    }
                    break;
                case 2:       //REST XP/level
                    // build the asset table
                    $assets = createAssetsArray();
                    foreach ($data['rewardable_id'] as $key => $r) {
                        $asset = Skill::find($data['rewardable_id'][$key]);
                        addAsset($assets, $asset, 0);
                    }
                    $assets = getDataReadyAssets($assets);
                    break;
                case 3:       //REMOVE Skills
                    // build the asset table
                    $assets = createAssetsArray();
                    foreach ($data['rewardable_id'] as $key => $r) {
                        $asset = Skill::find($data['rewardable_id'][$key]);
                        addAsset($assets, $asset, 0);
                    }
                    $assets = getDataReadyAssets($assets);
                    break;
                default:
                    throw new \Exception('No Skill Item Type Selected.');
                    break;
            }
            $assets += ['skill_item_type' => $data['skill_item_type']];
            $assets += ['grant_type' => $data['grant_type']];
            $assets += ['error_on_missing' => $data['error_on_missing']];

            $tag->update(['data' => json_encode($assets)]);

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param \App\Models\User\UserItem $stacks
     * @param \App\Models\User\User     $user
     * @param array                     $data
     *
     * @return bool
     */
    public function act($stacks, $user, $data) {
        DB::beginTransaction();

        try {
            $firstData = $stacks->first()->item->tag('skill')->data;
            $character = Character::where('id', $data['character_id'])->get()->first();

            // Check instant failures
            if (!isset($firstData['skills'])) {
                throw new \Exception('Item has no applicable skills');
            } elseif ($character->user->id != $user->id) {
                throw new \Exception('You do not own this character');
            }

            // Check if character has selected skill
            if($firstData['grant_type'] == 0 && count($firstData['skills']) > 1 && $firstData['error_on_missing']){
                $character_skill = CharacterSkill::where([
                    ['character_image_id', '=', $character->image->id],
                    ['skill_id', '=', $data['selected_skill']],
                ])->first();
                if (!isset($character_skill)){
                    throw new \Exception('Character does not know selected skill');
                }
            }

            // Check if character selected has at least one skill in this item
            $options = Skill::find(array_keys($firstData['skills']))->pluck('id');
            $has_option = !$firstData['error_on_missing'];
            $learned_skills = [];
            foreach ($options as $skill){
                $character_skill = CharacterSkill::where([
                    ['character_image_id', '=', $character->image->id],
                    ['skill_id', '=', $skill],
                ])->first();
                if ($character_skill){
                    $has_option = true;
                    $learned_skills += [$skill => $firstData['skills'][$skill]];
                }
            }
            if (!$has_option){
                throw new \Exception('Character has none of the applicable skills');
            }

            foreach ($stacks as $key => $stack) {
                // Check to make sure the owner of the box is the one opening it
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                //Try to delete the box item. If successful, we can start distributing rewards.
                $total_rewards = [];
                if ((new InventoryManager)->debitStack($stack->user, 'Skill Item Redeemed', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        // Pick skills to give to character based on grant type
                        if($firstData['grant_type'] == 0 && count($firstData['skills']) > 1){
                            //grant skill that was selected
                            $skillOption['skills'] = [$data['selected_skill'] => $firstData['skills'][$data['selected_skill']]];
                        } elseif ($firstData['grant_type'] == 1) {
                            //grant random skill
                            if (!$firstData['error_on_missing']){
                                // if we are granting the skill as well, we can pull from the whole list
                                $random = array_rand($firstData['skills']);
                            } else {
                                $random = array_rand($learned_skills);
                            }
                            $skillOption['skills'] = [$random => $firstData['skills'][$random]];
                        } else {
                            //grant all skills
                            $skillOption = $stacks->first()->item->tag('skill')->data;
                        }

                        if (!$rewards = fillCharacterAssets(parseAssetData($skillOption), $stack->user, $character, 'Skill Redemption', [
                            'data' => 'Redeemed from '.$stack->item->name,
                            'is_lvl' => $firstData['is_lvl'],
                        ])) {
                            throw new \Exception("Failed to redeem skill Items. Can not decrease character's skill level below 0 or increase above max");
                        } else {
                            $total_rewards[$q] = $rewards;
                        }
                    }
                }
                //Flash all rewards now that we know stack operation succeeds
                foreach ($total_rewards as $reward) {
                    flash($this->getSkillRewardsString($reward));
                }
            }
            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Acts upon the item when used from the inventory.
     *
     * @param array $rewards
     *
     * @return string
     */
    private function getSkillRewardsString($rewards) {
        $results = 'You have learned or earned xp for the following skill: ';
        $result_elements = [];
        foreach ($rewards as $assetType) {
            if (isset($assetType)) {
                foreach ($assetType as $asset) {
                    array_push($result_elements, $asset['asset']->displayName);
                }
            }
        }

        return $results.implode(', ', $result_elements);
    }
}
