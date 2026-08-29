<?php

namespace App\Models\Character;

use App\Models\Model;
use App\Models\Skill\Skill;
use App\Models\Skill\SkillTag;
use Carbon\Carbon;

class CharacterSkill extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_image_id', 'skill_id', 'data', 'character_type', 'xp', 'charges', 'reset_time',
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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reset_time' => 'datetime',
    ];

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

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include skills that require updating.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequiresUpdate($query) {
        return $query->whereIn('skill_id', Skill::whereIn('id', SkillTag::where('is_active', 1)->pluck('skill_id')->toArray())->pluck('id')->toArray())->where('reset_time', '<', Carbon::now());
    }

    /**********************************************************************************************

        Other Functions

    **********************************************************************************************/

    /**
     * Get the level of this skill.
     */
    public function getlevel() {
        $skill = $this->skill()->get()[0];

        return $skill->getlevel($this->xp);
    }

    /**
     * Get the max level of this skill. This just calls the skill get max levels function.
     */
    public function getMaxlevel() {
        $skill = $this->skill()->get()[0];

        return $skill->getMaxlevels();
    }

    /**
     * Get the max charges of this skill. This just calls the skill get max charges function.
     */
    public function getMaxCharges() {
        $skill = $this->skill()->get()[0];

        return max($skill->getMaxCharges(), 1);
    }

    /**
     * Get the max charges the character has at their current level.
     */
    public function getTotalCharges() {
        $skill = $this->skill()->get()[0];

        $max_charges = $skill->getMaxCharges();
        $max_levels = $skill->getMaxLevels();
        if ($max_levels === 0) {
            return max($max_charges, 1);
        }
        $character_level = $skill->getlevel($this->xp);

        return max(ceil(($max_charges / $max_levels) * (max($character_level, 1))), 1);
    }

    /**
     * Get the charge cost for the skill at the character's current level.
     *
     * @param mixed $tag
     */
    public function getChargesOnSingleUse($tag) {
        $skill = $this->skill()->get()[0];
        $character_level = $skill->getlevel($this->xp);

        foreach ($skill->tag($tag)->data as $breakpoint) {
            if ($character_level >= $breakpoint['min_lvl'] && $character_level < $breakpoint['max_lvl']) {
                $charge = $breakpoint['charges'];
            }
        }

        return max($charge, 1);
    }

    /**
     * Get the number of charges the character has left to use for this skill.
     */
    public function getAvailableCharges() {
        $charges_before_use = 0;
        $skill = $this->skill()->get()[0];

        $max_charges = $skill->getMaxCharges();
        $max_levels = $skill->getMaxLevels();
        if ($max_levels === 0) {
            $charges_before_use = (max($max_charges, 1));
        } else {
            $character_level = $skill->getlevel($this->xp);
            $charges_before_use = max(ceil(($max_charges / $max_levels) * (max($character_level, 1))), 1);
        }

        return max($charges_before_use - $this->charges, 0);
    }
}
