@if($image)
    {!! Form::open(['url' => 'admin/character/image/'.$image->id.'/skills/reset']) !!}

    <p>You are about to <strong>reset all skills levels</strong> for this character.
        This is not reversible. <br> Are you sure you want to reset skills?</p>

    <div class="text-center">
        {!! Form::submit('Reset Character Skills', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid character selected.
@endif