<?php

namespace App\Http\Controllers\Admin\Data;

use App\Http\Controllers\Controller;
use App\Models\Prompt\Prompt;
use App\Models\Skill\Skill;
use App\Models\Skill\SkillCategory;
use App\Services\SkillService;
use App\Models\Species\Species;
use Auth;
use Illuminate\Http\Request;

class SkillController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Admin / Skill Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character skill categories and features
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
            'name', 'description', 'image', 'remove_image', 'is_default', 'is_visible',
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
    public function getSkillIndex() {
        // todo add query filtering
        return view('admin.skills.index', [
            'skills' => Skill::orderBy('name', 'ASC')->get(),
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
            'skills'     => ['none' => 'No Parent/Prerequisite'] + Skill::orderBy('name', 'ASC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'Any Category'] + SkillCategory::pluck('name', 'id', 'max_level', 'max_charge')->toArray(),
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
            'skills'     => ['none' => 'No Parent/Prerequisite'] + Skill::where('id', '!=', $skill->id)->orderBy('name', 'ASC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'Any Category'] + SkillCategory::pluck('name', 'id', 'max_level', 'max_charge')->toArray(),
        ]);
    }

    /**
     * Creates or edits an skill.
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
            'is_visible', 'skill_type', 'parent_id', 'parent_level', 'prerequisite_id',
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
}
