<li class="list-group-item">
    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#redeemSkill">Use Set Reset Item</a>
    <div id="redeemSkill" class="collapse">
        @php $skill_options = App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name','id') @endphp
        {!! Form::hidden('tag', $tag->tag) !!}

        <p>
            This item will reset the level of the selected character's skill(s). Consuming this item is not reversible.
        </p>
        <div class="form-group">
            {!! Form::label('character_id', 'Characters') !!} {!! add_help('Select Character') !!}
            {!! Form::select('character_id', $user->characters->pluck('fullName', 'id', 'image'), null, ['class' => 'form-control default skill-select']) !!}
        </div>

        <!-- RESET Skill -->
        <div>
            @if ($tag->getData()['grant_type'] == App\Enums\Skill\ItemGrantType::ALL->value || count($tag->getData()['rewards']) == 1)
                <p class="mb-0"><strong>Skill:</strong></p>
            @else
                <p class="mb-0"><strong>Skills from Pool:</strong></p>
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
                    {!! Form::label('selected_skill', 'Select Skill') !!} {!! add_help('Select the skill affect you would like to reset on this character') !!}
                    {!! Form::select('selected_skill', App\Models\Skill\Skill::find($tag->getData()['skill_opt'])->pluck('name', 'id'), null, ['class' => 'form-control default skill-select']) !!}
                </div>
            @endif
            @if ($tag->getData()['skill_item_type'] == App\Enums\Skill\ItemType::REVOKE->value)
                <!-- REVOKE Skill -->
                <p><strong>This item will remove the skill from your character regardless of the current level.</strong></p>
            @else
                <p><strong>This item will reset the skill level of your character to lv 1 regardless of the current level.</strong></p>
            @endif
        </div>

        <div class="text-right">
            {!! Form::button('Redeem Item', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
        </div>
    </div>
</li>

<script>
    $(document).ready(function() {
        $('.default.skill-select').selectize();
    });
</script>
