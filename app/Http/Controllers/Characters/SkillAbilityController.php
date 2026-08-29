<?php

namespace App\Http\Controllers\Characters;

use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Services\SkillManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillAbilityController extends Controller {
    /**
     * Handles skill ability processing.
     *
     * @param App\Services\SkillManager $service
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSkillAbilityClaim(Request $request, SkillManager $service) {
        if (!Auth::check()) {
            abort(404);
        }
        switch ($request->get('action')) {
            default:
                flash('Invalid action selected.')->error();
                break;
            case 'act':
                $sender = Auth::user();
                $recipient = $this->postAbilityAct($request);
        }

        return redirect()->back();
    }

    /**
     * Acts on an ability based on the ability's tag.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function postAbilityAct(Request $request) {
        $character = Character::where('slug', $request->get('slug'))->get()->first();
        $ability = $character->image->skills->where('skill_id', $request->get('skill_id'))->first();
        $tag = $request->get('tag');
        $service = $ability->skill->hasTag($tag) ? $ability->skill->tag($tag)->service : null;

        if ($ability->getAvailableCharges() <= 0) {
            flash('Skill ability has no more uses.')->error();
        } elseif ($service && $rewardString = $service->act($ability, Auth::user(), $character, $ability->skill->tag($tag))) {
            flash('Skill ability used successfully. '.$rewardString)->success();
        } elseif (!$ability->skill->hasTag($tag)) {
            flash('Invalid action selected.')->error();
        } else {
            foreach ($service->errors()->getMessages()['error'] as $error) {
                flash($error)->error();
            }
        }

        return redirect()->back();
    }
}
