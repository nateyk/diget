@extends('themes.basic.layouts.app')
@section('title', translate('Creator storefronts'))
@section('content')
    <header class="home-landing-hero">
        <div class="container container-custom">
            <div class="home-landing-hero-grid">
                <div class="home-landing-copy">
                    <span class="home-landing-eyebrow">{{ translate('Premium creator storefronts') }}</span>
                    <h1 class="home-landing-title">
                        {{ translate('Launch a storefront that makes your digital products look premium') }}
                    </h1>
                    <p class="home-landing-text">
                        {{ translate('Launch your storefront, sell digital products, and grow a polished creator brand from one workspace.') }}
                    </p>

                    <div class="home-landing-actions">
                        @if (@$settings->actions->registration)
                            <a href="{{ route('register') }}" class="btn btn-primary btn-md">
                                {{ translate('Start selling') }}
                            </a>
                        @endif
                        <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-md">
                            {{ translate('Browse products') }}
                        </a>
                    </div>
                </div>

                <div class="home-landing-visual">
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
    <section class="section border-bottom">
        <div class="container container-custom">
            <div class="section-header">
                <div class="col-lg-7 mx-auto text-center">
                    <h2 class="section-title-text mb-2">
                        {{ translate('Everything creators need to sell from one link') }}
                    </h2>
                    <p class="section-text mb-0">
                        {{ translate('Build your storefront, publish digital products, and share a single home for your work.') }}
                    </p>
                </div>
            </div>
            <div class="section-body">
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <div class="col">
                        <div class="border-top pt-3">
                            <i class="fa-regular fa-id-card text-primary mb-3"></i>
                            <h3 class="h5 mb-2">{{ translate('Your creator storefront') }}</h3>
                            <p class="text-muted mb-0">
                                {{ translate('A focused profile for your brand, products, links, and story.') }}
                            </p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="border-top pt-3">
                            <i class="fa-solid fa-box-open text-primary mb-3"></i>
                            <h3 class="h5 mb-2">{{ translate('Digital products') }}</h3>
                            <p class="text-muted mb-0">
                                {{ translate('Publish files and product details from one simple creator workspace.') }}
                            </p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="border-top pt-3">
                            <i class="fa-solid fa-link text-primary mb-3"></i>
                            <h3 class="h5 mb-2">{{ translate('One link to share') }}</h3>
                            <p class="text-muted mb-0">
                                {{ translate('Send your audience directly to a storefront that belongs to you.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @php
        $storefrontSections = ['featured_author', 'testimonials', 'faqs', 'blog_articles'];
    @endphp
    @foreach ($homeSections as $key => $homeSection)
        @continue(!in_array($homeSection->alias, $storefrontSections, true))
        @include('themes.basic.sections.' . str($homeSection->alias)->replace('_', '-'))
    @endforeach
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('vendor/libs/swiper/swiper-bundle.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/libs/aos/aos.min.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('vendor/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/libs/aos/aos.min.js') }}"></script>
    @endpush
@endsection
