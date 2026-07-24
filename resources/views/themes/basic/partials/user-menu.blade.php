<div class="drop-down user-menu {{ $menu_class ?? '' }}" data-dropdown data-dropdown-position="top">
    <button type="button" class="drop-down-btn">
        <img src="{{ authUser()->getAvatar() }}" alt="{{ authUser()->getName() }}" class="user-img">
        <span class="user-name">{{ authUser()->getName() }}</span>
        <i class="fa-solid fa-angle-down ms-2" aria-hidden="true"></i>
    </button>
    <div class="drop-down-menu">
        @if (authUser()->isDataCompleted())
            <a href="{{ authUser()->getProfileLink() }}"
                class="drop-down-item {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                @if (request()->routeIs('profile.*')) aria-current="page" @endif>
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                {{ translate('Profile') }}
            </a>
            @if (authUser()->isAuthor())
                <a href="{{ route('workspace.dashboard') }}"
                    class="drop-down-item {{ request()->routeIs('workspace.dashboard') ? 'active' : '' }}"
                    @if (request()->routeIs('workspace.dashboard')) aria-current="page" @endif>
                    <i class="fa-solid fa-table-columns" aria-hidden="true"></i>
                    {{ translate('Dashboard') }}
                </a>
            @endif
            <a href="{{ route('workspace.balance.index') }}"
                class="drop-down-item {{ request()->routeIs('workspace.balance.*') ? 'active' : '' }}"
                @if (request()->routeIs('workspace.balance.*')) aria-current="page" @endif>
                <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                {{ translate('My Balance') }}
            </a>
            <a href="{{ route('favorites') }}"
                class="drop-down-item {{ request()->routeIs('favorites') ? 'active' : '' }}"
                @if (request()->routeIs('favorites')) aria-current="page" @endif>
                <i class="fa-solid fa-heart" aria-hidden="true"></i>
                {{ translate('Favorites') }}
            </a>
            <a href="{{ route('workspace.purchases.index') }}"
                class="drop-down-item {{ request()->routeIs('workspace.purchases.*') ? 'active' : '' }}"
                @if (request()->routeIs('workspace.purchases.*')) aria-current="page" @endif>
                <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                {{ translate('Purchases') }}
            </a>
            <a href="{{ route('workspace.transactions.index') }}"
                class="drop-down-item {{ request()->routeIs('workspace.transactions.*') ? 'active' : '' }}"
                @if (request()->routeIs('workspace.transactions.*')) aria-current="page" @endif>
                <i class="fa-solid fa-receipt" aria-hidden="true"></i>
                {{ translate('Transactions') }}
            </a>
            <a href="{{ route('workspace.settings.index') }}"
                class="drop-down-item {{ request()->routeIs('workspace.settings.*') ? 'active' : '' }}"
                @if (request()->routeIs('workspace.settings.*')) aria-current="page" @endif>
                <i class="fa-solid fa-cog" aria-hidden="true"></i>
                {{ translate('Settings') }}
            </a>
            <div class="drop-down-divider"></div>
            @if (authUser()->isAuthor())
                <a href="{{ route('workspace.items.index') }}"
                    class="drop-down-item {{ request()->routeIs('workspace.items.*') ? 'active' : '' }}"
                    @if (request()->routeIs('workspace.items.*')) aria-current="page" @endif>
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                    {{ translate('My Items') }}
                </a>
                <a href="{{ route('workspace.referrals') }}"
                    class="drop-down-item {{ request()->routeIs('workspace.referrals') ? 'active' : '' }}"
                    @if (request()->routeIs('workspace.referrals')) aria-current="page" @endif>
                    <i class="fa-solid fa-user-group" aria-hidden="true"></i>
                    {{ translate('Referrals') }}
                </a>
                <a href="{{ route('workspace.withdrawals.index') }}"
                    class="drop-down-item {{ request()->routeIs('workspace.withdrawals.*') ? 'active' : '' }}"
                    @if (request()->routeIs('workspace.withdrawals.*')) aria-current="page" @endif>
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    {{ translate('Withdrawals') }}
                </a>
                <div class="drop-down-divider"></div>
            @elseif(@settings('actions')->become_an_author)
                <a href="{{ route('workspace.become-an-author') }}"
                    class="drop-down-item {{ request()->routeIs('workspace.become-an-author') ? 'active' : '' }}"
                    @if (request()->routeIs('workspace.become-an-author')) aria-current="page" @endif>
                    <i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
                    {{ translate('Become an Author') }}
                </a>
                <div class="drop-down-divider"></div>
            @endif
        @endif
        <button type="submit" class="drop-down-item text-danger" form="logout-form">
            <i class="fa-solid fa-power-off" aria-hidden="true"></i>
            {{ translate('Logout') }}
        </button>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST">@csrf</form>
</div>
