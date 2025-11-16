@extends('admin.layout')

@section('admin-title')
    Skill Categories
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Skill Categories' => 'admin/data/skill-categories']) !!}

    <h1>Skill Categories</h1>

    <p></p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/skills') }}"><i class="fas fa-undo"></i> Return to Skills</a>
        <a class="btn btn-primary" href="{{ url('admin/data/skill-categories/create') }}"><i class="fas fa-plus"></i> Create New Skill Category</a>
    </div>
    @if (!count($categories))
        <p>No skill categories found.</p>
    @else
        <table class="table table-sm category-table">
            <tbody id="sortable" class="sortable">
                @foreach ($categories as $category)
                    <tr class="sort-skill" data-id="{{ $category->id }}">
                        <td>
                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                            @if (!$category->is_visible)
                                <i class="fas fa-eye-slash mr-1"></i>
                            @endif
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/data/skill-categories/edit/' . $category->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
        <div class="mb-4">
            {!! Form::open(['url' => 'admin/data/skill-categories/sort']) !!}
            {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
            {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
