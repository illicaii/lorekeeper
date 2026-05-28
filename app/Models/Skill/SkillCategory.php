<?php

namespace App\Models\Skill;

use App\Models\Model;

class SkillCategory extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sort', 'has_image', 'description', 'parsed_description', 'is_default', 'is_visible', 'is_levelable',
        'hash', 'max_level', 'max_charge', 'level_base', 'level_multiplier', 'randomize_firstLevel', 'random_level_min',
        'random_level_max',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skill_categories';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name'              => 'required|unique:skill_categories|between:3,100',
        'description'       => 'nullable',
        'image'             => 'mimes:jpeg,jpg,png|max:2048',
        'max_level'         => 'integer|min:0',
        'max_charge'        => 'integer|min:0',
        'level_base'        => 'integer|min:10',
        'level_multiplier'  => 'decimal:1,4|min:1.0',
        'random_level_min'  => 'integer|min:0',
        'random_level_max'  => 'integer|min:0',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name'              => 'required|between:3,100',
        'description'       => 'nullable',
        'image'             => 'mimes:jpeg,jpg,png|max:2048',
        'max_level'         => 'integer|min:0',
        'max_charge'        => 'integer|min:0',
        'level_base'        => 'integer|min:10',
        'level_multiplier'  => 'decimal:1,4|min:1.0',
        'random_level_min'  => 'integer|min:0',
        'random_level_max'  => 'integer|min:0',
    ];

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to show only visible categories.
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

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the skills associated with this species.
     */
    public function skills() {
        return $this->hasMany(Skill::class);
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
        return '<a href="'.$this->url.'" class="display-category">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return 'images/data/skill-categories';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getCategoryImageFileNameAttribute() {
        return $this->hash.$this->id.'-image.png';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getCategoryImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getCategoryImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->categoryImageFileName);
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('world/skill-categories?name='.$this->name);
    }

    /**
     * Gets the URL for an encyclopedia search of skills in this category.
     *
     * @return string
     */
    public function getSearchUrlAttribute() {
        return url('world/skills?skill_category_id='.$this->id);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/data/skill-categories/edit/'.$this->id);
    }
}
