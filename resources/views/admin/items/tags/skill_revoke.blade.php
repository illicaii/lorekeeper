
@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => false])

<h3>Skill Remove</h3>
<div class="mt-2 mb-2">
    {!! Form::label('skill_item_type', 'Select Skill Item Type') !!}
    {!! Form::select('skill_item_type', $item_types, isset($tag->getData()['skill_item_type']) ? $tag->getData()['skill_item_type'] : '2', ['class' => 'form-control skill-select selectize']) !!}
</div>
<div class="mt-2 mb-2">
    {!! Form::label('grant_type', 'Select Removal Type') !!}
    {!! Form::select('grant_type', $grant_types, isset($tag->getData()['grant_type']) ? $tag->getData()['grant_type'] : 0, ['class' => 'form-control skill-select selectize']) !!}
</div>
<hr>
<div>
    <div class="card card-body">
        <div class="row">
            <div class="text-right mb-3 col" id="Data">
                <a href="#" class="btn btn-outline-info" id="addLoot">Add Reward</a>
            </div>
        </div>
        <div>
            <table class="table table-sm" id="lootTable">
                <thead>
                    <tr>
                        <th width="90%">Skill</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody id="lootTableBody">
                    @if (is_array($tag->getData()) && isset($tag->getData()['rewards']))
                        @foreach ($tag->getData()['rewards'] as $loot)
                            <tr class="loot-row">
                                <td class="loot-row-select">
                                    {!! Form::select('rewardable_id[]', $skills, $loot->rewardable_id, ['class' => 'form-control skill-select selectize', 'placeholder' => 'Select Skill']) !!}
                                </td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<hr>
