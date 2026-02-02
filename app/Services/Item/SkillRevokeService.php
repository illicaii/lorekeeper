<?php

namespace App\Services\Item;

use App\Enums\Skill\ItemType;
use App\Enums\Skill\ItemGrantType;
use App\Models\Character\Character;
use App\Models\Character\CharacterSkill;
use App\Models\Skill\Skill;
use App\Services\InventoryManager;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class SkillRevokeService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Skill Service
    |--------------------------------------------------------------------------
    |
    | Handles the reset and deletion of skills through items
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        $item_types = [ItemType::RESET->value => 'Reset Level', ItemType::REVOKE->value => 'Remove Skill'];
        $grant_types = [ItemGrantType::SELECTOR->value => 'Remove Single (Selector)', ItemGrantType::ALL->value => 'Remove All'];

        return [
            'skills'      => Skill::orderBy('id')->pluck('name', 'id'),
            'item_types'  => $item_types,
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
        if (!isset($tag->data)) {
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
            'skill_item_type'  => $tag->data['skill_item_type'],
            'grant_type'       => $tag->data['grant_type'],
            'rewards'          => $rewards,
            'skill_opt'        => $skill_opt,
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

            // Build the asset table
            $assets = createAssetsArray();
            foreach ($data['rewardable_id'] as $key => $r) {
                $asset = Skill::find($data['rewardable_id'][$key]);
                addAsset($assets, $asset, 0);
            }
            $assets = getDataReadyAssets($assets);
            $assets += ['remove' => ($data['skill_item_type'] == ItemType::REVOKE->value)]; //True if REVOKE, False if RESET
            $assets += ['skill_item_type' => $data['skill_item_type']];
            $assets += ['grant_type' => $data['grant_type']];

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
            $firstData = $stacks->first()->item->tag('skill_revoke')->data;
            $character = Character::where('id', $data['character_id'])->get()->first();

            // Check instant failures
            if (!isset($firstData['skills'])) {
                throw new \Exception('Item has no applicable skills');
            } elseif ($character->user->id != $user->id) {
                throw new \Exception('You do not own this character');
            }

            //set reason to be passed to logging
            if($firstData['skill_item_type'] == ItemType::RESET->value){
                $reason = 'Item based Reset';
            } else {
                $reason = 'Item based Removal';
            }

            foreach ($stacks as $key => $stack) {
                // Check to make sure the owner of the box is the one opening it
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                } else if ($data['quantities'][$key] > 1){
                    throw new \Exception('You can not use more than one of this item at a time.');
                }

                //Try to delete the box item. If successful, we can start distributing rewards.
                $total_rewards = [];
                if ((new InventoryManager)->debitStack($stack->user, 'Skill Reset Item Redeemed', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        // Pick skills to give to character based on grant type
                        if ($firstData['grant_type'] == ItemGrantType::SELECTOR->value && count($firstData['skills']) > 1) {
                            //revoke skill that was selected
                            $character_skill = CharacterSkill::where([
                                ['character_image_id', '=', $character->image->id],
                                ['skill_id', '=', $data['selected_skill']],
                            ])->first();
                            if (!$character_skill) {
                                throw new \Exception('Character does not know selected skill');
                            } else if($character_skill->xp <= 0 && $firstData['skill_item_type'] == ItemType::RESET->value){
                                throw new \Exception('Skill is already at level 1.');
                            }
                            $skillOption['skills'] = [$data['selected_skill'] => $firstData['skills'][$data['selected_skill']]];
                        } else {
                            //revoke all skills
                            $skillOption = $stacks->first()->item->tag('skill_revoke')->data;
                            $skill_pool = Skill::find(array_keys($skillOption['skills']))->pluck('id');
                            $skillOption['skills'] = [];
                            // Check if character selected has at least one skill in this item
                            foreach ($skill_pool as $skill) {
                                if ($character_skill = CharacterSkill::where([
                                    ['character_image_id', '=', $character->image->id],
                                    ['skill_id', '=', $skill],
                                ])->first()) {
                                    if ($firstData['skill_item_type'] == ItemType::REVOKE->value){
                                        $skillOption['skills'] += [$skill => 0];
                                    } else if ($character_skill->xp > 0){
                                        $skillOption['skills'] += [$skill => 0];
                                    }
                                }
                            }
                            if (count($skillOption['skills']) < 1) {
                                throw new \Exception('Character does have any levels the applicable skill(s).');
                            }
                        }

                        if (!$rewards = fillCharacterAssets(parseAssetData($skillOption), $stack->user, $character, $reason, [
                            'data'      => 'Used '.$stack->item->displayName.'.',
                            'is_revoke'  => ($firstData['skill_item_type'] == ItemType::REVOKE->value),
                            'is_lvl'    => false,
                            'is_set'    => true,
                        ])) {
                            throw new \Exception("Failed to redeem skill item.");
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
        $results = 'Successfully reset the following skill(s): ';
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
