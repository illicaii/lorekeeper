<h3>Drops</h3>
<p>This currently does nothing</p>
<p>These are the rewards that will be distributed to the character when they use the skill action based on the skill level matching the breakpoints. The
     box will only distribute rewards to the characters themselves - user-only rewards should not be added.</p>

<div>
    <div class="card card-body">
        <h5>Breakpoints</h5>
        <p>The number of charges a character has can be calculated by the formula: </p>
        <ul>
            <li>character_charges = ceil((max_charges/max_levels )*character_lvl)</li>
        </ul>
        <p>If a skill has no level, charges will be the greater of max_charges or 1. Max Charges and levels are selected on a Skill or Category basis.</p>

        <p>For example, if a character has 7 levels in a skill that has 10 charges and 20 levels, the character will have 4 charges (3.5 rounded up)</p>

        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="add-sublist">Add Breakpoint</a>
        </div>

        <div id="sublistList" class="my-4">
            @if (isset($data))
                @foreach ($data as $key => $sublist)
                    <div class="card p-3 mb-3 bg-light">
                        <h5>Breakpoint Range</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="45%">Min Level (Inclusive)</th>
                                    <th width="45%">Max Level (Exclusive)</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <td>{!! Form::text('min_lvl[]', $sublist['min_lvl'], ['class' => 'form-control']) !!}</td>
                                <td>{!! Form::text('max_lvl[]', $sublist['max_lvl'], ['class' => 'form-control']) !!}</td>
                                {!! Form::hidden('breakpoint_id[]', $key, ['class' => 'subtable-id']) !!}
                                <td class="text-right"><a href="#" class="btn btn-danger remove-sublist" id="button-addon2">Remove Breakpoint</a></td>
                            </tbody>
                        </table>

                        <p>Drops will drop all rewards in this group provided character has sufficient charges until no more charges can be debited.</p>

                        <h5>Rewards</h5>
                        <div class="text-right mb-3">
                            <a href="#" class="btn btn-info addLoot" value="{{ $key }}">Add Reward</a>
                        </div>
                        <table class="table table-sm rewardTable">
                            <thead>
                                <tr>
                                    <th width="25%">Reward Type</th>
                                    <th width="35%">Reward</th>
                                    <th width="10%">Quantity</th>
                                    <th width="20%">Charges Consumed</th>
                                    <th width="10%"></th>
                                </tr>
                            </thead>
                            <tbody class="rewardTableBody">
                                @if($sublist['rewards'])
                                    @foreach ($sublist['rewards']['rewardable_type'] as $sub_key => $reward)
                                        <tr class="reward-row">
                                            <td>
                                                {!! Form::select(
                                                    'rewardable_type[]',
                                                    ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table'],
                                                    $reward,
                                                    ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type',]
                                                ) !!}
                                            </td>
                                            <td class="reward-row-select">
                                                @if ($reward == 'Item')
                                                    {!! Form::select('rewardable_id[]', $items, $sublist['rewards']['rewardable_id'][$sub_key], ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                                                @elseif($reward == 'Currency')
                                                    {!! Form::select('rewardable_id[]', $currencies, $sublist['rewards']['rewardable_id'][$sub_key], ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                                                @elseif($reward == 'LootTable')
                                                    {!! Form::select('rewardable_id[]', $tables, $sublist['rewards']['rewardable_id'][$sub_key], ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                                                @endif
                                            </td>
                                            <td>{!! Form::text('quantity[]', $sublist['rewards']['quantity'][$sub_key], ['class' => 'form-control']) !!}</td>
                                            <td>{!! Form::text('charges[]', $sublist['rewards']['charges'][$sub_key], ['class' => 'form-control']) !!}</td>
                                            {!! Form::hidden('sublist_id[]', $key, ['class' => 'subtable-id']) !!}
                                            <td class="text-right"><a href="#" class="btn btn-danger remove-reward-button">Remove</a></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <hr />
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
<hr>
