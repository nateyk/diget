<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/swiper/swiper-bundle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/libs/jquery/fancybox/jquery.fancybox.min.css') }}">
    @endpush
    @include('themes.basic.includes.head')
</head>

<body>
    <header class="item-detail-topbar">
        <div class="container">
            <a href="{{ $item->author->getProfileLink() }}" class="btn btn-outline-secondary btn-padding"
                aria-label="{{ translate('Back to storefront') }}">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <a href="{{ $item->author->getProfileLink() }}" class="item-detail-topbar-link">
                <span>{{ translate('Creator storefront') }}</span>
                <strong>{{ '@' . $item->author->username }}</strong>
            </a>
        </div>
    </header>
    <section class="section forced-start item-detail-page py-4">
        <div class="container">
            <div class="section-header mb-3">
                <div class="item-detail-titlebar">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-lg">
                            <h1
                                class="item-single-title h2 {{ ($settings->item->reviews_status && $item->hasReviews()) || $item->hasSales() ? 'mb-2' : 'mb-0' }}">
                                {{ $item->name }}
                            </h1>
                            <div class="row row-cols-auto align-items-center g-2 small text-muted item-detail-title-meta">
                                @if (($settings->item->reviews_status && $item->hasReviews()) || $item->hasSales() || $item->isRecentlyUpdated())
                                    @if ($settings->item->reviews_status && $item->hasReviews())
                                        <div class="col">
                                            <a href="{{ $item->getReviewsLink() }}">
                                                <div class="row row-cols-auto align-items-center g-2">
                                                    <div class="col">
                                                        @include('themes.basic.partials.rating-stars', [
                                                            'stars' => $item->avg_reviews,
                                                        ])
                                                    </div>
                                                    <div class="col">
                                                        <span class="text-muted">
                                                            {{ translate($item->total_reviews > 1 ? '(:count Reviews)' : '(:count Review)', [
                                                                'count' => number_format($item->total_reviews),
                                                            ]) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                    @if ($item->isPurchasingEnabled() && $item->hasSales())
                                        @if ($settings->item->reviews_status && $item->hasReviews())
                                            <div class="col">
                                                <span>-</span>
                                            </div>
                                        @endif
                                        <div class="col">
                                            <i class="fa-solid fa-cart-shopping me-1"></i>
                                            <span>{{ translate($item->total_sales > 1 ? ':count Sales' : ':count Sale', [
                                                'count' => number_format($item->total_sales),
                                            ]) }}</span>
                                        </div>
                                    @endif
                                    @if ($item->isRecentlyUpdated())
                                        @if (($settings->item->reviews_status && $item->hasReviews()) || ($item->isPurchasingEnabled() && $item->hasSales()))
                                            <div class="col">
                                                <span>-</span>
                                            </div>
                                        @endif
                                        <div class="col text-primary">
                                            <i class="fa-solid fa-circle-check me-1"></i>
                                            <span class="fw-bold">{{ translate('Recently Updated') }}</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="col-12 col-lg-auto ms-lg-auto">
                            <div class="row g-2">
                                <div class="col">
                                    <div class="row g-2">
                                        <div class="col-auto">
                                            <livewire:item.favorite-button :item="$item" />
                                        </div>
                                    </div>
                                </div>
                                @if ($item->isFree())
                                    <div class="col-auto d-inline d-lg-none">
                                        @if ($item->isMainFileExternal())
                                            <a href="{{ route('items.free.download.external', hash_encode($item->id)) }}"
                                                target="_blank" class="btn btn-primary btn-md px-3">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        @else
                                            <form action="{{ route('items.free.download', hash_encode($item->id)) }}"
                                                method="POST">
                                                @csrf
                                                <button class="btn btn-primary btn-md px-3">
                                                    <i class="fa-solid fa-download"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                                @if ($item->isPurchasingEnabled())
                                    <div class="col-auto d-inline d-lg-none">
                                        <form action="{{ route('items.buy-now', [$item->slug, $item->id]) }}"
                                            method="POST">
                                            @csrf
                                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                                            <input type="hidden" name="license_type" value="1">
                                            <button class="btn btn-primary btn-md px-3" @disabled(authUser() && authUser()->id == $item->author_id)>
                                                <i class="fa-solid fa-bag-shopping me-2"></i>
                                                <span>{{ getAmount($item->price->regular, 2, '.', '', true) }}</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-body">
                <div class="row g-3">
                    <div class="col-12 col-lg-8">
                        <div class="card-v border item-detail-card item-detail-preview-card p-3 mb-3">
                            <div class="item-single-preview">
                                @if ($item->isPreviewFileTypeImage())
                                    <div class="item-single-img">
                                        <img src="{{ $item->getPreviewImageLink() }}" alt="{{ $item->name }}" />
                                    </div>
                                    @if ($item->screenshots)
                                        <div class="item-swiper mt-3">
                                            <div class="swiper-actions">
                                                <div id="itemSwiperPrev" class="swiper-button-prev">
                                                    <i class="fa-solid fa-chevron-left fa-rtl"></i>
                                                </div>
                                            </div>
                                            <div class="swiper itemSwiper">
                                                <div class="swiper-wrapper">
                                                    @foreach ($item->getScreenshotLinks() as $screenshot)
                                                        <div class="swiper-slide">
                                                            <a href="{{ $screenshot }}" class="item-slide-img"
                                                                data-fancybox="itemSlide">
                                                                <img src="{{ $screenshot }}"
                                                                    alt="{{ $item->name }}" />
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="swiper-actions">
                                                <div id="itemSwiperNext" class="swiper-button-next">
                                                    <i class="fa-solid fa-chevron-right fa-rtl"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @elseif($item->isPreviewFileTypeVideo())
                                    <div class="item-single-video">
                                        <video class="video-plyr" poster="{{ $item->getPreviewImageLink() }}" controls>
                                            <source src="{{ $item->getPreviewLink() }}">
                                        </video>
                                    </div>
                                @elseif($item->isPreviewFileTypeAudio())
                                    <div class="item-single-audio">
                                        <div class="item-audio-wave">
                                            <div class="item-audio-actions md">
                                                <button class="play-button btn btn-primary btn-md px-2">
                                                    <div class="play-button-icon">
                                                        <i class="fa-solid fa-play"></i>
                                                    </div>
                                                </button>
                                                <button class="pause-button btn btn-primary btn-md px-2 d-none">
                                                    <div class="play-button-icon">
                                                        <i class="fa-solid fa-pause"></i>
                                                    </div>
                                                </button>
                                            </div>
                                            <div class="current-time fs-5">00:00</div>
                                            <div class="waveform" data-url="{{ $item->getPreviewLink() }}"
                                                data-waveheight="100"></div>
                                            <div class="total-duration fs-5">00:00</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="tabs-custom">
                            <div class="card-v border item-detail-card p-3">
                                @php
                                    $itemSettings = $settings->item;
                                @endphp
                                @if (
                                    @$itemSettings->reviews_status ||
                                        @$itemSettings->comments_status ||
                                        @$itemSettings->changelogs_status)
                                    <div class="row row-cols-1 row-cols-sm-2 g-2 mb-3">
                                        <div class="col">
                                            <a href="{{ $item->getLink() }}"
                                                class="btn {{ request()->routeIs('items.view') ? 'btn-primary' : 'btn-outline-secondary' }} btn-md w-100">
                                                <i class="fa-regular fa-circle-question me-1"></i>
                                                <span>{{ translate('Description') }}</span>
                                            </a>
                                        </div>
                                        @if ($settings->item->changelogs_status && $item->hasChangelogs())
                                            <div class="col">
                                                <a href="{{ $item->getChangeLogsLink() }}"
                                                    class="btn {{ request()->routeIs('items.changelogs') ? 'btn-primary' : 'btn-outline-secondary' }} btn-md w-100">
                                                    <i class="fa-solid fa-rotate me-1"></i>
                                                    <span>{{ translate('Changelogs') }}</span>
                                                </a>
                                            </div>
                                        @endif
                                        @if (
                                            ($settings->item->reviews_status && $item->hasReviews()) ||
                                                ($settings->item->reviews_status && authUser() && authUser()->hasPurchasedItem($item->id)))
                                            <div class="col">
                                                <a href="{{ $item->getReviewsLink() }}"
                                                    class="btn {{ request()->routeIs('items.reviews') ? 'btn-primary' : 'btn-outline-secondary' }} btn-md w-100">
                                                    <i class="fa-regular fa-star me-1"></i>
                                                    <span>{{ translate('Reviews (:count)', ['count' => numberFormat($item->total_reviews)]) }}</span>
                                                </a>
                                            </div>
                                        @endif
                                        @if (@$itemSettings->comments_status)
                                            <div class="col">
                                                <livewire:item.comments-counter :item="$item" :isActive="request()->routeIs('items.comments') ? true : false" />
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @yield('content')
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 item-detail-sidebar sticky-lg-top">
                        @if (licenseType(2) && @$settings->premium->status && $item->isPremium())
                            @if (authUser() && authUser()->isSubscribed())
                                <div class="card-v border border-2 border-primary item-detail-card p-3 mb-4">
                                    <div class="card-v-body text-center p-3">
                                        <div class="mb-4">
                                            <div class="mb-3">
                                                <i class="fa-solid fa-download text-primary fa-3x"></i>
                                            </div>
                                            <h3 class="mb-3">{{ translate('Included in your plan') }}</h3>
                                            <p class="mb-0">
                                                {{ translate('Your current plan includes access to this product.') }}
                                            </p>
                                        </div>
                                        @if ($item->isMainFileExternal())
                                            <a href="{{ route('items.premium.download.external', hash_encode($item->id)) }}"
                                                target="_blank"
                                                class="btn btn-primary btn-md w-100 {{ $item->author->id == authUser()->id ? 'disabled' : '' }}">
                                                <i class="fa-solid fa-download me-1"></i>
                                                {{ translate('Download') }}
                                            </a>
                                        @else
                                            <form
                                                action="{{ route('items.premium.download', hash_encode($item->id)) }}"
                                                method="POST">
                                                @csrf
                                                <button
                                                    class="btn btn-primary btn-md w-100 {{ $item->author->id == authUser()->id ? 'disabled' : '' }}">
                                                    <i class="fa-solid fa-download me-1"></i>
                                                    {{ translate('Download') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if ($item->author->id != authUser()->id)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('items.premium.license', encrypt($item->id)) }}"
                                                    target="_blank">
                                                    {{ translate('License certificate') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="card-v border border-2 border-premium item-detail-card p-3 mb-4">
                                    <div class="card-v-body p-4">
                                        <div class="mb-4">
                                            <div class="mb-3">
                                                <i class="fa-solid fa-crown text-premium fa-3x"></i>
                                            </div>
                                            <h3 class="mb-3">{{ translate('Available with membership') }}</h3>
                                            <p class="mb-0">
                                                {{ translate('Choose a membership plan to access this product and other member benefits.') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('premium.index') }}"
                                            class="btn btn-premium btn-md w-100">{{ translate('View membership') }}</a>
                                        @if (@$settings->premium->terms_link)
                                            <div class="text-center mt-3">
                                                <a href="{{ @$settings->premium->terms_link }}" class="text-premium"
                                                    target="_blank">
                                                    {{ translate('Learn more about premium') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if ($item->isFree())
                            <div class="card-v border item-detail-card item-detail-license-card p-0 mb-3">
                                <div class="card-v-header border-bottom py-3 px-3">
                                    <div class="row row-cols-auto align-items-center justify-content-between g-2">
                                        <div class="col">
                                            <h5 class="mb-0">{{ translate('Free download') }}</h5>
                                        </div>
                                        @if (@$settings->links->free_items_policy_link)
                                            <div class="col small">
                                                <a href="{{ @$settings->links->free_items_policy_link }}">
                                                    <span>{{ translate('Free items policy') }}</span>
                                                    <i class="fa-solid fa-chevron-right fa-rtl ms-1 fa-sm"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-v-body p-3">
                                    <p class="text-muted">
                                        {{ translate('Shared free by @:creator. Download it directly to your device.', [
                                            'creator' => strtolower($item->author->username),
                                        ]) }}
                                    </p>
                                    @if ($item->isMainFileExternal())
                                        <a href="{{ route('items.free.download.external', hash_encode($item->id)) }}"
                                            target="_blank" class="btn btn-primary btn-md w-100">
                                            <i class="fa-solid fa-download me-1"></i>
                                            {{ translate('Download') }}
                                        </a>
                                    @else
                                        <form action="{{ route('items.free.download', hash_encode($item->id)) }}"
                                            method="POST">
                                            @csrf
                                            <button class="btn btn-primary btn-md w-100">
                                                <i class="fa-solid fa-download me-1"></i>
                                                {{ translate('Download') }}
                                            </button>
                                        </form>
                                    @endif
                                    @if (authUser())
                                        <div class="text-center mt-3">
                                            <a href="{{ route('items.free.license', encrypt($item->id)) }}"
                                                target="_blank">
                                                {{ translate('License certificate') }}
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-center mt-3">
                                            <a href="{{ route('login') }}">
                                                {{ translate('License certificate') }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if ($item->isPurchasingEnabled())
                            <div class="card-v border item-detail-card item-detail-license-card p-0">
                                <div class="card-v-header border-bottom py-3 px-3">
                                    <div class="row row-cols-auto align-items-center justify-content-between g-2">
                                        <div class="col">
                                            <h5 class="mb-0">{{ translate('Get this product') }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-v-body p-3">
                                    <form action="{{ route('items.buy-now', [$item->slug, $item->id]) }}" class="buy-now-form"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        <input type="hidden" name="license_type" value="1">
                                        <div class="mb-3">
                                            <div class="small text-muted mb-1">{{ translate('Price') }}</div>
                                            <div class="d-flex align-items-center gap-2 h4 fw-semibold mb-1">
                                                @if ($item->isOnDiscount())
                                                    <span class="text-muted text-decoration-line-through fs-6">
                                                        {{ getAmount($item->getRegularPrice(), 2, '.', '', true) }}
                                                    </span>
                                                    <span class="text-primary">
                                                        {{ getAmount($item->price->regular, 2, '.', '', true) }}
                                                    </span>
                                                @else
                                                    <span>
                                                        {{ getAmount($item->getRegularPrice(), 2, '.', '', true) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="small text-muted mb-0">
                                                {{ translate('One-time payment. Access the product from your library after checkout.') }}
                                            </p>
                                        </div>
                                        <button class="btn btn-primary btn-md w-100" @disabled(authUser() && authUser()->id == $item->author_id)>
                                            <i class="fa-solid fa-bag-shopping me-1"></i>
                                            {{ translate('Purchase') }}
                                        </button>
                                    </form>
                                    <div class="list border-top pt-3 mt-3">
                                        <div class="list-item small text-muted">
                                            <i class="fa-solid fa-check text-primary me-1"></i>
                                            {{ translate('Secure checkout') }}
                                        </div>
                                        <div class="list-item small text-muted">
                                            <i class="fa-solid fa-check text-primary me-1"></i>
                                            {{ translate('Available in your purchase library') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="card-v border item-detail-card item-detail-author-card p-3 mt-3">
                            <h5 class="mb-3">{{ translate('Creator') }}</h5>
                            <div class="row align-items-center g-2 mb-3">
                                @php
                                    $author = $item->author;
                                @endphp
                                <div class="col">
                                    <div class="row row-cols-auto align-items-center g-2">
                                        <div class="col">
                                            <a href="{{ $author->getProfileLink() }}"
                                                class="user-avatar user-avatar-lg me-1">
                                                <img src="{{ $author->getAvatar() }}"
                                                    alt="{{ $author->username }}">
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a href="{{ $author->getProfileLink() }}" class="d-block text-dark mb-1">
                                                <h5 class="mb-0">
                                                    {{ $author->getName() }}
                                                    @if ($author->isBanned())
                                                        <span class="badge bg-danger fw-light ms-2">
                                                            <i class="fa-solid fa-ban me-1"></i>
                                                            {{ translate('Banned') }}
                                                        </span>
                                                    @endif
                                                </h5>
                                            </a>
                                            <p class="mb-0 text-muted small">
                                                {{ '@' . $author->username }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @if (!$author->isBanned())
                                    <div class="col-auto">
                                        <livewire:follow-button :user="$author" :iconButton="true" />
                                    </div>
                                @endif
                            </div>
                            <div class="row row-cols-auto g-2">
                                @foreach ($userBadges as $userBadge)
                                    <div class="col">
                                        <div class="item-author-badge">
                                            <img src="{{ $userBadge->badge->getImageLink() }}"
                                                alt="{{ $userBadge->badge->name }}"
                                                title="{{ $userBadge->badge->getFullTitle() }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ $author->getProfileLink() }}"
                                class="btn btn-outline-secondary w-100 mt-3">
                                {{ translate('Visit storefront') }}
                            </a>
                        </div>
                        <div class="card-v border item-detail-card item-detail-meta-card p-3 mt-3">
                            <h5 class="mb-3">{{ translate('Product details') }}</h5>
                            <div class="small">
                                @if ($item->last_update_at)
                                    <div class="d-flex justify-content-between border-bottom item-detail-meta-row">
                                        <p class="mb-0">{{ translate('Last Update') }}:</p>
                                        <p class="mb-0 ms-2">{{ dateFormat($item->last_update_at) }}</p>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between border-bottom item-detail-meta-row">
                                    <p class="mb-0">{{ translate('Published') }}:</p>
                                    <p class="mb-0 ms-2">{{ dateFormat($item->created_at) }}</p>
                                </div>
                                @if ($item->version)
                                    <div class="d-flex justify-content-between border-bottom item-detail-meta-row">
                                        <p class="mb-0">{{ translate('Version') }}:</p>
                                        <p class="mb-0 ms-2">
                                            @if (@$settings->item->changelogs_status && $item->hasChangelogs())
                                                <a href="{{ $item->getChangelogsLink() }}">
                                                    {{ translate('v:version', ['version' => $item->version]) }}
                                                </a>
                                            @else
                                                <span>{{ translate('v:version', ['version' => $item->version]) }}</span>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between border-bottom item-detail-meta-row">
                                    <p class="mb-0">{{ translate('Category') }}:</p>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb justify-content-center m-0">
                                            <li class="breadcrumb-item">
                                                <a
                                                    href="{{ $item->category->getLink() }}">{{ $item->category->name }}</a>
                                            </li>
                                        </ol>
                                    </nav>
                                </div>
                                @if ($item->options && count($item->options) > 0)
                                    @foreach ($item->options as $key => $value)
                                        <div class="d-flex justify-content-between border-bottom item-detail-meta-row">
                                            <p class="mb-0">{{ $key }}:</p>
                                            @if (is_array($value))
                                                <div class="col-7 text-end ms-2">
                                                    @foreach ($value as $option)
                                                        <a
                                                            href="{{ route('items.index', ['search' => strtolower($option)]) }}">
                                                            {{ $option }}
                                                        </a>{{ !$loop->last ? ',' : '' }}
                                                    @endforeach
                                                </div>
                                            @else
                                                <span>{{ $value }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                                <div class="d-flex justify-content-between item-detail-meta-row">
                                    <p class="mb-0">{{ translate('Tags') }}:</p>
                                    <div class="col-7 text-end ms-2">
                                        @foreach ($item->getTags() as $tag)
                                            <a href="{{ route('items.index', ['search' => strtolower($tag)]) }}">
                                                {{ $tag }}</a>{{ !$loop->last ? ',' : '' }}
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-v border item-detail-card item-detail-share-card p-3 mt-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="fs-5">{{ translate('Share') }}</span>
                                @include('themes.basic.partials.share-buttons', [
                                    'link' => $item->getLink(),
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if ($authorItems->count() > 0)
        <div class="section section-start">
            <div class="container">
                <div class="section-header">
                    <div
                        class="row row-cols-auto align-items-center justify-content-center justify-content-lg-between g-3">
                        <div class="col">
                            <div class="section-title mb-0">
                                <h2 class="section-title-text">
                                    {{ translate('More from @:username', ['username' => $author->username]) }}
                                </h2>
                                <div class="section-title-divider"></div>
                            </div>
                        </div>
                        <div class="col d-none d-lg-block">
                            <a href="{{ $author->getPortfolioLink() }}">
                                {{ translate('Visit storefront') }}
                                <i class="fa-solid fa-chevron-right fa-rtl fa-sm ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="section-body">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                        @foreach ($authorItems as $authorItem)
                            <div class="col">
                                @include('themes.basic.partials.item', [
                                    'item' => $authorItem,
                                    'item_classes' => 'border',
                                ])
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-5 d-block d-lg-none">
                        <a href="{{ $author->getPortfolioLink() }}" class="btn btn-primary btn-md btn-icon">
                            {{ translate('Visit storefront') }}
                            <i class="fa-solid fa-arrow-right fa-rtl ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @include('themes.basic.includes.config')
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/jquery/fancybox/jquery.fancybox.min.js') }}"></script>
    @endpush
    @include('themes.basic.includes.scripts')
</body>

</html>
