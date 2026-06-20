<div id="rewardRowData" class="hide">
    <table class="table table-sm">
        <tbody id="rewardRow">
            <tr class="reward-row">
                <td>{!! Form::select('rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table'], null, [
                    'class' => 'form-control reward-type',
                    'placeholder' => 'Select Reward Type',
                ]) !!}</td>
                <td class="reward-row-select"></td>
                <td>{!! Form::text('quantity[]', 1, ['class' => 'form-control']) !!}</td>
                <td>{!! Form::text('charges[]', 1, ['class' => 'form-control']) !!}</td>
                {!! Form::hidden('sublist_id[]', null, ['class' => 'subtable-id']) !!}
                <td class="text-right"><a href="#" class="btn btn-danger remove-reward-button">Remove</a></td>
            </tr>
        </tbody>
    </table>
    {!! Form::select('rewardable_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('rewardable_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
    {!! Form::select('rewardable_id[]', $tables, null, ['class' => 'form-control table-select', 'placeholder' => 'Select Loot Table']) !!}
</div>

<div id="sublistRowData" class="hide">
    <div id="sublistRow">
        <div class="sublist-row">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td>{!! Form::text('min_lvl[]', 1, ['class' => 'form-control']) !!}</td>
                        <td>{!! Form::text('max_lvl[]', 1, ['class' => 'form-control']) !!}</td>
                        <td>{!! Form::hidden('breakpoint_id[]', null, ['class' => 'subtable-id']) !!}</td>
                        <td class="text-right"><a href="#" class="btn btn-danger remove-sublist" id="button-addon2">Remove</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $rewardRow = $('#rewardRow').find('.reward-row');
            var $itemSelect = $('#rewardRowData').find('.item-select');
            var $currencySelect = $('#rewardRowData').find('.currency-select');
            var $tableSelect = $('#rewardRowData').find('.table-select');
            var $skillSelect = $('#rewardRowData').find('.skill-select');

            var $sublistRow = $('#sublistRow').find('.sublist-row');

            $('.rewardTableBody .selectize').selectize();
            attachRemoveListener($('.rewardTableBody .remove-reward-button'));

            $('.addLoot').on('click', function(e) {
                e.preventDefault();
                var $clone = $rewardRow.clone();
                $(this).parent().parent().find('.rewardTable').first().append($clone);
                attachRewardTypeListener($clone.find('.reward-type'));
                attachRemoveListener($clone.find('.remove-reward-button'));
                $clone.find('.subtable-id').attr('value', $(this).attr("value"));
            });

            $('.reward-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.reward-row-select');

                var $clone = null;
                if (val == 'Item') $clone = $itemSelect.clone();
                else if (val == 'Currency') $clone = $currencySelect.clone();
                else if (val == 'LootTable') $clone = $tableSelect.clone();
                else if (val == 'SkillGrant') $clone = $skillSelect.clone();
                else if (val == 'SkillXP') $clone = $skillSelect.clone();
                else if (val == 'SkillLevel') $clone = $skillSelect.clone();

                $cell.html('');
                $cell.append($clone);
                var row_num = $(this).parent().parent().index();
            });

            function attachRewardTypeListener(node) {
                node.on('change', function(e) {
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.reward-row-select');

                    var $clone = null;
                    if (val == 'Item') $clone = $itemSelect.clone();
                    else if (val == 'Currency') $clone = $currencySelect.clone();
                    else if (val == 'LootTable') $clone = $tableSelect.clone();
                    else if (val == 'SkillGrant') $clone = $skillSelect.clone();
                    else if (val == 'SkillXP') $clone = $skillSelect.clone();
                    else if (val == 'SkillLevel') $clone = $skillSelect.clone();

                    $cell.html('');
                    $cell.append($clone);
                    var row_num = $(this).parent().parent().index();
                });
            }

            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }

            // Conditional Loot Sub-tables
            $('.criteria-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().parent().find('.sublist-row-select');

                var $clone = null;

                $cell.html('');
                $cell.append($clone);
                var row_num = $(this).parent().parent().index();
            });

            function attachSublistTypeListener(node) {
                node.on('change', function(e) {
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.sublist-row-select');

                    var $clone = null;

                    $cell.html('');
                    $cell.append($clone);
                    var row_num = $(this).parent().parent().index();
                });
            }

            $('#add-sublist').on('click', function(e) {
                e.preventDefault();
                addSublistRow();
            });
            $('.remove-sublist').on('click', function(e) {
                e.preventDefault();
                removeSublistRow($(this));
            })

            function addSublistRow() {
                var $clone = $sublistRow.clone();
                attachSublistTypeListener($clone.find('.criteria-type'));

                $('#sublistList').append($clone);
                $clone.removeClass('hide sublist-row');
                $clone.find('.remove-sublist').on('click', function(e) {
                    e.preventDefault();
                    removeSublistRow($(this));
                });
                attachSublistListeners($clone);
            }

            function removeSublistRow($trigger) {
                $trigger.parent().parent().parent().parent().parent().remove();
            }

            $('#sublistList .sublist-list-entry').each(function(index) {
                attachSublistListeners($(this));
            });

            function attachSublistListeners(node) {
                node.find('.add-sublist-row').on('click', function(e) {
                    e.preventDefault();
                    var $clone = $rewardRow.clone();
                    $(this).parent().parent().find('.sublist-rewards').append($clone);

                    attachRewardTypeListener($clone.find('.reward-type'));
                    attachRemoveListener($clone.find('.remove-reward-button'));
                    attachWeightListener($clone.find('.reward-weight'));
                    $clone.find('.subtable-id').attr('value', $(this).attr("value"));
                    refreshChances();
                });
            }
        });
    </script>
@endsection