<div class="nav-bar">
    <div class="container container-custom">
        <div class="nav-bar-container">
            <a href="{{ route('home') }}" class="logo">
                <span class="brand-wordmark">{{ ucfirst(config('app.name', 'Diget')) }}</span>
            </a>
            <div class="nav-bar-menu ms-auto" id="primary-navigation">
                <div class="overlay"></div>
                <div class="nav-bar-menu-inner">
                    <div class="nav-bar-links">
                        @foreach ($topNavLinks as $topNavLink)
                            @if ($topNavLink->children->count() > 0)
                                <div class="drop-down" data-dropdown data-dropdown-position="top">
                                    <button type="button" class="drop-down-btn">
                                        <span class="me-2">{{ $topNavLink->name }}</span>
                                        <i class="fa fa-angle-down ms-auto"></i>
                                    </button>
                                    <div class="drop-down-menu drop-down-menu-md drop-down-menu-end">
                                        @foreach ($topNavLink->children as $child)
                                            <a href="{{ $child->link }}"
                                                {{ $child->isExternal() ? 'target=_blank' : '' }}
                                                class="drop-down-item">
                                                <span>{{ $child->name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $topNavLink->link }}"
                                    {{ $topNavLink->isExternal() ? 'target=_blank' : '' }} class="link">
                                    <div class="link-title">
                                        <span>{{ $topNavLink->name }}</span>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                        @include('themes.basic.partials.currencies-menu', [
                            'group_classes' => 'drop-down-menu-end',
                        ])
                        @guest
                            <a href="{{ route('login') }}" class="link-btn btn btn-outline-primary d-block d-xl-none">{{ translate('Sign In') }}</a>
                            @if (@$settings->actions->registration)
                                <a href="{{ route('register') }}" class="link-btn btn btn-primary d-block d-xl-none">{{ translate('Sign Up') }}</a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
            <div class="nav-bar-buttons">
                @guest
                    <a href="{{ route('login') }}" class="link-btn btn btn-outline-primary">{{ translate('Sign In') }}</a>
                    @if (@$settings->actions->registration)
                        <a href="{{ route('register') }}" class="link-btn btn btn-primary">{{ translate('Sign Up') }}</a>
                    @endif
                @endguest
            </div>
            <div class="nav-bar-actions">
                @auth
                    @include('themes.basic.partials.user-menu', ['menu_class' => 'ms-3 me-0'])
                @endauth
            </div>
            <button type="button" class="nav-bar-menu-btn ms-3" aria-label="Toggle navigation" aria-controls="primary-navigation" aria-expanded="false">
                <i class="fa-solid fa-bars fa-lg"></i>
            </button>
        </div>
    </div>
</div>
