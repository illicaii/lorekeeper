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
        {!! Form::checkbox('is_visible', 1, $category->is_visible ? $category->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the category will not be visible in the category list or available for selection in search. Permissioned staff will still be able to add traits to them, however.') !!}
    </div>
    <div class="form-group">
        {!! Form::checkbox('is_default', 1, $category->is_default ? $category->is_default : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_default', 'Is Default', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned on, the skills in this category will be automatically populated upon character creation') !!}
    </div>
    <div class="form-group">
        {!! Form::label('Max Level (Optional)') !!} {!! add_help('The max level this skills in this category can be leveled to. (aka. The level cap.) Leave blank if you do not wish to have levels') !!}
        {!! Form::number('max_level', $category->max_level ? $category->max_level : 0, ['class' => 'form-control', 'min' => 0]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('Max Charges (Optional)') !!} {!! add_help('The max level of charges skills in this category can have. Leave this blank for categories with no charges') !!}
        {!! Form::number('max_charge', $category->max_charge ? $category->max_charge : 0, ['class' => 'form-control', 'min' => 0]) !!}
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
