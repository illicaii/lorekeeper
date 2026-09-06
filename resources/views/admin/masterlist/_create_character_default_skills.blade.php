@if (count($default_skills) > 0)
    @foreach ($default_skills as $skill)
        <div class="mb-2 d-flex align-items-center">
            {!! Form::text('', $skill->name, ['class' => 'form-control mr-2', 'disabled']) !!}
        </div>
    @endforeach
@endif
