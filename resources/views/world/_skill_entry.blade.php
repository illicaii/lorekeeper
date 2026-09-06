<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
    @endif
    <div class="{{ $skill->has_image ? 'col-md-9' : 'col-12' }}">
        <x-admin-edit title="Skill" :object="$skill" />
        <h3>
            @if (!$skill->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            @if ($skill->is_backend)
                <i class="fas fa-key mr-1"></i>
            @endif
            {!! $name !!} @if (isset($idUrl) && $idUrl)
                <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        @if ($skill->is_backend)
            <hr>
            <strong>!! This is a backend skill. Regular users will not be able to see this skill regardless of visibility !!</strong>
            <hr>
        @endif
        <div class="row float-right">
            @foreach ($skill->tags as $tag)
                @if ($tag->is_active)
                    <div class="col">
                        {!! $tag->displayTag !!}
                    </div>
                @endif
            @endforeach
        </div>
        <div class="row">
            @if (isset($skill->category) && $skill->category)
                <div class="col-md-3">
                    <p><strong>Category:</strong> {!! $skill->category->displayname !!}</p>
                </div>
            @endif
            @if ($skill->species_id)
                <div class="col-md-3">
                    <p><strong>Species:</strong> {!! $skill->species->displayName !!}</p>
                </div>
            @endif
        </div>
        @if (isset($skill->parent_id) && $skill->parent)
            <div class="row">
                <div class="col">
                    <p><strong>Unlockable at:</strong> {!! $skill->parent->displayname !!}(lv.{!! $skill->parent_level !!})</p>
                </div>
            </div>
        @endif
        <div class="world-entry-text">
            {!! $description !!}
        </div>

        <hr>
        <div class="row">
            @if ($skill->getMaxLevels() && $skill->getMaxLevels() != 0)
                <div class="col-md">
                    <p><strong>Max Level:</strong> {!! $skill->getMaxLevels() !!}</p>
                </div>
            @endif
            @if ($skill->getMaxCharges() != 0)
                <div class="col-md">
                    <p><strong>Max Energy:</strong> {!! $skill->getMaxCharges() !!} <small>
                            @if ($skill->reset_period() != null)
                                (per {!! $skill->reset_period() !!})
                            @else
                                (lifetime)
                            @endif
                        </small>
                    </p>
                </div>
            @endif
        </div>


    </div>
</div>
