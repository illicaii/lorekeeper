@extends('admin.layout')

@section('admin-title')
    {{ $category->id ? 'Edit' : 'Create' }} Skill Categories
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Skill Categories' => 'admin/data/skill-categories',
        ($category->id ? 'Edit' : 'Create') . ' Category' => $category->id ? 'admin/data/skill-categories/edit/' . $category->id : 'admin/data/skill-categories/create',
    ]) !!}

    <h1>{{ $category->id ? 'Edit' : 'Create' }} Category
        @if ($category->id)
            <a href="#" class="btn btn-danger float-right delete-category-button">Delete Category</a>
        @endif
    </h1>

    {!! Form::open(['url' => $category->id ? 'admin/data/skill-categories/edit/' . $category->id : 'admin/data/skill-categories/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $category->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($category->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $category->description, ['class' => 'form-control wysiwyg']) !!}
    </div>
    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $category->is_visible ? 1 : $category->is_visible, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the category will not be visible in the category list or available for selection in search. Permissioned staff will still be able to add traits to them, however.') !!}
    </div>
    <div class="form-group">
        {!! Form::checkbox('is_default', 1, $category->is_default ? $category->is_default : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_default', 'Is Default', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the skills in this category will be automatically populated upon character creation.') !!}
    </div>
    <div class="form-group">
        <a data-toggle="collapse" href="#collapseLevel" role="button" aria-expanded="false" aria-controls="collapseLevel">
            {!! Form::checkbox('is_levelable', 1, $category->is_levelable ? $category->is_levelable : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('is_levelable', 'Has Levels', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the skills in this category will have levels') !!}
        </a>
    </div>
    <div class="collapse {{ $category->is_levelable ? 'show' : '' }} card p-3 mb-3" id="collapseLevel">
        <div class="form-group">
            <h5>Level Curve</h5>
            <p>The level curve is the mathematical formula that dictates the rate at which skills level up. This extension uses a simple exponential level curve: <br> Current Level = floor((Current xp) / (Base XP * Multiplier)) + 1.0 </p>
            {!! Form::label('Base XP') !!} {!! add_help('The XP needed to go from level 1 to level 2.') !!}
            {!! Form::number('level_base', $category->level_base ? $category->level_base : 100, ['class' => 'form-control', 'min' => 10]) !!}
            {!! Form::label('Multiplier') !!} {!! add_help('The multiplier used when determining how much XP is needed to get to the next level.') !!}
            {!! Form::text('level_multiplier', $category->level_multiplier ? $category->level_multiplier : 1.25, ['class' => 'form-control', 'min' => 1.0]) !!}
        </div>
        <div class="form-group">
            <h5>Level Limits</h5>
            {!! Form::label('Max Level') !!} {!! add_help('The max level this skills in this category can be leveled to. (aka. The level cap.) Setting to zero effectively caps the skill at lv 0 (No levels)') !!}
            {!! Form::number('max_level', $category->max_level ? $category->max_level : 0, ['class' => 'form-control', 'min' => 0]) !!}
        </div>
        <h5>Randomized Starting Level (Optional)</h5>
        <div class="form-group">
            {!! Form::checkbox('randomize_firstLevel', 1, $category->randomize_firstLevel ? $category->randomize_firstLevel : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            {!! Form::label('randomize_firstLevel', 'Random Starting Level', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the skill will be assigned a random level between the MIN and MAX entered below upon character creation') !!}
        </div>
        <div class="form-group">
            {!! Form::label('Min Starting Level (Must be less than max level)') !!} {!! add_help('The min level skills in this category can roll on creation') !!}
            {!! Form::number('random_level_min', $category->random_level_min ? $category->random_level_min : 0, ['class' => 'form-control', 'min' => 0]) !!}
            {!! Form::label('Max Starting Level (Must be greater than min level)') !!} {!! add_help('The max level skills in this category can roll on creation') !!}
            {!! Form::number('random_level_max', $category->random_level_max ? $category->random_level_max : 0, ['class' => 'form-control', 'min' => 0]) !!}
        </div>
    </div>
    <div class="form-group">
        <h5>Level Charges (Optional)</h5>
        {!! Form::label('Max Charges') !!} {!! add_help('The max level of charges skills in this category can have. Leave this blank for categories with no charges') !!}
        {!! Form::number('max_charge', $category->max_charge ? $category->max_charge : 0, ['class' => 'form-control mb-2', 'min' => 0]) !!}

        {!! Form::label('Reset Frequency') !!} {!! add_help(
            'Used in conjunction with reset period to determine when the skill charges are reset. For example, when frequency is set to 4 and period is set to Day then the skill charges in this category will reset ever 4 days. Leave this blank for categories with no charges',
        ) !!}
        {!! Form::number('reset_frequency', $category->reset_frequency ? $category->reset_frequency : 1, ['class' => 'form-control mb-2', 'min' => 0]) !!}
        {!! Form::label('Reset Period') !!} {!! add_help("The duration of time before the charges of skills in this category will be reset. Leave this as 'Never' for categories with no charges") !!}
        {!! Form::select('reset_period', [null => 'Never', 'hour' => 'Hour', 'day' => 'Day', 'month' => 'Month', 'year' => 'Year'], $category->reset_period ? $category->reset_period : null, ['class' => 'form-control']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($category->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._skill_category_entry', ['imageUrl' => $category->categoryImageUrl, 'name' => $category->displayName, 'description' => $category->description, 'category' => $category])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-category-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/skill-categories/delete') }}/{{ $category->id }}", 'Delete Category');
            });
        });
    </script>
@endsection
