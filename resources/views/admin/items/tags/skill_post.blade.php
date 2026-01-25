<div id="lootRowData" class="hide">
    <table class="table table-sm">
        <tbody id="lootRow">
            <tr class="loot-row">
                <td class="loot-row-select">
                    {!! Form::select('rewardable_id[]', $skills, null, ['class' => 'form-control skill-select', 'placeholder' => 'Select Skills']) !!}
                </td>
                <td>{!! Form::text('quantity[]', 1, ['class' => 'form-control']) !!}</td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
            </tr>
        </tbody>
    </table>

</div>
