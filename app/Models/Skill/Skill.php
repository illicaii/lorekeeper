<?php

namespace App\Models\Skill;

use App\Models\Model;
use App\Models\Species\Species;
use Illuminate\Support\Facades\DB;

class Skill extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'skill_abrv', 'description', 'parsed_description', 'skill_category_id', 'species_id',
        'has_image', 'hash', 'is_visible', 'is_backend', 'skill_type', 'parent_id', 'parent_level',
        'override_default_caps', 'ovr_level_cap', 'ovr_charge_cap',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skills';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'tags',
    ];

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'skill_category_id'     => 'nullable',
        'species_id'            => 'nullable',
        'name'                  => 'required|unique:skills|between:3,100',
        'skill_abrv'            => 'required|unique:skills|between:1,20',
        'description'           => 'nullable',
        'image'                 => 'mimes:jpeg,jpg,png|max:2048',
        'override_default_caps' => 'boolean:strict',
        'ovr_level_cap'         => 'integer|min:0',
        'ovr_charge_cap'        => 'integer|min:0',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'skill_category_id'     => 'nullable',
        'species_id'            => 'nullable',
        'name'                  => 'required|between:3,100',
        'skill_abrv'            => 'required|between:1,20',
        'description'           => 'nullable',
        'image'                 => 'mimes:jpeg,jpg,png|max:2048',
        'override_default_caps' => 'boolean:strict',
        'ovr_level_cap'         => 'integer|min:0',
        'ovr_charge_cap'        => 'integer|min:0',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the category the skill belongs to.
     */
    public function category() {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /**
     * Get the species the skill belongs to.
     */
    public function species() {
        return $this->belongsTo(Species::class);
    }

    /**
     * Get the children of the skill.
     */
    public function children() {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the parent the skill belongs to.
     */
    public function parent() {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the skill's tags.
     */
    public function tags() {
        return $this->hasMany(SkillTag::class, 'skill_id');
    }

    /**
     * Get the max skill level.
     */
    public function maxLevel() {
        if ($this->override_default_caps) {
            return $this->ovr_level_cap;
        }
        if (isset($this->category->max_level)) {
            return $this->category->max_level;
        }

        return 0;
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort skills in alphabetical order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortAlphabetical($query, $reverse = false) {
        return $query->orderBy('name', $reverse ? 'DESC' : 'ASC');
    }

    /**
     * Scope a query to sort skills in category order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortCategory($query) {
        if (SkillCategory::all()->count()) {
            return $query->orderBy(SkillCategory::select('sort')->whereColumn('skills.skill_category_id', 'skill_categories.id'), 'DESC');
        }

        return $query;
    }

    /**
     * Scope a query to sort skills in species order.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortSpecies($query) {
        $ids = Species::orderBy('sort', 'DESC')->pluck('id')->toArray();

        return count($ids) ? $query->orderBy(DB::raw('FIELD(species_id, '.implode(',', $ids).')')) : $query;
    }

    /**
     * Scope a query to sort skills by newest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortNewest($query) {
        return $query->orderBy('id', 'DESC');
    }

    /**
     * Scope a query to sort skills oldest first.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortOldest($query) {
        return $query->orderBy('id');
    }

    /**
     * Scope a query to show only visible skills.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to show only visible skills.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFrontEnd($query, $user = null) {
        if ($user && $user->hasPower('edit_data')) {
            return $query;
        }

        return $query->where('is_backend', 0);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return '<a href="'.$this->url.'" class="display-skill">'.$this->name.'</a>'.($this->skill_abrv ? ' ('.$this->skill_abrv.')' : '');
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/skills';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getImageFileNameAttribute() {
        return $this->hash.$this->id.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->imageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/skills?name='.$this->name);
    }

    /**
     * Gets the URL for a masterlist search of characters in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('masterlist?skill_id[]='.$this->id);
    }

    /**
     * Gets the URL of the individual skill's page, by ID.
     *
     * @return string
     */
    public function getIdUrlAttribute() {
        return url('world/skills/'.$this->id);
    }

    /**
     * Gets the currency's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'skills';
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/skills/edit/'.$this->id);
    }

    /**********************************************************************************************

        Other Functions

    **********************************************************************************************/

    /**
     * Get skills for display in dropdowns.
     *
     * @param mixed $withHidden
     * @param mixed $withBackend
     */
    public static function getDropdownItems($withHidden = 0, $withBackend = 0) {
        $visibleOnly = 1;
        if ($withHidden) {
            $visibleOnly = 0;
        }

        $sorted_skill_categories = collect(SkillCategory::all()->where('is_visible', '>=', $visibleOnly)->sortBy('sort')->pluck('name')->toArray());

        $grouped = self::where('is_visible', '>=', $visibleOnly)->where('is_backend', '<=', $withBackend)->select('name', 'id', 'skill_category_id')->with('category')->orderBy('name')->get()->keyBy('id')->groupBy('category.name', $preserveKeys = true)->toArray();
        if (isset($grouped[''])) {
            if (!$sorted_skill_categories->contains('Miscellaneous')) {
                $sorted_skill_categories->push('Miscellaneous');
            }
            $grouped['Miscellaneous'] ??= [] + $grouped[''];
        }

        $sorted_skill_categories = $sorted_skill_categories->filter(function ($value, $key) use ($grouped) {
            return in_array($value, array_keys($grouped), true);
        });

        foreach ($grouped as $category => $skills) {
            foreach ($skills as $id  => $skill) {
                $grouped[$category][$id] = $skill['name'];
            }
        }
        $skills_by_category = $sorted_skill_categories->map(function ($category) use ($grouped) {
            return [$category => $grouped[$category]];
        });

        return $skills_by_category;
    }

    /* Skill Ability Tags ************************************************************************/

    /**
     * Checks if the skill has any active tag.
     *
     * @param mixed $tag
     *
     * @return bool
     */
    public function hasActiveTag() {
        return $this->tags()->where('is_active', 1)->exists();
    }

    /**
     * Checks if the skill has a particular tag.
     *
     * @param mixed $tag
     *
     * @return bool
     */
    public function hasTag($tag) {
        return $this->tags()->where('tag', $tag)->where('is_active', 1)->exists();
    }

    /**
     * Gets a particular tag attached to the skill.
     *
     * @param mixed $tag
     *
     * @return ItemTag
     */
    public function tag($tag) {
        return $this->tags()->where('tag', $tag)->where('is_active', 1)->first();
    }

    /* Level and XP ******************************************************************************/

    /**
     * Get the max level for this skill.
     */
    public function getMaxLevels() {
        $skill = $this;
        $r = 0;
        if ($skill->override_default_caps) {
            $r = $skill->ovr_level_cap;
        } elseif (isset($skill->category->max_level)) {
            $r = $skill->category->max_level;
        }
        return $r;
    }

    /**
     * Get the max number of charges for this skill.
     */
    public function getMaxCharges() {
        $skill = $this;
        $r = 0;
        if ($skill->override_default_caps) {
            $r = $skill->ovr_charge_cap;
        } elseif (isset($skill->category->max_charge)) {
            $r = $skill->category->max_charge;
        }
        return $r;
    }

    /**
     * Get the level of the skill based on the xp given.
     *
     * @param mixed $xp
     */
    public function getlevel($xp) {
        $skill = $this;
        if ($skill->override_default_caps) {
            $max_level = $skill->ovr_level_cap;
        } elseif (isset($skill->category->max_level)) {
            $max_level = $skill->category->max_level;
        } else {
            $max_level = 0;
        }

        // Default level curve if category is not defined
        $xp_base = 100.0;
        $multiplier = 1.25;
        // Override default with category defined level curve
        if (isset($this->category)) {
            $xp_base = $this->category->level_base;
            $multiplier = $this->category->level_multiplier;
        }

        $level = floor($xp / ($xp_base * $multiplier)) + 1.0;
        if ($level > $max_level) {
            return $max_level;
        }

        return $level;
    }

    /**
     * Get the xp needed to reach given skill level.
     *
     * @param mixed $level
     */
    public function getXpForLevel($level) {
        $skill = $this;
        if ($skill->override_default_caps) {
            $max_level = $skill->ovr_level_cap;
        } elseif (isset($skill->category->max_level)) {
            $max_level = $skill->category->max_level;
        } else {
            $max_level = 0;
        }
        if ($level > $max_level) {
            $level = $max_level;
        } elseif ($level <= 0) {
            $level = 1;
        }

        // Default level curve if category is not defined
        $xp_base = 100.0;
        $multiplier = 1.25;
        // Override default with category defined level curve
        if (isset($this->category)) {
            $xp_base = $this->category->level_base;
            $multiplier = $this->category->level_multiplier;
        }

        $xp = floor(($level - 1) * ($xp_base * $multiplier));

        return $xp;
    }

    /**
     * Get starting XP for randomly generated level of the skill based on the category.
     *
     * @return int xp needed for random level between MIN and MAX starting level as defined by the category or 0 if no category
     */
    public function getRandomStartingLevel() {
        if (isset($this->category) && $this->category->randomize_firstLevel) {
            $random_level = rand($this->category->random_level_min, $this->category->random_level_max);

            return $this->getXpForLevel($random_level);
        }

        return 0;
    }
}
