<div class="card h-100" data-id="{{ $skill->id }}" data-name="Collect from {{ $skill->name }}">
    <div class="row p-2 align-self-center justify-content-center h-100 w-100">
        @if ($skill->has_image)
            <div class = "col-3 justify-content-center d-flex p-0 h-100">
                <img src="{{ $skill->imageUrl }}" class="skill-image-compact">
            </div>
        @endif
        <div class = "col p-2 align-self-center justify-content-center ">
            <div class="row ml-1 mr-2 d-flex justify-content-between">
                <h5 class="mb-1">{!! $skill->displayName !!}</h5>
                @if (isset($ability->xp) && $skill->getMaxLevels() > 0)
                    <p class="m-0">LV. {!! $ability->getlevel() !!} </p>
                @endif
            </div>
            <small class="ml-2 m-0">
                <strong>Resets:</strong>
                @if ($ability->reset_time)
                    {!! pretty_date($ability->reset_time) !!}
                @elseif ($ability->skill->reset_period() != null)
                    --- @if (Auth::user() && $character->user_id == $user->id)
                        (click Collect)
                    @endif
                @else
                    Never
                @endif
            </small>
            <p class="m-0 ml-2 mb-2"><strong>Energy:</strong> {!! $ability->getAvailableCharges() !!}/{!! $ability->getTotalCharges() !!}</p>
            @if (Auth::user() && $character->user_id == $user->id)
                {!! Form::open(['url' => 'character/' . $character->slug . '/skill-abilities/' . $skill->id]) !!}
                {!! Form::hidden('tag', $skill->tag('drop')->tag) !!}
                {!! Form::hidden('skill_id', $skill->id) !!}
                {!! Form::hidden('slug', $character->slug) !!}
                <button class="btn btn-primary btn-sm btn-collect w-100" name="action" value="act" @if ($ability->getAvailableCharges() <= 0) disabled @endif>Collect</button>
                {{ Form::close() }}

                <div class="w-100 text-center">
                    @if ($ability->getAvailableCharges() > 0)
                        <small>Consumes x{!! min($ability->getChargesOnSingleUse($skill->tag('drop')->tag), $ability->getTotalCharges()) !!} Energy!</small>
                    @else
                        <small>{!! $character->displayname !!} is too tired.</small>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
