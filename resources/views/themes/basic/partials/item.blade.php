@php
    $showCreator = $show_creator ?? true;
    $isDiscounted = $item->isOnDiscount();
@endphp
<article class="item product-card {{ $item_classes ?? '' }}">
    <div class="item-header">
        @if ($isDiscounted)
            <span class="item-badge" aria-label="{{ translate('On sale') }}">{{ translate('Sale') }}</span>
        @endif
        @if ($item->isPreviewFileTypeImage())
            <a href="{{ $item->getLink() }}" aria-label="{{ translate('View :name', ['name' => $item->name]) }}">
                <img class="item-img" src="{{ $item->getPreviewImageLink() }}" alt="{{ $item->name }}" />
            </a>
        @elseif($item->isPreviewFileTypeVideo())
            <a href="{{ $item->getLink() }}" class="opacity-100">
                <div class="item-video">
                    <video class="plyr" poster="{{ $item->getPreviewImageLink() }}" muted>
                        <source src="{{ $item->getPreviewLink() }}">
                    </video>
                    <div class="item-video-actions d-flex align-items-center justify-content-between gap-1">
                        <div class="item-video-volume item-video-action">
                            <i class="fa-solid fa-volume-high unmuted"></i>
                            <i class="fa-solid fa-volume-xmark muted"></i>
                        </div>
                        <div class="item-video-full item-video-action">
                            <i class="fa-solid fa-expand"></i>
                        </div>
                    </div>
                    <div class="item-video-progress"><span></span></div>
                </div>
            </a>
        @elseif($item->isPreviewFileTypeAudio())
            <div class="item-audio">
                <a href="{{ $item->getLink() }}" class="item-audio-link opacity-100"></a>
                <div class="item-audio-wave">
                    <div class="item-audio-actions">
                        <button class="play-button btn btn-primary btn-sm px-2" aria-label="{{ translate('Play preview') }}">
                            <span class="play-button-icon"><i class="fa-solid fa-play"></i></span>
                        </button>
                        <button class="pause-button btn btn-primary btn-sm px-2 d-none" aria-label="{{ translate('Pause preview') }}">
                            <span class="play-button-icon"><i class="fa-solid fa-pause"></i></span>
                        </button>
                    </div>
                    <div class="waveform" data-url="{{ $item->getPreviewLink() }}" data-waveheight="50"></div>
                    <div class="total-duration">00:00</div>
                </div>
            </div>
        @endif
    </div>
    <div class="item-body">
        <a class="item-title" href="{{ $item->getLink() }}">{{ $item->name }}</a>

        @if ($showCreator)
            <p class="item-text">
                {{ translate('By') }}
                <a href="{{ $item->author->getProfileLink() }}">{{ '@' . $item->author->username }}</a>
            </p>
        @endif

        @if ($settings->item->reviews_status && $item->hasReviews())
            <div class="item-ratings">
                <div class="row row-cols-auto align-items-center g-2">
                    @include('themes.basic.partials.rating-stars', ['stars' => $item->avg_reviews])
                    <div class="col">
                        <span class="text-muted small">({{ numberFormat($item->total_reviews) }})</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="item-purchase">
            <div class="d-flex align-items-end justify-content-between gap-3">
                <div>
                    <div class="item-price">
                        @if ($item->isFree())
                            <span class="item-price-number">{{ translate('Free') }}</span>
                        @elseif ($isDiscounted)
                            <span class="item-price-through">{{ getAmount($item->getRegularPrice(), 2, '.', '', true) }}</span>
                            <span class="item-price-number">{{ getAmount($item->price->regular, 2, '.', '', true) }}</span>
                        @else
                            <span class="item-price-number">{{ getAmount($item->getRegularPrice(), 2, '.', '', true) }}</span>
                        @endif
                    </div>
                    @if ($item->isPurchasingEnabled() && $item->hasSales())
                        <div class="item-sales">
                            {{ translate($item->total_sales > 1 ? ':count sales' : ':count sale', [
                                'count' => numberFormat($item->total_sales),
                            ]) }}
                        </div>
                    @elseif(@$settings->item->free_item_total_downloads && $item->free_downloads > 0)
                        <div class="item-sales">
                            {{ translate($item->free_downloads > 1 ? ':count downloads' : ':count download', [
                                'count' => numberFormat($item->free_downloads),
                            ]) }}
                        </div>
                    @endif
                </div>
                <div class="ms-auto">
                    <a href="{{ $item->getLink() }}" class="small fw-semibold text-dark text-nowrap">
                    {{ translate('View product') }}
                    <i class="fa-solid fa-arrow-right fa-rtl ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</article>
