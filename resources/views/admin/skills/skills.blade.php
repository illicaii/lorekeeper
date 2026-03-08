@extends('admin.layout')

@section('admin-title')
    Skills
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Skills' => 'admin/data/skills']) !!}

    <h1>Skills</h1>

    <p>
        Skills are abilities that characters can have. They can be used to represent anything and can be worked in to different mechanics on site.
        They can be grouped into categories, and are a similar to traits (with levels)! Skill Categories are important to the functionality of
        skills, a range of different options can be defined category wide, rather than skill by skill.
    </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/skill-categories') }}"><i class="fas fa-folder"></i> Skill Categories</a>
        <a class="btn btn-primary" href="{{ url('admin/data/skills/create') }}"><i class="fas fa-plus"></i> Create New Skill</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('species_id', $species, Request::get('species_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('skill_category_id', $categories, Request::get('skill_category_id'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($skills))
        <p>No skills found.</p>
    @else
        {!! $skills->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Category</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Species</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Max Level</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">Max Charges</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($skills as $skill)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">
                                    @if (!$skill->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {!! $skill->displayName !!}
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">{{ $skill->category ? $skill->category->name : '---' }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">{{ $skill->species ? $skill->species->name : '---' }}</div>
                            </div>
                            <div class="col-6 col-md-2">
                                @if ($skill->override_default_caps && isset($skill->ovr_level_cap))
                                    <div class="logs-table-cell">*{{ $skill->ovr_level_cap }}</div>
                                @elseif (isset($skill->category->max_level))
                                    <div class="logs-table-cell">{{ $skill->category->max_level }}</div>
                                @else
                                    <div class="logs-table-cell">---</div>
                                @endif
                            </div>
                            <div class="col-6 col-md-2">
                                @if ($skill->override_default_caps && isset($skill->ovr_charge_cap))
                                    <div class="logs-table-cell">*{{ $skill->ovr_charge_cap }}</div>
                                @elseif (isset($skill->category->max_charge))
                                    <div class="logs-table-cell">{{ $skill->category->max_charge }}</div>
                                @else
                                    <div class="logs-table-cell">---</div>
                                @endif
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell">
                                    <a href="{{ url('admin/data/skills/edit/' . $skill->id) }}" class="btn btn-primary py-0 px-1 w-100">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $skills->render() !!}
    @endif

@endsection

@section('scripts')
    @parent
    <script></script>
@endsection
