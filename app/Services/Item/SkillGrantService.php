<?php

namespace App\Services\Item;

use App\Enums\Skill\ItemGrantType;
use App\Enums\Skill\ItemType;
use App\Models\Character\Character;
use App\Models\Character\CharacterSkill;
use App\Models\Skill\Skill;
use App\Services\InventoryManager;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class SkillGrantService extends Service {
    /*
    |--------------------------------------------------------------------------
    | Skill Service
    |--------------------------------------------------------------------------
    |
    | Handles the granting and editing of skills through items
    |
    */

    /**
     * Retrieves any data that should be used in the item tag editing form.
     *
     * @return array
     */
    public function getEditData() {
        $item_types = [ItemType::GRANT->value => 'Skill Grant', ItemType::ADD->value => 'Add XP/Level', ItemType::SET->value => 'Set Level'];
        $grant_types = [ItemGrantType::SELECTOR->value => 'Grant Single (Selector)',
            ItemGrantType::RANDOM->value               => 'Grant Random',
            ItemGrantType::ALL->value                  => 'Grant All'];

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
            'grant_level'      => $tag->data['is_lvl'],
            'grant_type'       => $tag->data['grant_type'],
            'error_on_missing' => $tag->data['error_on_missing'],
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

            // The data will be stored as an asset table, json_encode()d.
            // First build the asset table, then prepare it for storage.
            $assets = createAssetsArray();
            foreach ($data['rewardable_id'] as $key => $r) {
                $asset = Skill::find($data['rewardable_id'][$key]);
                addAsset($assets, $asset, $data['quantity'][$key]);
            }
            $assets = getDataReadyAssets($assets);
            if (isset($data['grant_level'])) {
                $assets += ['is_lvl' => $data['grant_level']];
            }
            $assets += ['error_on_missing' => $data['error_on_missing']];
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
            $firstData = $stacks->first()->item->tag('skill_grant')->data;
            $character = Character::where('id', $data['character_id'])->get()->first();

            // Check instant failures
            if (!isset($firstData['skills'])) {
                throw new \Exception('Item has no applicable skills');
            } elseif ($character->user->id != $user->id) {
                throw new \Exception('You do not own this character');
            }

            // Check if character selected has at least one skill in this item
            $skillOption = $stacks->first()->item->tag('skill_grant')->data;
            $skill_pool = Skill::find(array_keys($skillOption['skills']))->pluck('id');
            $learned_skills = [];
            $unlearned_skills = [];
            foreach ($skill_pool as $skill) {
                if ($character_skill = CharacterSkill::where([
                    ['character_image_id', '=', $character->image->id],
                    ['skill_id', '=', $skill],
                ])->first()) {
                    $learned_skills += [$skill => $skillOption['skills'][$skill]];
                } else {
                    $unlearned_skills += [$skill => $skillOption['skills'][$skill]];
                }
            }

            foreach ($stacks as $key => $stack) {
                // Check to make sure the owner of the box is the one opening it
                if ($stack->user_id != $user->id) {
                    throw new \Exception('This item does not belong to you.');
                }

                // Try to delete the box item. If successful, we can start distributing rewards.
                $total_rewards = [];
                if ((new InventoryManager)->debitStack($stack->user, 'Skill Item Redeemed', ['data' => ''], $stack, $data['quantities'][$key])) {
                    for ($q = 0; $q < $data['quantities'][$key]; $q++) {
                        if ($firstData['skill_item_type'] == ItemType::GRANT->value) {
                            if (count($unlearned_skills) == 0) {
                                throw new \Exception('Character knows all applicable skills already');
                            } elseif ($data['quantities'][$key] > count($unlearned_skills)) {
                                throw new \Exception('You can not use more items than skills to learn');
                            } elseif ($firstData['grant_type'] == ItemGrantType::ALL->value && $data['quantities'][$key] > 1) {
                                throw new \Exception('You can not use more than one of these items at a time');
                            } elseif ($firstData['grant_type'] == ItemGrantType::SELECTOR->value && count($firstData['skills']) > 1) {
                                if (!array_key_exists($data['selected_skill'], $unlearned_skills)) {
                                    throw new \Exception('Character knows selected skill already');
                                }
                                $skillOption = $this->pickSkill($firstData['skill_item_type'], $firstData['grant_type'], $character, $firstData, $unlearned_skills, $data['selected_skill']);
                            } else {
                                $skillOption = $this->pickSkill($firstData['skill_item_type'], $firstData['grant_type'], $character, $firstData, $unlearned_skills);
                            }
                        } else {
                            if (count($learned_skills) == 0) {
                                throw new \Exception('Character does not know any applicable skills');
                            } elseif ($firstData['grant_type'] == ItemGrantType::SELECTOR->value && count($firstData['skills']) > 1) {
                                if (!array_key_exists($data['selected_skill'], $learned_skills)) {
                                    throw new \Exception("Character doesn't know selected skill");
                                }
                                $skillOption = $this->pickSkill($firstData['skill_item_type'], $firstData['grant_type'], $character, $firstData, $learned_skills, $data['selected_skill']);
                            } else {
                                $skillOption = $this->pickSkill($firstData['skill_item_type'], $firstData['grant_type'], $character, $firstData, $learned_skills);
                            }
                        }

                        if (!$rewards = fillCharacterAssets(parseAssetData($skillOption), $stack->user, $character, 'Item based Modification', [
                            'data'   => 'Used '.$stack->item->displayName.'.',
                            'is_lvl' => $firstData['is_lvl'],
                            'is_set' => ($firstData['skill_item_type'] == ItemType::SET->value),
                        ])) {
                            throw new \Exception("Failed to redeem skill Items. Can not decrease character's skill level below 0 or increase above max");
                        } else {
                            $total_rewards[$q] = $rewards;
                        }
                    }
                }
                // Flash all rewards now that we know stack operation succeeds
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
     * Picks the skill, dependant on grant type.
     *
     * @param App\Enums\Skill\ItemType      $itemType
     * @param App\Enums\Skill\ItemGrantType $grantType
     * @param Character                     $character
     * @param array                         $itemData
     * @param array                         $skillPool
     * @param int                           $selected_skill
     *
     * @return array
     */
    private function pickSkill($itemType, $grantType, $character, $itemData, $skillPool, $selected_skill = null) {
        // Pick skills to give to character based on grant type
        switch ($grantType) {
            case ItemGrantType::SELECTOR->value:
                if (count($itemData['skills']) > 1) {
                    $skillOption['skills'] = [$selected_skill => $itemData['skills'][$selected_skill]];
                } else {
                    $skillOption['skills'] = $itemData['skills'];
                }
                break;
            case ItemGrantType::RANDOM->value:
                // grant random skill
                if ($itemType == ItemType::GRANT->value) {
                    do {
                        $random = array_rand($skillPool);
                        $character_skill = CharacterSkill::where([
                            ['character_image_id', '=', $character->image->id],
                            ['skill_id', '=', $random],
                        ])->first();
                    } while ($character_skill);
                } else {
                    $random = array_rand($skillPool);
                }

                $skillOption['skills'] = [$random => $itemData['skills'][$random]];
                break;
            case ItemGrantType::ALL->value:
                $skillOption['skills'] = $skillPool;
                break;
            default:
                throw new \Exception('No Skill Item Type Selected.');
                break;
        }

        return $skillOption;
    }

    /**
     * Gets the skill reward string.
     *
     * @param array $rewards
     *
     * @return string
     */
    private function getSkillRewardsString($rewards) {
        $results = 'Successfully modified the following skill(s): ';
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
