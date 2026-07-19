<div class="dashboard-counter workspace-stat-card justify-content-start {{ $variant ?? '' }}">
    <div class="dashboard-counter-icon">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="dashboard-counter-info">
        <h2 class="dashboard-counter-title">{{ $label }}</h2>
        <p class="dashboard-counter-number">{{ $value }}</p>
    </div>
</div>
