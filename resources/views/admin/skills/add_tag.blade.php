@extends('admin.layout')

@section('admin-title')
    Add Skill Tag
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills', 'Edit Skill' => 'admin/data/skills/edit/' . $skill->id, 'Add Skill Tag' => 'admin/data/skills/tag/' . $skill->id]) !!}

    <h1>Add Skill Tag</h1>

    <p>Select an skill tag to add to the skill. You cannot add duplicate tags to the same skill (they are removed from the selection). You will be taken to the parameter editing page after adding the tag. </p>

    {!! Form::open(['url' => 'admin/data/skills/tag/' . $skill->id]) !!}

    <div class="form-group">
        {!! Form::label('tag', 'Tag') !!}
        {!! Form::select('tag', [0 => 'Select a Tag'] + $tags, null, ['class' => 'form-control']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Add Tag', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection
