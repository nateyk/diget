<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    @include('themes.basic.includes.head')
    <x-ad alias="head_code" />
</head>

<body class="auth-page">
    @php($isRegisterPage = request()->routeIs('register'))
    <section class="auth-shell">
        <div class="auth-shell-inner">
            <div class="auth-card {{ $isRegisterPage ? 'auth-card-register' : 'auth-card-simple' }}">
                @if ($isRegisterPage)
                    <aside class="auth-side">
                        <a href="{{ route('home') }}" class="auth-side-logo" aria-label="{{ ucfirst(config('app.name', 'Diget')) }}">
                            <span class="brand-wordmark">{{ ucfirst(config('app.name', 'Diget')) }}</span>
                        </a>
                        <div>
                            <h1>{{ translate('Build your creator storefront.') }}</h1>
                            <p>{{ translate('Share your profile, sell digital products, and keep your audience connected from one clean page.') }}</p>
                        </div>
                    </aside>
                @endif
                <main class="auth-content">
                    <a href="{{ route('home') }}" class="auth-logo" aria-label="{{ ucfirst(config('app.name', 'Diget')) }}">
                        <span class="brand-wordmark">{{ ucfirst(config('app.name', 'Diget')) }}</span>
                    </a>
                    <div class="section-body">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    </section>
    @include('themes.basic.includes.config')
    @include('themes.basic.includes.scripts')
    @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}')
            @endforeach
        </script>
    @elseif(session('status'))
        <script>
            toastr.success('{{ session('status') }}')
        </script>
    @elseif(session('resent'))
        <script>
            toastr.success('{{ translate('Link has been resend Successfully') }}')
        </script>
    @endif
</body>

</html>
