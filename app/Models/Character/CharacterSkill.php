<?php

namespace App\Models\Character;

use App\Models\Model;
use App\Models\Skill\Skill;

class CharacterSkill extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_image_id', 'skill_id', 'data', 'character_type', 'xp', 'charges', 'is_active',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_skills';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['skill'];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'xp'             => 'integer|min:0',
        'charges'        => 'integer|min:0',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'xp'             => 'integer|min:0',
        'charges'        => 'integer|min:0',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the image associated with this record.
     */
    public function image() {
        return $this->belongsTo(CharacterImage::class, 'character_image_id');
    }

    /**
     * Get the skill associated with this record.
     */
    public function skill() {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    /**********************************************************************************************

        Other Functions

    **********************************************************************************************/

    public function getlevel() {
        $skill = $this->belongsTo(Skill::class, 'skill_id')->get()[0];
        $xp = $this->xp;
        if ($skill->override_default_caps) {
            $max_level = $skill->ovr_level_cap;
        } elseif (isset($skill->category->max_level)) {
            $max_level = $skill->category->max_level;
        } else {
            $max_level = 0;
        }

        $xp_base = 10.0;
        $multiplier = 1.25;
        $level = floor($xp / ($xp_base * $multiplier)) + 1.0;
        if ($level > $max_level) {
            return $max_level;
        }

        return $level;
    }
}
