<header class="workspace-page-header">
    <div class="workspace-page-header-copy">
        <h1 class="workspace-page-title">@yield('title')</h1>
        <div class="workspace-page-breadcrumbs">
            @yield('breadcrumbs')
        </div>
    </div>

    <div class="workspace-page-header-actions">
        @if (request()->routeIs('workspace.dashboard'))
            @include('themes.basic.workspace.partials.period-select', ['date' => authUser()->created_at])
        @endif

        @hasSection('back')
            <a href="@yield('back')" class="btn btn-outline-secondary btn-md">
                <i class="fa-solid fa-arrow-left fa-rtl me-1"></i>
                {{ translate('Back') }}
            </a>
        @endif

        @hasSection('create')
            <a href="@yield('create')" class="btn btn-primary btn-md">
                <i class="fa fa-plus me-1"></i>
                @yield('create_label', translate('Create'))
            </a>
        @endif

        @if (request()->routeIs('workspace.items.index'))
            @if ($items->count() > 0 || request()->input('search') || request()->input('category'))
                <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#addItemModel">
                    <i class="fa-regular fa-plus me-1"></i>
                    {{ translate('New Item') }}
                </button>
            @endif
        @endif

        @if (request()->routeIs('workspace.transactions.show') && $trx->isPaid())
            <a href="{{ route('workspace.transactions.invoice', $trx->id) }}" target="_blank" class="btn btn-primary btn-md">
                <i class="fa-regular fa-file-lines me-1"></i>
                {{ translate('Invoice') }}
            </a>
        @endif

        @if (request()->routeIs('workspace.withdrawals.index'))
            <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#withdrawModel">
                <i class="fa-regular fa-paper-plane me-1"></i>
                {{ translate('Withdraw') }}
            </button>
        @endif

        @if (@$settings->deposit->status && request()->routeIs('workspace.balance.index'))
            <button type="button" class="btn btn-primary btn-md" data-bs-toggle="modal" data-bs-target="#depositModel">
                <i class="fa-solid fa-circle-dollar-to-slot me-1"></i>
                {{ translate('Deposit') }}
            </button>
        @endif
    </div>
</header>
