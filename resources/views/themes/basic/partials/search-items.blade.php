@if ($items->count() > 0)
    <div class="items">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
            @foreach ($items as $item)
                <div class="col">
                    @include('themes.basic.partials.item', [
                        'item' => $item,
                        'item_classes' => 'border',
                    ])
                </div>
            @endforeach
        </div>
    </div>
    {{ $items->links() }}
@else
    @php
        $hasActiveFilters = request()->query->count() > 0;
        $clearFiltersUrl = request()->routeIs('categories.category') && isset($category)
            ? route('categories.category', $category->slug)
            : route('items.index');
    @endphp
    @include('themes.basic.partials.public-empty-state', [
        'title' => $hasActiveFilters ? translate('No products match these filters') : translate('No products published yet'),
        'description' => $hasActiveFilters
            ? translate('Clear the active filters or try a different search.')
            : translate('Check back soon or create a storefront to publish your first product.'),
        'actionUrl' => $hasActiveFilters
            ? $clearFiltersUrl
            : (@$settings->actions->registration ? route('register') : route('categories.index')),
        'actionLabel' => $hasActiveFilters ? translate('Clear filters') : translate('Create storefront'),
    ])
@endif
