@php
    $item = $topSellingItem->item;
@endphp

<div class="dashboard-item d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <a href="{{ $item->getLink() }}" class="item-img item-img-sm me-3">
            <img src="{{ $item->getThumbnailLink() }}" alt="{{ $item->name }}">
        </a>
        <div>
            <a href="{{ $item->getLink() }}" class="d-block text-dark fw-500 mb-2">
                {{ $item->name }}
            </a>
            <div class="mt-2 text-muted">
                ({{ translate($topSellingItem->total_sales > 1 ? ':count Sales' : ':count Sale', ['count' => numberFormat($topSellingItem->total_sales)]) }})
            </div>
        </div>
    </div>
</div>
