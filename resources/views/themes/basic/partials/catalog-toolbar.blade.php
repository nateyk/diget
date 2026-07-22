@php
    $catalogTitle = $catalogTitle ?? translate('All products');
    $catalogDescription = $catalogDescription ?? null;
    $catalogItems = $catalogItems ?? null;
    $clearFiltersUrl = $clearFiltersUrl ?? route('items.index');
    $activeFiltersCount = count(array_filter(request()->query(), static fn ($value) => filled($value)));
@endphp

<div class="catalog-toolbar row g-2 align-items-end mb-3">
    <div class="col-12 col-md">
        <h1 class="h4 mb-1">{{ $catalogTitle }}</h1>
        @if ($catalogDescription)
            <p class="small text-muted mb-0">{{ $catalogDescription }}</p>
        @endif
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary btn-md d-inline-flex d-xl-none" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#searchFilters" aria-controls="searchFilters">
            <i class="fa-solid fa-filter me-1" aria-hidden="true"></i>
            <span>{{ translate('Filters') }}</span>
            @if ($activeFiltersCount)
                <span class="badge rounded-pill text-bg-secondary ms-1">{{ $activeFiltersCount }}</span>
            @endif
        </button>
    </div>
    @if ($activeFiltersCount)
        <div class="col-auto">
            <a href="{{ $clearFiltersUrl }}" class="btn btn-link btn-md px-0">{{ translate('Clear filters') }}</a>
        </div>
    @endif
    @if ($catalogItems?->count())
        <div class="col-auto d-none d-md-inline">
            @include('themes.basic.partials.grid-buttons')
        </div>
    @endif
</div>
