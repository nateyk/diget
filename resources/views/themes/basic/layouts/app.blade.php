<!DOCTYPE html>
<html lang="{{ getLocale() }}" dir="{{ getDirection() }}">

<head>
    @include('themes.basic.includes.head')
    <x-ad alias="head_code" />
</head>

<body>
    @unless (request()->routeIs('home'))
        @include('themes.basic.includes.navbar')
    @endunless
    @yield('content')
    @include('themes.basic.includes.footer')
    @include('themes.basic.includes.config')
    @include('themes.basic.includes.scripts')
</body>

</html>
