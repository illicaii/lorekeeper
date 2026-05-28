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
                @if ($request->character->is_myo_slot)
                    @if ($request->character->image->skills)
                        @foreach ($request->character->image->skills as $skill)
                            <div class="mb-2 d-flex align-items-center">
                                {!! Form::text('', $skill->name, ['class' => 'form-control mr-2', 'disabled']) !!}
                                {!! Form::text('', $skill->data, ['class' => 'form-control mr-2', 'disabled']) !!}
                                <div>{!! add_help('This skill is required.') !!}</div>
                            </div>
                        @endforeach
                    @endif
                    @if ($request->character->image->species->getDefaultSkills())
                        <p>These are skills all characters will start with. They can not be removed or edited.</p>
                        @foreach ($request->character->image->species->getDefaultSkills() as $skill)
                            <div class="mb-2 d-flex align-items-center">
                                {!! Form::text('', $skill->name, ['class' => 'form-control mr-2', 'disabled']) !!}
                                <div>{!! add_help('This skill is required.') !!}</div>
                            </div>
                        @endforeach
                    @endif
                @endif

                <p>Additional Skills:</p>
                {{-- Add in the ones that currently exist --}}
                @if ($request->skills)
                    @foreach ($request->skills as $skill)
                        @if (!$skill->is_backend)
                            <div class="mb-2 d-flex">
                                {!! Form::select('skill_id[]', $skills, $skill->skill_id, ['class' => 'form-control mr-2 initial skill-select', 'placeholder' => 'Select Skill']) !!}
                                {!! Form::text('skill_data[]', $skill->data, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
                                <a href="#" class="remove-skill btn btn-danger mb-2">×</a>
                            </div>
                        @else
                            <div class="mb-2 d-flex" style="display: none !important;">
                                {!! Form::select('skill_id[]', $skills, $skill->skill_id, ['class' => 'form-control mr-2 initial skill-select']) !!}
                                {!! Form::text('skill_data[]', $skill->data, ['class' => 'form-control mr-2']) !!}
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
            <div class="skill-row hide mb-2">
                {!! Form::select('skill_id[]', $skills, null, ['class' => 'form-control mr-2 skill-select', 'placeholder' => 'Select Skill']) !!}
                {!! Form::text('skill_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
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
            @if ($request->character && $request->character->is_myo_slot)
                @if ($request->character->image->skills)
                    @foreach ($request->character->image->skills as $skill)
                        <div>
                            @if ($skill->skill->skill_category_id)
                                <strong>{!! $skill->skill->category->displayName !!}:</strong>
                                @endif {!! $skill->skill->displayName !!} @if ($skill->data)
                                    ({{ $skill->data }})
                                    @endif @if ($skill->xp && (Auth::check() && Auth::user()->hasPower('manage_characters')))
                                        ({{ $skill->xp }})
                                    @endif <span class="text-danger">*Required</span>
                        </div>
                    @endforeach
                @endif
                @if ($request->character->image->species->getDefaultSkills())
                    @foreach ($request->character->image->species->getDefaultSkills(1, 1) as $skill)
                        @if (!$skill->is_backend || (Auth::check() && Auth::user()->hasPower('manage_characters')))
                            <div>
                                @if ($skill->skill_category_id)
                                    <strong>{!! $skill->category->displayName !!}:</strong>
                                @endif {!! $skill->displayName !!}
                                @if ($skill->data)
                                    ({{ $skill->data }})
                                @endif
                                @if ($skill->xp && (Auth::check() && Auth::user()->hasPower('manage_characters')))
                                    ({{ $skill->xp }})
                                @endif
                                <span class="text-danger">*Default</span>
                            </div>
                        @endif
                    @endforeach
                @endif
            @endif
            @foreach ($request->skills as $skill)
                @if (!$skill->is_backend || (Auth::check() && Auth::user()->hasPower('manage_characters')))
                    <div>
                        @if ($skill->skill->skill_category_id)
                            <strong>{!! $skill->skill->category->displayName !!}:</strong>
                        @endif
                        @if ($skill->is_backend)
                            <i class="fas fa-key mr-1"></i>
                        @endif
                        {!! $skill->skill->displayName !!}
                        @if ($skill->data)
                            ({{ $skill->data }})
                        @endif
                        @if ($skill->xp && (Auth::check() && Auth::user()->hasPower('manage_characters')))
                            (XP: {{ $skill->xp }})
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @endif

@endsection

@section('scripts')
    @include('widgets._image_upload_js')

    <script></script>
@endsection
