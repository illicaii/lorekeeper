@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Abilities
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Abilities' => $character->url . '/skill_abilities']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Abilities' => $character->url . '/skill_abilities',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    <h3>
        Abilities
    </h3>

    <div class="row mb-4">
        @if (count($abilities) > 0)
            @foreach ($abilities as $ability)
                @if ($ability->skill->hasActiveTag())
                <div class = "col-12 col-md-6 col-xl-4 pb-2 px-2">
                    @include('skill_abilities._drop', ['skill' => $ability->skill, 'ability' => $ability, 'character' => $character])
                </div>
                @endif
            @endforeach
        @else
            Character has no abilities
        @endif
    </div>

    <!-- if (count($item->tags))
            <div>
                foreach ($item->tags as $tag)
                    if ($tag->is_active)
                         $tag->displayTag
                    endif
                endforeach
            </div>
        endif -->

    <h3>Latest Activity</h3>
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-4">
                    <div class="logs-table-cell">Skill</div>
                </div>
                <div class="col-6 col-md-6">
                    <div class="logs-table-cell">Log</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Date</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            <!-- foreach ($logs as $log)
                <div class="logs-table-row">
                    include('character._skill_abilities_log_row', ['log' => $log, 'owner' => $character])
                </div>
            endforeach -->
        </div>
    </div>
    <div class="text-right">
        <a href="{{ url($character->url . '/skill-abilities-logs') }}">View all...</a>
    </div>
@endsection
