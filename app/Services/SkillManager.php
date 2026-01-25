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
                    if ($this->creditSkill($staff, $character, 'Staff Grant', $data['data'], $skill, $skill_xp[$i], $data['is_lvl'])) {
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
     * @param \App\Models\Character\Character $sender
     * @param \App\Models\Character\Character $recipient
     * @param string                          $type
     * @param array                           $data
     * @param Skill                           $skill
     * @param int                             $quantity
     * @param mixed                           $is_lvl
     *
     * @return bool
     */
    public function creditSkill($sender, $recipient, $type, $data, $skill, $quantity, $is_lvl) {
        DB::beginTransaction();

        try {
            $recipient_stack = CharacterSkill::where([
                ['character_image_id', '=', $recipient->image->id],
                ['skill_id', '=', $skill->id],
            ])->first();

            if (!$recipient_stack) {
                //New skill grant
                if ($quantity < 0) {
                    throw new \Exception('Can not grant negative xp to character(s) that do not have '.$skill->displayName);
                }

                $log_data = 'Learned '.$skill->name.' skill. Reason:';
                if ($is_lvl) {
                    $quantity = $skill->getXpForLevel($quantity);
                }
                $recipient_stack = CharacterSkill::create(['character_image_id' => $recipient->image->id, 'skill_id' => $skill->id, 'xp' => $quantity, 'charges' => 0]);
            } else {
                //Add levels or xp to existing skill

                if ($is_lvl && !($quantity == 0)) {
                    $truelvl = $recipient_stack->getlevel() + $quantity;
                    $quantity = $skill->getXpForLevel($truelvl) - $recipient_stack->xp;
                }

                $log_data = 'Received '.$quantity.' xp for '.$skill->name.' skill. Reason:';
                if ($recipient_stack->xp + $quantity >= 0) {
                    $recipient_stack->xp += $quantity;
                } else {
                    throw new \Exception("Can not grant xp that would make a character's level negative");
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
     * Creates a log for the skill awarding.
     *
     * @param int    $recipientId
     * @param int    $senderId
     * @param string $type
     * @param string $data
     */
    public function createLog($recipientId, $senderId, $type, $data) {
        return DB::table('character_log')->insert(
            [
                'character_id' => $recipientId,
                'sender_id'    => $senderId,
                'log'          => 'Skill Awarded ('.$type.')',
                'log_type'     => 'Skill Awarded',
                'data'         => $data,
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
            ]
        );
    }
}
