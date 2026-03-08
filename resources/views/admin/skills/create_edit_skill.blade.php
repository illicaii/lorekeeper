@extends('admin.layout')

@section('admin-title')
    {{ $skill->id ? 'Edit' : 'Create' }} Skills
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills', ($skill->id ? 'Edit' : 'Create') . ' Skill' => $skill->id ? 'admin/data/skills/edit/' . $skill->id : 'admin/data/skills/create']) !!}

    <h1>{{ $skill->id ? 'Edit' : 'Create' }} Skill
        @if ($skill->id)
            <a href="#" class="btn btn-outline-danger float-right delete-skill-button">Delete Skill</a>
        @endif
    </h1>

    {!! Form::open(['url' => $skill->id ? 'admin/data/skills/edit/' . $skill->id : 'admin/data/skills/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $skill->name, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('Abbreviation') !!}
        {!! Form::text('skill_abrv', $skill->skill_abrv, ['class' => 'form-control']) !!}
    </div>
    <div class="row">
        <div class="col-md-6 form-group">
            {!! Form::label('Skill Category (Optional)') !!}
            {!! Form::select('skill_category_id', $categories, $skill->skill_category_id, ['class' => 'form-control']) !!}
        </div>
        <div class="col-md-6 form-group">
            {!! Form::label('Species Restriction (Optional)') !!}
            {!! Form::select('species_id', $species, $skill->species_id, ['class' => 'form-control', 'id' => 'species']) !!}
        </div>
    </div>
    <div class="form-group">
        <p>This needs to be removed when skill tags are implemented</p>
        {!! Form::label('skill_type', 'Select Skill Item Type') !!}
        {!! Form::select('skill_type', $skill_types, isset($skill->skill_type) ? $skill->skill_type : '0', ['class' => 'form-control skill-select selectize']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($skill->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $skill->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $skill->is_visible ? $skill->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the trait will not be visible in the trait list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>
    <div class="form-group">
        {!! Form::checkbox('is_backend', 1, $skill->is_backend ? $skill->is_backend : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_backend', 'Is Backend', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the trait will not be visible to players. This is meant to be used for hidden RNG based skills.') !!}
    </div>
    <div class="form-group">
        <a data-toggle="collapse" href="#collapseOverride" role="button" aria-expanded="false" aria-controls="collapseOverride">
            {!! Form::checkbox('override_default_caps', 1, $skill->override_default_caps ? $skill->override_default_caps : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('override_default_caps', 'Override skill category defaults', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the max level and max charge fields will be used instead of the category defaults.') !!}
        </a>
    </div>
    <div class="collapse {{  $skill->override_default_caps ? 'show' : '' }} card p-3 mb-3" id="collapseOverride">
        <div class="form-group">
            {!! Form::label('Max Level (Optional)') !!} {!! add_help('The max level this skills in this category can be leveled to. (aka. The level cap.) Leave blank if you do not wish to have levels') !!}
            {!! Form::number('ovr_level_cap', $skill->ovr_level_cap ? $skill->ovr_level_cap : 0, ['class' => 'form-control', 'min' => 0]) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Max Charges (Optional)') !!} {!! add_help('The max level of charges skills in this category can have. Leave this blank for categories with no charges') !!}
            {!! Form::number('ovr_charge_cap', $skill->ovr_charge_cap ? $skill->ovr_charge_cap : 0, ['class' => 'form-control', 'min' => 0]) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Parent (Optional)') !!} {!! add_help('The skill that must be present in order to learn this skill.') !!}
                {!! Form::select('parent_id', $skills, $skill->parent_id, ['class' => 'form-control mb-1']) !!}
            </div>
        </div>
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('Parent Level (Optional)') !!} {!! add_help('Parent Skill level that must be reached before this skill can be learned.') !!}
                {!! Form::number('parent_level', $skill->parent_level ? $skill->parent_level : 1, ['class' => 'form-control', 'min' => 1]) !!}
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($skill->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($skill->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._skill_entry', ['imageUrl' => $skill->imageUrl,
                                                'name' => $skill->displayName,
                                                'description' => $skill->description,
                                                'searchUrl' => $skill->searchUrl,
                                                'is_visible' => $skill->is_visible,
                                                'is_backend' => $skill->is_backend,])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();

            $('.delete-skill-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/skills/delete') }}/{{ $skill->id }}", 'Delete Skill');
            });
        });
    </script>
@endsection
