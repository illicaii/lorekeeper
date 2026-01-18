@if($image)
    {!! Form::open(['url' => 'admin/character/image/'.$image->id.'/skills/remove']) !!}

    <p>You are about to <strong>remove all skills</strong> from this character.
            This is not reversible. <br> Are you sure you want to remove skills?</p>

    <div class="text-center">
        {!! Form::submit('Remove Character Skills', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid character selected.
@endif