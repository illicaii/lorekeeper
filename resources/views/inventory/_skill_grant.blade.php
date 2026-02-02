@if ($tag->getData()['skill_item_type'] == App\Enums\Skill\ItemType::GRANT->value  || $tag->getData()['skill_item_type'] == App\Enums\Skill\ItemType::ADD->value)
<li class="list-group-item">
    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#redeemSkill">Redeem Skill Item</a>
    <div id="redeemSkill" class="collapse">
        @php $skill_options = App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name','id') @endphp
        {!! Form::hidden('tag', $tag->tag) !!}

        <p>
            This item will grant a character of your choice the following skill feature. Consuming this item is not reversible.
        </p>
        <div class="form-group">
            {!! Form::label('character_id', 'Characters') !!} {!! add_help('Select Character') !!}
            {!! Form::select('character_id', $user->characters->pluck('fullName', 'id', 'image'), null, ['class' => 'form-control default skill-select']) !!}
        </div>

        <!-- Skill Learn -->
        @if ($tag->getData()['skill_item_type'] == App\Enums\Skill\ItemType::GRANT->value)
            <div>
                @if (count($tag->getData()['rewards']) == 1)
                    <p class="mb-0"><strong>Skill Learned:</strong></p>
                @elseif ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::ALL->value)
                    <p class="mb-0"><strong>Skills Learned:</strong></p>
                @else
                    <p class="mb-0"><strong>Skill Pool:</strong></p>
                @endif
                <div class="row mb-2">
                    @if (count($tag->getData()['rewards']))
                        <div class="col">
                            <ul>
                                @foreach ($tag->getData()['rewards'] as $loot)
                                    <li>{!! App\Models\Skill\Skill::find($loot->rewardable_id)->displayName !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                @if ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::SELECTOR->value && count($tag->getData()['rewards']) > 1)
                    <div class="form-group">
                        {!! Form::label('selected_skill', 'Select Skill') !!} {!! add_help('Select the skill affect you would like to add to this character') !!}
                        {!! Form::select('selected_skill', App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name', 'id'), null, ['class' => 'form-control default skill-select']) !!}
                    </div>
                @elseif (count($tag->getData()['rewards']) > 1 && !$tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::ALL->value)
                    <p>You will receive a random skill from this list. If your character already has all the skills in this list, the item will not be consumed.</p>
                @endif
            </div>

        <!-- ADD XP or level -->
        @elseif ($tag->getData()['skill_item_type'] == App\Enums\Skill\ItemType::ADD->value)
            <div>
                @if ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::ALL->value || count($tag->getData()['rewards']) == 1)
                    <p class="mb-0"><strong>Skill XP Added:</strong></p>
                @else
                    <p class="mb-0"><strong>Skill XP Pool:</strong></p>
                @endif
                <div class="row mb-2">
                    @if (count($tag->getData()['rewards']))
                        <div class="col">
                            <ul>
                                @foreach ($tag->getData()['rewards'] as $loot)
                                    <li>{!! App\Models\Skill\Skill::find($loot->rewardable_id)->displayName !!} +{!! $loot->quantity !!}xp</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                @if ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::SELECTOR->value && count($tag->getData()['rewards']) > 1)
                    <div class="form-group">
                        {!! Form::label('selected_skill', 'Select Skill') !!} {!! add_help('Select the skill affect you would like to add to this character') !!}
                        {!! Form::select('selected_skill', App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name', 'id'), null, ['class' => 'form-control default skill-select']) !!}
                    </div>
                @else
                    <p>
                        Skills can not be applied to characters which lack any skill the xp is granting. If your character does not have any skill in this list, this item it will not be consumed.
                        <strong>The item will still be consumed if your character has any skill on this list.</strong>
                    </p>
                @endif
            </div>
        @endif

        <div class="text-right">
            {!! Form::button('Redeem Item', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
        </div>
    </div>
</li>

@elseif ($tag->getData()['skill_item_type'] == App\Enums\Skill\ItemType::SET->value)
    <li class="list-group-item">
        <a class="card-title h5 collapse-title" data-toggle="collapse" href="#redeemSkill">Redeem Set Skill Item</a>
        <div id="redeemSkill" class="collapse">
            @php $skill_options = App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name','id') @endphp
            {!! Form::hidden('tag', $tag->tag) !!}

            <p>
                This item will set the level of your character's skill(s). Consuming this item is not reversible.
            </p>
            <div class="form-group">
                {!! Form::label('character_id', 'Characters') !!} {!! add_help('Select Character') !!}
                {!! Form::select('character_id', $user->characters->pluck('fullName', 'id', 'image'), null, ['class' => 'form-control default skill-select']) !!}
            </div>

            <!-- RESET Skill -->
            <div>
                @if ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::ALL->value || count($tag->getData()['rewards']) == 1)
                    <p class="mb-0"><strong>Skill(s):</strong></p>
                @else
                    <p class="mb-0"><strong>Skills from Pool:</strong></p>
                @endif
                <div class="row mb-2">
                    @if (count($tag->getData()['rewards']))
                        <div class="col">
                            <ul>
                                @foreach ($tag->getData()['rewards'] as $loot)
                                    <li>{!! App\Models\Skill\Skill::find($loot->rewardable_id)->displayName !!} set to lv. {!! $loot->quantity !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                @if ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::SELECTOR->value && count($tag->getData()['rewards']) > 1)
                    <div class="form-group">
                        {!! Form::label('selected_skill', 'Select Skill') !!} {!! add_help('Select the skill affect you would like to reset on this character') !!}
                        {!! Form::select('selected_skill', App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name', 'id'), null, ['class' => 'form-control default skill-select']) !!}
                    </div>
                @endif
                <p><strong>This item will set the skill level of your character regardless of the current level. This could decrease the level.</strong></p>
            </div>

            <div class="text-right">
                {!! Form::button('Redeem Item', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
            </div>
        </div>
    </li>
@endif

<script>
    $(document).ready(function() {
        $('.default.skill-select').selectize();
    });
</script>
