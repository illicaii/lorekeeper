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
            {!! $name !!} @if (isset($idUrl) && $idUrl)
                <a href="{{ $idUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
        <div class="row">
            @if (isset($skill->category) && $skill->category)
                <div class="col-md">
                    <p><strong>Category:</strong> {!! $skill->category->displayname !!}</p>
                </div>
            @endif
            @if ($skill->species_id)
                <div class="col-md">
                    <p><strong>Species:</strong> {!! $skill->species->displayName !!}</p>
                </div>
            @endif
            @if (isset($skill->parent_id) && $skill->parent)
                <div class="col-md">
                    <p><strong>Parent:</strong> {!! $skill->parent->displayname !!}</p>
                </div>
            @endif
        </div>
        @if (isset($skill->parent_id) && $skill->parent)
            <div class="row">
                <div class="col-md">
                    <p><strong>Parent Skill:</strong> {!! $skill->parent->displayname !!}</p>
                </div>
                <div class="col-md">
                    <p><strong>Unlocks at:</strong> {!! $skill->parent->name !!}(lv.{!! $skill->parent_level !!})</p>
                </div>
            </div>
        @endif
        <div class="world-entry-text">
            {!! $description !!}
        </div>

        @if (isset($skill->override_default_caps) && ((isset($skill->ovr_level_cap) && $skill->ovr_level_cap != 0) || (isset($skill->ovr_charge_cap) && $skill->ovr_charge_cap != 0)))
            <hr>
            <div class="row">
                @if (isset($skill->ovr_level_cap) && $skill->ovr_level_cap != 0)
                    <div class="col-md">
                        <p><strong>Max Level:</strong> {!! $skill->ovr_level_cap !!}</p>
                    </div>
                @endif
                @if (isset($skill->ovr_charge_cap) && $skill->ovr_charge_cap != 0)
                    <div class="col-md">
                        <p><strong>Max Charges:</strong> {!! $skill->ovr_charge_cap !!}</p>
                    </div>
                @endif
            </div>
        @elseif((isset($skill->category->max_level) && $skill->category->max_level != 0) || (isset($skill->category->max_charge) && $skill->category->max_charge != 0))
            <hr>
            <div class="row">
                @if (isset($skill->category->max_level) && $skill->category->max_level != 0)
                    <div class="col-md">
                        <p><strong>Max Level:</strong> {!! $skill->category->max_level !!}</p>
                    </div>
                @endif
                @if (isset($skill->category->max_charge) && $skill->category->max_charge != 0)
                    <div class="col-md">
                        <p><strong>Max Charges:</strong> {!! $skill->category->max_charge !!}</p>
                    </div>
                @endif
            </div>
        @endif


    </div>
</div>
