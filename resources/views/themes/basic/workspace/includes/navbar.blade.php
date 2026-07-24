<nav class="dashboard-nav">
    <button type="button" class="dashboard-nav-btn dashboard-btn dashboard-toggle-btn"
        aria-controls="workspaceSidebar" aria-expanded="false" aria-label="{{ translate('Toggle navigation') }}">
        <i class="fa-solid fa-bars"></i>
    </button>
    <a href="{{ route('home') }}" class="logo logo-sm logo-toggle ms-3">
        <img src="{{ asset($themeSettings->general->logo_dark) }}" alt="{{ @$settings->general->site_name }}" />
    </a>
    <div class="d-flex align-items-center ms-auto">
        @include('themes.basic.partials.currencies-menu')
        @if (authUser()->isAuthor())
            @php($dashboardStorefrontLink = authUser()->getProfileLink())
            <button type="button" class="btn btn-outline-secondary dashboard-storefront-share ms-3"
                data-bs-toggle="modal" data-bs-target="#dashboardStorefrontShareModal">
                <i class="fa-solid fa-share-nodes me-1"></i>
                <span>{{ translate('Share') }}</span>
            </button>
        @endif
        @if (@settings('actions')->become_an_author && !authUser()->isAuthor())
            <a href="{{ route('workspace.become-an-author') }}" class="btn btn-outline-primary btn-md ms-3">
                <i class="fa-solid fa-pen-nib me-1"></i>
                {{ translate('Become an Author') }}
            </a>
        @endif
        @include('themes.basic.partials.user-menu', ['menu_class' => 'ms-3'])
    </div>
</nav>

@if (authUser()->isAuthor())
    <div class="modal fade" id="dashboardStorefrontShareModal" tabindex="-1"
        aria-labelledby="dashboardStorefrontShareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="modal-header border-0 p-0 mb-3">
                    <h5 class="modal-title" id="dashboardStorefrontShareModalLabel">
                        {{ translate('Share storefront') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ translate('Close') }}"></button>
                </div>
                <div class="modal-body p-0">
                    <p class="text-muted small mb-3">
                        {{ translate('Share your creator storefront with your audience.') }}
                    </p>
                    @include('themes.basic.partials.share-buttons', [
                        'link' => $dashboardStorefrontLink,
                        'socials_classes' => 'dashboard-storefront-share-socials mb-3',
                    ])
                    <label for="dashboardStorefrontShareLink" class="form-label">
                        {{ translate('Storefront link') }}
                    </label>
                    <div class="input-group">
                        <input id="dashboardStorefrontShareLink" type="text" class="form-control form-control-md"
                            value="{{ $dashboardStorefrontLink }}" readonly>
                        <button type="button" class="btn btn-outline-primary btn-md"
                            data-dashboard-storefront-copy="#dashboardStorefrontShareLink">
                            <i class="fa-regular fa-copy me-1"></i>
                            {{ translate('Copy') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            "use strict";

            document.querySelectorAll('[data-dashboard-storefront-copy]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const input = document.querySelector(button.dataset.dashboardStorefrontCopy);

                    if (!input) {
                        return;
                    }

                    input.select();
                    input.setSelectionRange(0, input.value.length);

                    try {
                        await navigator.clipboard.writeText(input.value);
                    } catch (error) {
                        document.execCommand('copy');
                    }

                    if (typeof toastr !== 'undefined' && typeof config !== 'undefined') {
                        toastr.success(config.translates.copied);
                    }
                });
            });
        </script>
    @endpush
@endif
