@include('js._loot_js', ['showLootTables' => true, 'showRaffles' => false])

<h3>Skill Grant Item</h3>
<div class="mt-2 mb-2">
    {!! Form::label('skill_item_type', 'Select Skill Item Type') !!}
    {!! Form::select('skill_item_type', $item_types, isset($tag->getData()['skill_item_type']) ? $tag->getData()['skill_item_type'] : '0', ['class' => 'form-control skill-select selectize']) !!}
</div>
<hr>
<!-- Skill/Lvl/XP Grant -->
<div>
    <div class="card card-body">
        <div class="row">
            <div class="mb-3 col-sm-auto">
                {!! Form::hidden('grant_level', 0) !!}
                {!! Form::checkbox('grant_level', 1, isset($tag->getData()['grant_level']) ? $tag->getData()['grant_level'] : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-on' => 'Add level', 'data-off' => 'Add XP']) !!}
                {!! add_help('Add selected skills as level or XP points. This option does nothing for the "Set level Skill" Item Type.') !!}
            </div>
            <div class="mb-3 col-sm-auto">
                {!! Form::hidden('populate_start_level', 0) !!}
                {!! Form::checkbox('populate_start_level', 1, isset($tag->getData()['populate_start_level']) ? $tag->getData()['populate_start_level'] : 0, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                    'data-on' => 'Override quantity with random level from category range',
                    'data-off' => 'Use Quantity Value Below',
                ]) !!}
                {!! add_help(
                    'If turned on, the skill quantity will be assigned a random xp value corresponding to a level between the MIN and MAX level from the skill category, ignoring the quantity set below. Take caution using this option with the add level toggle and/or "add" item types',
                ) !!}
            </div>
            <div class="text-right mb-3 col" id="Data">
                <a href="#" class="btn btn-outline-info" id="addLoot">Add Reward</a>
            </div>
        </div>
        <div>
            <table class="table table-sm" id="lootTable">
                <thead>
                    <tr>
                        <th width="35%">Reward</th>
                        <th width="20%">Quantity (Points or Levels)</th>
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
                                <td>{!! Form::text('quantity[]', $loot->quantity, ['class' => 'form-control']) !!}</td>
                                <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <hr>
    <h3>Grant Rules</h3>
    <p> Choose whether this item will grant one, all, or random skills from the pool and if the skill needs to be known by the character beforehand.</p>
    <div class="mt-2 mb-2">
        {!! Form::select('grant_type', $grant_types, isset($tag->getData()['grant_type']) ? $tag->getData()['grant_type'] : 0, ['class' => 'form-control skill-select selectize']) !!}
    </div>
    <div class="mt-2 mb-2">
        {!! Form::hidden('error_on_missing', 0) !!}
        {!! Form::checkbox('error_on_missing', 1, isset($tag->getData()['error_on_missing']) ? $tag->getData()['error_on_missing'] : 1, [
            'class' => 'form-check-input',
            'data-toggle' => 'toggle',
            'data-on' => 'Error for unlearned skill',
            'data-off' => 'Consume and grant regardless of learned status',
        ]) !!}
    </div>
</div>

<hr>
