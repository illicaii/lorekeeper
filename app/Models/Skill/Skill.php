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
        'name', 'skill_abrv', 'description', 'parsed_description', 'skill_category_id', 'species_id', 'has_image', 'is_visible', 'skill_type',
        'parent_id', 'parent_level', 'override_default_caps', 'ovr_level_cap',
        'ovr_charge_cap', 'hash',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skills';

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
        'image'                 => 'mimes:png',
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
        'image'                 => 'mimes:png',
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
        return $this->hasMany(Skill::class, 'parent_id');
    }

    /**
     * Get the parent the skill belongs to.
     */
    public function parent() {
        return $this->belongsTo(Skill::class, 'parent_id');
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
}
