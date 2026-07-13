<div id="searchFiltersSidebar" class="d-none d-xl-block">
    <div class="discover-filters card-v card-bg border">
        @include('themes.basic.partials.search-filter-sections', [
            'idSuffix' => '',
            'priceMinId' => 'priceForm',
            'priceMaxId' => 'priceTo',
            'priceButtonId' => 'searchByPrice',
        ])
    </div>
</div>
<div id="searchFiltersMenu">
    <div class="offcanvas offcanvas-start" tabindex="-1" id="searchFilters" aria-labelledby="searchFiltersLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="searchFiltersLabel">{{ translate('Filters') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="discover-filters card-v card-bg border">
                @include('themes.basic.partials.search-filter-sections', [
                    'idSuffix' => 'Mobile',
                    'priceMinId' => 'priceForm1',
                    'priceMaxId' => 'priceTo1',
                    'priceButtonId' => 'searchByPrice1',
                ])
            </div>
        </div>
    </div>
</div>
