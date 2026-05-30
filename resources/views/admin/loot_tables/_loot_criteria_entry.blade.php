<tr class="sublist-row">
    <td>{!! Form::select(
            'sublist_criteria_type[]',
            ['Skill' => 'Skill'],
            $sublist['criteria_type'],
            ['class' => 'form-control criteria-type', 'placeholder' => 'Select Criteria Type']
        ) !!}
    </td>
    <td class="sublist-row-select">
        @if ($sublist['criteria_type'] == 'Skill')
            {!! Form::select('sublist_id[]', $skills, $sublist['criteria_id'], ['class' => 'form-control', 'placeholder' => 'Select Skill', 'aria-label' => 'Skill']) !!}
        @endif
    </td>
    <td>{!! Form::select('sublist_criteria[]', ['=' => '=', '<' => '<', '>' => '>', '<=' => '<=', '>=' => '>='], $sublist['criteria'], ['class' => 'form-control', 'placeholder' => 'Select Condition', 'aria-label' => 'Criteria']) !!}</td>
    <td>{!! Form::number('sublist_quantity[]', $sublist['quantity'], ['class' => 'form-control', 'placeholder' => 'Enter Skill Level Breakpoint', 'aria-label' => 'Skill Level']) !!}</td>
    <td class="text-right"><a href="#" class="btn btn-danger remove-sublist" id="button-addon2">Remove</a></td>
</tr>
