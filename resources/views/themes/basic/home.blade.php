@extends('themes.basic.layouts.app')
@section('title', @$settings->seo->title)
@section('content')
    <header class="home-landing-hero">
        <div class="container container-custom">
            <div class="home-landing-hero-grid">
                <div class="home-landing-copy" data-aos="fade-up" data-aos-duration="900">
                    <span class="home-landing-eyebrow">{{ translate('Premium creator storefronts') }}</span>
                    <h1 class="home-landing-title">
                        {{ translate('Launch a storefront that makes your digital products look premium') }}
                    </h1>
                    <p class="home-landing-text">
                        {{ translate('Launch your storefront, sell digital products, and grow a polished creator brand from one workspace.') }}
                    </p>

                    <div class="home-landing-actions">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-md">
                            {{ translate('Start selling') }}
                        </a>
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-md">
                            {{ translate('Browse products') }}
                        </a>
                    </div>
                </div>

                <div class="home-landing-visual" data-aos="fade-up" data-aos-delay="100" data-aos-duration="900">
                    <div class="home-landing-image-card">
                        <img src="{{ asset($themeSettings->home_page->header_background) }}"
                            alt="{{ translate('Creator storefront preview') }}">
                    </div>
                    <div class="home-landing-proof">
                        <strong>{{ translate('Storefront-ready') }}</strong>
                        <span>{{ translate('Profiles, products, checkout, and creator tools in one place.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <x-ad alias="home_page_top" @class('container container-custom mt-5') />
    @foreach ($homeSections as $key => $homeSection)
        @include('themes.basic.sections.' . str($homeSection->alias)->replace('_', '-'))
    @endforeach
    <x-ad alias="home_page_bottom" @class('container container-custom my-5') />
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/swiper/swiper-bundle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/libs/aos/aos.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/aos/aos.min.js') }}"></script>
    @endpush
@endsection
