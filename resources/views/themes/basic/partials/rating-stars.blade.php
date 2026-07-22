<div class="row row-cols-auto flex-nowrap g-1 ratings {{ $ratings_classes ?? '' }}">
    @for ($i = 0; $i < 5; $i++)
        <div class="col rating {{ $stars > $i ? 'rating-active' : '' }}">
            @if ($stars > $i)
                <i class="fa-solid fa-star"></i>
            @else
                <i class="fa-solid fa-star"></i>
            @endif
        </div>
    @endfor
</div>
