@extends('admin.layout')

@section('admin-title')
    Edit Skill Tag
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills', 'Edit Skill' => 'admin/data/skills/edit/' . $skill->id, 'Edit Tag Settings - ' . $tag->tag => 'admin/data/skills/tag/' . $skill->id . '/' . $tag->tag]) !!}

    <h1>
        Edit Tag Settings - {!! $tag->displayTag !!}
        <a href="#" class="btn btn-outline-danger float-right delete-tag-button">Delete Tag</a>
    </h1>

    <p>Edit the parameters for the tag on this skill. Note that for the skill tag to take effect (e.g. become a usable), you will need to turn on the Active toggle. (Conversely, you can turn it off to prevent users from using it while preserving
        the old settings for future use.)</p>

    @if (View::exists('admin.skills.tags.' . $tag->tag . '_pre'))
        @include('admin.skills.tags.' . $tag->tag . '_pre', ['skill' => $skill, 'tag' => $tag])
    @endif
    {!! Form::open(['url' => 'admin/data/skills/tag/' . $skill->id . '/' . $tag->tag]) !!}

    @if (View::exists('admin.skills.tags.' . $tag->tag))
        @include('admin.skills.tags.' . $tag->tag, ['skill' => $skill, 'tag' => $tag])
    @endif

    {!! Form::checkbox('is_active', 1, $tag->is_active, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Active', ['class' => 'form-check-label ml-3']) !!}

    <div class="text-right">
        {!! Form::submit('Edit Tag Settings', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
    @if (View::exists('admin.skills.tags.' . $tag->tag . '_post'))
        @include('admin.skills.tags.' . $tag->tag . '_post', ['skill' => $skill, 'tag' => $tag])
    @endif
@endsection

@section('scripts')
    @parent
    @if (View::exists('js.admin_skills.' . $tag->tag))
        @include('js.admin_skills.' . $tag->tag, ['skill' => $skill, 'tag' => $tag])
    @endif
    <script>
        $(document).ready(function() {
            $('.delete-tag-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/skills/delete-tag') }}/{{ $skill->id }}/{{ $tag->tag }}", 'Delete Tag');
            });
        });
    </script>
@endsection
