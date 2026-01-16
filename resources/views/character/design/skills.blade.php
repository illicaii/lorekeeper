@extends('character.design.layout')

@section('design-title')
    Request (#{{ $request->id }}) :: Skills
@endsection

@section('design-content')
    {!! breadcrumbs(['Design Approvals' => 'designs', 'Request (#' . $request->id . ')' => 'designs/' . $request->id, 'Skills' => 'designs/' . $request->id . '/skills']) !!}

    @include('character.design._header', ['request' => $request])

    <h2>Skills</h2>

    @if ($request->status == 'Draft' && $request->user_id == Auth::user()->id)
        <p>Select the skills for the {{ $request->character->is_myo_slot ? 'created' : 'updated' }} character. @if ($request->character->is_myo_slot)
                Some skills may have been restricted for you - you cannot change them.
            @endif Staff will not be able to modify these skills for you during approval, so if in doubt, please communicate with them beforehand to make sure that your design is acceptable.</p>
        {!! Form::open(['url' => 'designs/' . $request->id . '/skills']) !!}

        <div class="form-group">
            {!! Form::label('Skills') !!}
            <div><a href="#" class="btn btn-primary mb-2" id="add-skill">Add Skill</a></div>
            <div id="skillList">
                {{-- Add in the compulsory skills for MYO slots --}}
                @if ($request->character->is_myo_slot && $request->character->image->skills)
                    @foreach ($request->character->image->skills as $skill)
                        <div class="mb-2 d-flex align-items-center">
                            {!! Form::text('', $skill->name, ['class' => 'form-control mr-2', 'disabled']) !!}
                            {!! Form::text('', $skill->data, ['class' => 'form-control mr-2', 'disabled']) !!}
                            {!! Form::number('', $skill->xp, ['class' => 'form-control mr-2', 'disabled']) !!}
                            <div>{!! add_help('This skill is required.') !!}</div>
                        </div>
                    @endforeach
                @endif

                {{-- Add in the ones that currently exist --}}
                @if ($request->skills)
                    @foreach ($request->skills as $skill)
                        <div class="mb-2 d-flex">
                            {!! Form::select('skill_id[]', $skills, $skill->skill_id, ['class' => 'form-control mr-2 initial skill-select', 'placeholder' => 'Select Skill']) !!}
                            {!! Form::text('skill_data[]', $skill->data, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                            {!! Form::number('skill_xp[]', $skill->xp ? $skill->xp : 0, ['class' => 'form-control mr-2', 'disabled']) !!}
                            <a href="#" class="remove-skill btn btn-danger mb-2">×</a>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="skill-row hide mb-2">
                {!! Form::select('skill_id[]', $skills, null, ['class' => 'form-control mr-2 skill-select', 'placeholder' => 'Select Skill']) !!}
                {!! Form::text('skill_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                {!! Form::text('skill_xp[]', 0, ['class' => 'form-control mr-2', 'disabled']) !!}
                <a href="#" class="remove-skill btn btn-danger mb-2">×</a>
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <h5>Skills</h5>
        <div>
            @if ($request->character && $request->character->is_myo_slot && $request->character->image->skills)
                @foreach ($request->character->image->skills as $skill)
                    <div>
                        @if ($skill->skill->skill_category_id)
                            <strong>{!! $skill->skill->category->displayName !!}:</strong>
                            @endif {!! $skill->skill->displayName !!} @if ($skill->data)
                                ({{ $skill->data }})
                                @endif @if ($skill->xp)
                                    ({{ $skill->xp }})
                                @endif <span class="text-danger">*Required</span>
                    </div>
                @endforeach
            @endif
            @foreach ($request->skills as $skill)
                <div>
                    @if ($skill->skill->skill_category_id)
                        <strong>{!! $skill->skill->category->displayName !!}:</strong>
                        @endif {!! $skill->skill->displayName !!} @if ($skill->data)
                            ({{ $skill->data }})
                        @endif
                        @if ($skill->xp)
                            ({{ $skill->xp }})
                        @endif
                </div>
            @endforeach
        </div>
    @endif

@endsection

@section('scripts')
    @include('widgets._image_upload_js')

    <script></script>
@endsection
