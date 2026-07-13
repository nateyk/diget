<div class="dashboard-item pb-1 mb-1 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <div class="flag-img me-3">
            <img src="{{ countryFlag($topPurchasingCountry->country) }}"
                alt="{{ countries($topPurchasingCountry->country) }}">
        </div>
        <div>
            <span class="d-block fw-500 mb-1">
                {{ countries($topPurchasingCountry->country) }}
            </span>
        </div>
    </div>
    <div class="ms-3">
        <span class="fw-bold text-success me-1">
            {{ getAmount($topPurchasingCountry->total_earnings) }}
        </span>
    </div>
</div>
