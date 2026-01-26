<?php

namespace App\Models\Skill;

use App\Models\Model;
use App\Models\User\User;

class SkillLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'sender_id', 'log', 'log_type', 'data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skill_log';
    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who initiated the logged action.
     */
    public function sender() {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the character that is the target of the action.
     */
    public function character() {
        return $this->belongsTo(Character::class);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Retrieves the changed data as an associative array.
     *
     * @return array
     */
    public function getChangedDataAttribute() {
        return json_decode($this->change_log, true);
    }
}
