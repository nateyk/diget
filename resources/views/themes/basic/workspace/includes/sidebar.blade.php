<aside id="workspaceSidebar" class="dashboard-sidebar workspace-sidebar" aria-label="{{ translate('Workspace navigation') }}">
    <button type="button" class="overlay" aria-label="{{ translate('Close navigation') }}"></button>
    <div class="dashboard-sidebar-container">
        <div class="dashboard-sidebar-header">
            <a href="{{ route('home') }}" class="logo logo-sm">
                <img src="{{ asset($themeSettings->general->logo_light) }}" alt="{{ @$settings->general->site_name }}" />
            </a>
        </div>
        <div class="dashboard-sidebar-body">
            <div class="dashboard-sidebar-content">
                <div class="dashboard-sidebar-inner">
                    <a href="{{ route('workspace.balance.index') }}" class="dashboard-balance"
                        aria-label="{{ translate('View balance') }}">
                        <div class="dashboard-balance-info">
                            <h6 class="dashboard-balance-title">{{ translate('Balance') }}</h6>
                            <p class="dashboard-balance-number">
                                {{ getAmount(authUser()->balance) }}</p>
                        </div>
                        <div class="dashboard-balance-icon">
                            <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                        </div>
                    </a>
                    <nav class="dashboard-sidebar-links" aria-label="{{ translate('Workspace navigation') }}">
                    @if (authUser()->isAuthor())
                            <div
                                class="dashboard-sidebar-link {{ request()->routeIs('workspace.dashboard') ? 'current' : '' }}">
                                <a href="{{ route('workspace.dashboard') }}" class="dashboard-sidebar-link-title"
                                    @if (request()->routeIs('workspace.dashboard')) aria-current="page" @endif>
                                    <i class="fa-solid fa-table-columns"></i>
                                    <span>{{ translate('Dashboard') }}</span>
                                </a>
                            </div>
                            <div
                                class="dashboard-sidebar-link {{ request()->routeIs('workspace.items.*') ? 'current' : '' }}">
                                <a href="{{ route('workspace.items.index') }}" class="dashboard-sidebar-link-title"
                                    @if (request()->routeIs('workspace.items.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-box-open"></i>
                                    <span>{{ translate('My Items') }}</span>
                                </a>
                            </div>
                            @if (@$settings->referral->status)
                                <div
                                    class="dashboard-sidebar-link {{ request()->routeIs('workspace.referrals') ? 'current' : '' }}">
                                    <a href="{{ route('workspace.referrals') }}" class="dashboard-sidebar-link-title"
                                        @if (request()->routeIs('workspace.referrals')) aria-current="page" @endif>
                                        <i class="fa-solid fa-user-group"></i>
                                        <span>{{ translate('Referrals') }}</span>
                                    </a>
                                </div>
                            @endif
                            <div
                                class="dashboard-sidebar-link {{ request()->routeIs('workspace.withdrawals.index') ? 'current' : '' }}">
                                <a href="{{ route('workspace.withdrawals.index') }}" class="dashboard-sidebar-link-title"
                                    @if (request()->routeIs('workspace.withdrawals.index')) aria-current="page" @endif>
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>{{ translate('Withdrawals') }}</span>
                                </a>
                            </div>
                            @if (isAddonActive('license_verification_tool'))
                                <div class="dashboard-sidebar-link {{ request()->segment(2) == 'tools' ? 'active animated ' : '' }} dashboard-toggle"
                                    data-toggle>
                                    <button type="button" class="dashboard-sidebar-link-title toggle-title"
                                        aria-controls="workspaceTools" aria-expanded="{{ request()->segment(2) == 'tools' ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                        <span>{{ translate('Tools') }}</span>
                                    </button>
                                    <div id="workspaceTools" class="dashboard-sidebar-link-menu">
                                        <div
                                            class="dashboard-sidebar-link {{ request()->routeIs('workspace.tools.license-verification.*') ? 'current' : '' }}">
                                            <a href="{{ route('workspace.tools.license-verification.index') }}"
                                                class="dashboard-sidebar-link-title"
                                                @if (request()->routeIs('workspace.tools.license-verification.*')) aria-current="page" @endif>
                                                <span>{{ translate('License Verification') }}</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                    @endif
                        <div
                            class="dashboard-sidebar-link {{ request()->routeIs('workspace.purchases.*') ? 'current' : '' }}">
                            <a href="{{ route('workspace.purchases.index') }}" class="dashboard-sidebar-link-title"
                                @if (request()->routeIs('workspace.purchases.*')) aria-current="page" @endif>
                                <i class="fa-solid fa-basket-shopping"></i>
                                <span>{{ translate('Purchases') }}</span>
                            </a>
                        </div>
                        <div
                            class="dashboard-sidebar-link {{ request()->routeIs('workspace.transactions.*') ? 'current' : '' }}">
                            <a href="{{ route('workspace.transactions.index') }}" class="dashboard-sidebar-link-title"
                                @if (request()->routeIs('workspace.transactions.*')) aria-current="page" @endif>
                                <i class="fa-solid fa-receipt"></i>
                                <span>{{ translate('Transactions') }}</span>
                            </a>
                        </div>
                        @if (@$settings->actions->refunds)
                            <div
                                class="dashboard-sidebar-link {{ request()->routeIs('workspace.refunds.*') ? 'current' : '' }}">
                                <a href="{{ route('workspace.refunds.index') }}" class="dashboard-sidebar-link-title"
                                    @if (request()->routeIs('workspace.refunds.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-share"></i>
                                    <span>{{ translate('Refunds') }}</span>
                                    @if ($counters['pending_refunds'])
                                        <span class="counter me-0">{{ $counters['pending_refunds'] }}</span>
                                    @endif
                                </a>
                            </div>
                        @endif
                        @if (@$settings->actions->tickets)
                            <div
                                class="dashboard-sidebar-link {{ request()->routeIs('workspace.tickets.*') ? 'current' : '' }}">
                                <a href="{{ route('workspace.tickets.index') }}" class="dashboard-sidebar-link-title"
                                    @if (request()->routeIs('workspace.tickets.*')) aria-current="page" @endif>
                                    <i class="fa-solid fa-inbox"></i>
                                    <span>{{ translate('Tickets') }}</span>
                                </a>
                            </div>
                        @endif
                        <div
                            class="dashboard-sidebar-link {{ request()->routeIs('workspace.settings.*') ? 'current' : '' }}">
                            <a href="{{ route('workspace.settings.index') }}" class="dashboard-sidebar-link-title"
                                @if (request()->routeIs('workspace.settings.*')) aria-current="page" @endif>
                                <i class="fa-solid fa-cog"></i>
                                <span>{{ translate('Settings') }}</span>
                            </a>
                        </div>
                        <div class="dashboard-sidebar-link dashboard-sidebar-logout">
                            <button type="submit" form="logout-form" class="dashboard-sidebar-link-title">
                                <i class="fa-solid fa-power-off"></i>
                                <span>{{ translate('Logout') }}</span>
                            </button>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</aside>
