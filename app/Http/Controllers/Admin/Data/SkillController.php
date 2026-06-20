<?php

namespace App\Http\Controllers\Admin\Data;

use App\Enums\Skill\SkillType;
use App\Http\Controllers\Controller;
use App\Models\Skill\Skill;
use App\Models\Skill\SkillCategory;
use App\Models\Species\Species;
use App\Services\SkillService;
use Auth;
use Illuminate\Http\Request;

class SkillController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Skill Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character skill categories and skills
    |
    */

    /**********************************************************************************************

        SKILL CATEGORIES

    **********************************************************************************************/

    /**
     * Shows the skill category index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCategoryIndex() {
        return view('admin.skills.skill_categories', [
            'categories' => SkillCategory::orderBy('sort', 'DESC')->get(),
        ]);
    }

    /**
     * Shows the create skill category page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateSkillCategory() {
        return view('admin.skills.create_edit_skill_category', [
            'category' => new SkillCategory,
        ]);
    }

    /**
     * Shows the edit skill category page.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSkillCategory($id) {
        $category = SkillCategory::find($id);
        if (!$category) {
            abort(404);
        }

        return view('admin.skills.create_edit_skill_category', [
            'category' => $category,
        ]);
    }

    /**
     * Creates or edits an skill category.
     *
     * @param App\Services\SkillService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditSkillCategory(Request $request, SkillService $service, $id = null) {
        $id ? $request->validate(SkillCategory::$updateRules) : $request->validate(SkillCategory::$createRules);
        $data = $request->only([
            'name', 'description', 'image', 'remove_image', 'is_default', 'is_visible', 'is_levelable',
            'max_level', 'max_charge', 'level_base', 'level_multiplier', 'randomize_firstLevel', 'random_level_min',
            'random_level_max',
        ]);
        if ($id && $service->updateSkillCategory(SkillCategory::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } elseif (!$id && $category = $service->createSkillCategory($data, Auth::user())) {
            flash('Category created successfully.')->success();

            return redirect()->to('admin/data/skill-categories/edit/'.$category->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the skill category deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSkillCategory($id) {
        $category = SkillCategory::find($id);

        return view('admin.skills._delete_skill_category', [
            'category' => $category,
        ]);
    }

    /**
     * Deletes an skill category.
     *
     * @param App\Services\SkillService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSkillCategory(Request $request, SkillService $service, $id) {
        if ($id && $service->deleteSkillCategory(SkillCategory::find($id))) {
            flash('Category deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/skill-categories');
    }

    /**
     * Sorts skill categories.
     *
     * @param App\Services\SkillService $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortSkillCategory(Request $request, SkillService $service) {
        if ($service->sortSkillCategory($request->get('sort'))) {
            flash('Category order updated successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**********************************************************************************************

        SKILLS

    **********************************************************************************************/

    /**
     * Shows the skill Index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSkillIndex(Request $request) {
        $query = Skill::query();
        $data = $request->only(['skill_category_id', 'species_id', 'name']);
        if (isset($data['skill_category_id']) && $data['skill_category_id'] != 'none') {
            $query->where('skill_category_id', $data['skill_category_id']);
        }
        if (isset($data['species_id']) && $data['species_id'] != 'none') {
            $query->where('species_id', $data['species_id']);
        }
        if (isset($data['name'])) {
            $query->where('name', 'LIKE', '%'.$data['name'].'%');
        }

        return view('admin.skills.skills', [
            'skills'     => $query->paginate(20)->appends($request->query()),
            'species'    => ['none' => 'Any Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'Any Category'] + SkillCategory::pluck('name', 'id', 'max_level', 'max_charge')->toArray(),
        ]);
    }

    /**
     * Shows the create skill page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateSkill() {
        return view('admin.skills.create_edit_skill', [
            'skill'      => new Skill,
            'species'    => ['none' => 'No restriction'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'skills'     => ['none' => 'No Parent'] + Skill::orderBy('name', 'ASC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No Category'] + SkillCategory::pluck('name', 'id', 'max_level', 'max_charge')->toArray(),
            'skill_types'=> [SkillType::COSMETIC->value => 'Cosmetic', SkillType::CONSUMABLE->value => 'Consumable',
                SkillType::ITEM_GRANTER->value          => 'Item Granter'],
        ]);
    }

    /**
     * Shows the edit skill page.
     *
     * @param mixed $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSkill($id) {
        $skill = Skill::find($id);
        if (!$skill) {
            abort(404);
        }

        return view('admin.skills.create_edit_skill', [
            'skill'      => $skill,
            'species'    => ['none' => 'No restriction'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'skills'     => ['none' => 'No Parent'] + Skill::where('id', '!=', $skill->id)->orderBy('name', 'ASC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'No Category'] + SkillCategory::pluck('name', 'id', 'max_level', 'max_charge')->toArray(),
            'skill_types'=> [SkillType::COSMETIC->value => 'Cosmetic', SkillType::CONSUMABLE->value => 'Consumable',
                SkillType::ITEM_GRANTER->value          => 'Item Granter'],
        ]);
    }

    /**
     * Creates or edits a skill.
     *
     * @param App\Services\SkillService $service
     * @param int|null                  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditSkill(Request $request, SkillService $service, $id = null) {
        $id ? $request->validate(Skill::$updateRules) : $request->validate(Skill::$createRules);
        $data = $request->only([
            'name', 'skill_abrv', 'description', 'skill_category_id', 'species_id', 'image', 'remove_image',
            'is_visible', 'skill_type', 'parent_id', 'parent_level', 'is_backend',
            'override_default_caps', 'ovr_level_cap', 'ovr_charge_cap',
        ]);
        if ($id && $service->updateSkill(Skill::find($id), $data, Auth::user())) {
            flash('Skill updated successfully.')->success();
        } elseif (!$id && $skill = $service->createSkill($data, Auth::user())) {
            flash('Skill created successfully.')->success();

            return redirect()->to('admin/data/skills/edit/'.$skill->id);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the skill deletion modal.
     *
     * @param int $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSkill($id) {
        $skill = Skill::find($id);

        return view('admin.skills._delete_skill', [
            'skill' => $skill,
        ]);
    }

    /**
     * Deletes a skill.
     *
     * @param App\Services\SkillService $service
     * @param int                       $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSkill(Request $request, SkillService $service, $id) {
        if ($id && $service->deleteSkill(Skill::find($id))) {
            flash('Skill deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/skills');
    }

    /**********************************************************************************************

        SKILL TAGS

    **********************************************************************************************/

    /**
     * Gets the tag addition page.
     *
     * @param App\Services\SkillService $service
     * @param int                       $id
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getAddSkillTag(SkillService $service, $id) {
        $skill = Skill::find($id);

        return view('admin.skills.add_tag', [
            'skill' => $skill,
            'tags' => array_diff($service->getSkillTags(), $skill->tags()->pluck('tag')->toArray()),
        ]);
    }

    /**
     * Adds a tag to an skill.
     *
     * @param App\Services\SkillService $service
     * @param int                      $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAddSkillTag(Request $request, SkillService $service, $id) {
        $skill = Skill::find($id);
        $tag = $request->get('tag');
        if ($tag = $service->addSkillTag($skill, $tag, Auth::user())) {
            flash('Tag added successfully.')->success();

            return redirect()->to($tag->adminUrl);
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the tag editing page.
     *
     * @param int   $id
     * @param mixed $tag
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditSkillTag(SkillService $service, $id, $tag) {
        $skill = Skill::find($id);
        $tag = $skill->tags()->where('tag', $tag)->first();
        if (!$skill || !$tag) {
            abort(404);
        }

        return view('admin.skills.edit_tag', [
            'skill' => $skill,
            'tag'  => $tag,
            'data' => $tag->service->getTagData($tag),
        ] + $tag->getEditData());
    }

    /**
     * Edits tag data for an skill.
     *
     * @param App\Services\SkillService $service
     * @param int                      $id
     * @param string                   $tag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditSkillTag(Request $request, SkillService $service, $id, $tag) {
        $skill = Skill::find($id);
        if ($service->editSkillTag($skill, $tag, $request->all(), Auth::user())) {
            flash('Tag edited successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }

    /**
     * Gets the skill tag deletion modal.
     *
     * @param int    $id
     * @param string $tag
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteSkillTag($id, $tag) {
        $skill = Skill::find($id);
        $tag = $skill->tags()->where('tag', $tag)->first();

        return view('admin.skills._delete_skill_tag', [
            'skill' => $skill,
            'tag'  => $tag,
        ]);
    }

    /**
     * Deletes a tag from an skill.
     *
     * @param App\Services\SkillService $service
     * @param int                      $id
     * @param string                   $tag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteSkillTag(Request $request, SkillService $service, $id, $tag) {
        $skill = Skill::find($id);
        if ($service->deleteSkillTag($skill, $tag, Auth::user())) {
            flash('Tag deleted successfully.')->success();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->to('admin/data/skills/edit/'.$skill->id);
    }

    /**
     * Acts on a skill based on the skill's tag.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAct(Request $request) {
        $learned_skill = CharacterSkill::with('skill')->find($request->get('ids'));
        $tag = $request->get('tag');
        $service = $learned_skill->first()->skill->hasTag($tag) ? $learned_skill->first()->skill->tag($tag)->service : null;
        if ($service && $service->act($learned_skill, Auth::user(), $request->all())) {
            flash('Skill used successfully.')->success();
        } elseif (!$learned_skill->first()->skill->hasTag($tag)) {
            flash('Invalid action selected.')->error();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
