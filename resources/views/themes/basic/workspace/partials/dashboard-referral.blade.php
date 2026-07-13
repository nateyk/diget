<div class="dashboard-item pb-2 mb-2 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <span class="d-block fw-500">
            {{ shorterText($referral->referrer, 60) }}
        </span>
    </div>
    <div class="ms-3">
        <span class="text-muted me-1">
            {{ numberFormat($referral->total_views) }}
        </span>
    </div>
</div>
