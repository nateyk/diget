@php
    $icon = $icon ?? 'fa-regular fa-folder-open';
    $title = $title ?? translate('Nothing here yet');
    $description = $description ?? translate('Try again later or explore another section.');
    $actionUrl = $actionUrl ?? null;
    $actionLabel = $actionLabel ?? null;
@endphp

<div class="public-empty-state card-v border">
    <i class="{{ $icon }}" aria-hidden="true"></i>
    <h2 class="h5 mb-2">{{ $title }}</h2>
    <p class="text-muted mb-{{ $actionUrl && $actionLabel ? '3' : '0' }}">{{ $description }}</p>
    @if ($actionUrl && $actionLabel)
        <a href="{{ $actionUrl }}" class="btn btn-outline-primary btn-md">{{ $actionLabel }}</a>
    @endif
</div>
