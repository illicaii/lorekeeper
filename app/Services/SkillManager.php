<?php

namespace App\Services;

use App\Facades\Notifications;
use App\Models\Character\Character;
use App\Models\Character\CharacterSkill;
use App\Models\Skill\Skill;
use Carbon\Carbon;
use DB;

class SkillManager extends Service {
    /*
    |--------------------------------------------------------------------------
    | Skill Manager
    |--------------------------------------------------------------------------
    |
    | Handles modification of user-owned skills.
    |
    */

    /**
     * Credits an skill to multiple characters.
     *
     * @param array                 $data
     * @param \App\Models\User\User $staff
     *
     * @return bool
     */
    public function grantSkill($data, $staff) {
        DB::beginTransaction();
        try {
            $characters = Character::find($data['character_id']);
            if (count($characters) != count($data['character_id'])) {
                throw new \Exception('An invalid character was selected.');
            }
            $skills = Skill::find($data['skill_ids']);
            if (!count($skills)) {
                throw new \Exception('An invalid skill was selected.');
            }
            $skill_xp = $data['skill_xp'];

            foreach ($characters as $character) {
                foreach ($skills as $i=> $skill) {
                    if (!$this->logAdminAction($staff, 'Skill Grant', 'Granted '.$skill->displayName.' to '.$character->displayname)) {
                        throw new \Exception('Failed to log admin action.');
                    }
                    if ($this->creditSkill($staff, $character, 'Staff Grant', $data['data'], $skill, $skill_xp[$i], $data['is_lvl'], false, false)) {
                        if ($data['is_lvl']) {
                            Notifications::create('SKILL_GRANT', $character->user, [
                                'skill_name'         => $skill->displayName,
                                'character_slug'     => $character->slug,
                                'character_name'     => $character->displayName,
                            ]);
                        } else {
                            Notifications::create('XP_GRANT', $character->user, [
                                'skill_name'         => $skill->displayName,
                                'xp_amount'          => $skill_xp[$i],
                                'character_slug'     => $character->slug,
                                'character_name'     => $character->displayName,
                            ]);
                        }
                    } else {
                        throw new \Exception('Failed to credit skills to '.$character->slug.'.');
                    }
                }
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Credits an skill to a character.
     *
     * @param Character $sender
     * @param Character $recipient
     * @param string    $type
     * @param array     $data
     * @param Skill     $skill
     * @param int       $quantity
     * @param bool      $is_lvl
     * @param bool      $is_set
     * @param mixed     $random_level
     *
     * @return bool
     */
    public function creditSkill($sender, $recipient, $type, $data, $skill, $quantity, $is_lvl, $is_set, $random_level) {
        DB::beginTransaction();
        try {
            $recipient_stack = CharacterSkill::where([
                ['character_image_id', '=', $recipient->image->id],
                ['skill_id', '=', $skill->id],
            ])->first();
            if ($random_level) {
                $quantity = $skill->getRandomStartingLevel();
            }
            if (!$recipient_stack) {
                // New skill grant
                if ($quantity < 0) {
                    throw new \Exception('Can not grant negative xp to character(s) that do not have '.$skill->displayName);
                }
                $log_data = 'Learned '.$skill->displayName.' skill. '.($data ?? '');

                if (!($type === 'Staff Grant')) {
                    $type = 'Item based Redemption';
                }
                if (!$random_level && ($is_lvl || $is_set)) {
                    // Note: we don't need to do this when random_level is set because that already makes quantity the xp needed to get to the random_level
                    $quantity = $skill->getXpForLevel($quantity);
                }
                $recipient_stack = CharacterSkill::create(['character_image_id' => $recipient->image->id, 'skill_id' => $skill->id, 'xp' => $quantity, 'charges' => 0]);
            } else {
                // Character already knows skill
                // Note: we don't calculate XP when random_level is set because that already calculates the quantity needed to get to
                // the random_level from 0 which would max level every time
                if ($is_set) {
                    // Set level
                    if ($random_level) {
                        $quantity = $quantity - $recipient_stack->xp;
                    } else {
                        $quantity = $skill->getXpForLevel($quantity) - $recipient_stack->xp;
                    }
                } elseif (!$random_level && $is_lvl && !($quantity == 0)) {
                    // Add levels or xp to existing skill
                    $truelvl = $recipient_stack->getlevel() + $quantity;
                    $quantity = $skill->getXpForLevel($truelvl) - $recipient_stack->xp;
                }
                if ($quantity < 0) {
                    $log_data = 'Removed '.abs($quantity).' xp from '.$skill->displayName.' skill. '.($data ?? '');
                } else {
                    $log_data = 'Received '.$quantity.' xp for '.$skill->displayName.' skill. '.($data ?? '');
                }

                if (($recipient_stack->xp + $quantity) > $skill->getXpForLevel($skill->maxLevel())) {
                    if ($type === 'Staff Grant' || $random_level) {
                        $recipient_stack->xp = $skill->getXpForLevel($skill->maxLevel());
                    } else {
                        throw new \Exception("Can not grant xp that would increase a character's level more than max");
                    }
                } elseif ($recipient_stack->xp + $quantity < 0) {
                    if ($type === 'Staff Grant' || $random_level) {
                        $recipient_stack->xp = 0;
                    } else {
                        throw new \Exception("Can not grant xp that would make a character's level negative");
                    }
                } else {
                    $recipient_stack->xp += $quantity;
                }
                $recipient_stack->save();
            }

            if ($type && !$this->createLog($recipient->id, $sender->id, $type, $log_data)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Revokes (deletes) a skill from a character.
     *
     * @param Character $sender
     * @param Character $recipient
     * @param string    $type
     * @param array     $data
     * @param Skill     $skill
     *
     * @return bool
     */
    public function revokeSkill($sender, $recipient, $type, $data, $skill) {
        DB::beginTransaction();
        try {
            $recipient_stack = CharacterSkill::where([
                ['character_image_id', '=', $recipient->image->id],
                ['skill_id', '=', $skill->id],
            ])->first();
            if (!$recipient_stack) {
                throw new \Exception('Can not remove '.$skill->displayName.' from a character that does not know it.');
            } else {
                // Log old skills
                $image = $recipient->image;

                // Clear old skills
                $recipient_stack->delete();

                // Image and Character keep track of these skills
                $image->save();
                $image->character->save();
                $log_data = 'Forgot '.$skill->displayName.' skill. '.($data ?? '');
            }

            // Create log
            if ($type && !$this->createLog($recipient->id, $sender->id, $type, $log_data)) {
                throw new \Exception('Failed to create log.');
            }

            return $this->commitReturn(true);
        } catch (\Exception $e) {
            $this->setError('error', $e->getMessage());
        }

        return $this->rollbackReturn(false);
    }

    /**
     * Creates a log for the skill awarding.
     *
     * @param int    $recipientId
     * @param int    $senderId
     * @param string $type
     * @param string $data
     */
    public function createLog($recipientId, $senderId, $type, $data) {
        $msg = 'Granted';
        if ($type === 'Item based Reset') {
            $msg = 'Reset';
            $type = 'Item Use';
        } elseif ($type === 'Item based Revoked') {
            $msg = 'Deleted';
            $type = 'Item Use';
        } elseif ($type === 'Item based Redemption') {
            $msg = 'Added';
            $type = 'Item Use';
        } elseif ($type === 'Item based Modification') {
            $msg = 'Edited';
            $type = 'Item Use';
        }

        return DB::table('skill_log')->insert(
            [
                'character_id' => $recipientId,
                'sender_id'    => $senderId,
                'log'          => 'Skill '.$msg.' ('.$type.')',
                'log_type'     => 'Skill '.$msg,
                'data'         => $data,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }
}
