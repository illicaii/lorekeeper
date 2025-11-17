<div class="row world-entry">
    @if ($imageUrl)
        <div class="col-md-3 world-entry-image"><a href="{{ $imageUrl }}" data-lightbox="entry" data-title="{{ $name }}"><img src="{{ $imageUrl }}" class="world-entry-image" /></a></div>
    @endif
    <div class="{{ $imageUrl ? 'col-md-9' : 'col-12' }}">
        <h3>
            @if (!$category->is_visible)
                <i class="fas fa-eye-slash mr-1"></i>
            @endif
            {!! $name !!} @if (isset($searchUrl) && $searchUrl)
                <a href="{{ $searchUrl }}" class="world-entry-search text-muted"><i class="fas fa-search"></i></a>
            @endif
        </h3>
         @if((isset($category->max_level) && ($category->max_level != 0)) ||
                (isset($category->max_charge) && ($category->max_charge != 0)))
            <div class="row">
                @if (isset($category->max_level) && ($category->max_level != 0))
                    <div class="col-md">
                        <p><strong>Max Level:</strong> {!! $category->max_level !!}</p>
                    </div>
                @endif
                @if (isset($category->max_charge) && ($category->max_charge != 0))
                    <div class="col-md">
                        <p><strong>Max Charges:</strong> {!! $category->max_charge !!}</p>
                    </div>
                @endif
            </div>
        @endif
        <div class="world-entry-text">
            {!! $description !!}
        </div>
    </div>
</div>
