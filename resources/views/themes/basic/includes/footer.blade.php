<footer class="footer mt-auto">
    <div class="footer-upper">
        <div class="container container-custom">
            @if (isAddonActive('newsletter'))
                <livewire:newsletter.footer />
            @endif
            @php
                $footerLinks = $footerLinks->map(function ($footerLink) {
                    $validChildren = $footerLink->children->filter(function ($child) {
                        return filled($child->link) && str($child->link)->trim('/')->lower()->value() !== 'page-example';
                    })->values();

                    $footerLink->setRelation('children', $validChildren);

                    return $footerLink;
                })->filter(function ($footerLink) {
                    return $footerLink->children->isNotEmpty() ||
                        (filled($footerLink->link) && str($footerLink->link)->trim('/')->lower()->value() !== 'page-example');
                });
                $footerAbout = trim((string) $themeSettings->footer->footer_about_content);
                $showFooterAbout = $themeSettings->footer->footer_about &&
                    !str($footerAbout)->lower()->contains('lorem ipsum');
            @endphp
            @if ($showFooterAbout || $footerLinks->count() > 0)
                <div class="row footer-main-row g-4">
                    @if ($showFooterAbout)
                        <div class="col-12 col-lg-4">
                            <div class="footer-brand">
                                <a href="{{ route('home') }}" class="logo h3 mb-3 fw-700">
                                    <span class="brand-wordmark">{{ ucfirst(config('app.name', 'Diget')) }}</span>
                                </a>
                                <p class="footer-text">{{ $footerAbout }}</p>
                                @php
                                    $socialLinksSettings = $settings->social_links ?? (object) [];
                                    $socialFacebook = data_get($socialLinksSettings, 'facebook');
                                    $socialX = data_get($socialLinksSettings, 'x');
                                    $socialLinkedin = data_get($socialLinksSettings, 'linkedin');
                                    $socialYoutube = data_get($socialLinksSettings, 'youtube');
                                    $socialInstagram = data_get($socialLinksSettings, 'instagram');
                                    $socialPinterest = data_get($socialLinksSettings, 'pinterest');
                                    $hasSocialLinks = $socialFacebook || $socialX || $socialLinkedin || $socialYoutube || $socialInstagram || $socialPinterest;
                                @endphp
                                @if ($hasSocialLinks)
                                    <div class="socials socials-footer mt-3">
                                        @if ($socialFacebook)
                                            <a href="https://facebook.com/{{ $socialFacebook }}"
                                                target="_blank" class="social-btn social-facebook"
                                                aria-label="{{ translate('Facebook') }}">
                                                <i class="bi bi-facebook"></i>
                                            </a>
                                        @endif
                                        @if ($socialX)
                                            <a href="https://x.com/{{ $socialX }}" target="_blank"
                                                class="social-btn social-x" aria-label="{{ translate('X') }}">
                                                <i class="bi bi-twitter-x"></i>
                                            </a>
                                        @endif
                                        @if ($socialLinkedin)
                                            <a href="https://linkedin.com/in/{{ $socialLinkedin }}"
                                                target="_blank" class="social-btn social-linkedin"
                                                aria-label="{{ translate('LinkedIn') }}">
                                                <i class="bi bi-linkedin"></i>
                                            </a>
                                        @endif
                                        @if ($socialYoutube)
                                            <a href="https://youtube.com/{{ '@' . $socialYoutube }}"
                                                target="_blank" class="social-btn social-youtube"
                                                aria-label="{{ translate('YouTube') }}">
                                                <i class="bi bi-youtube"></i>
                                            </a>
                                        @endif
                                        @if ($socialInstagram)
                                            <a href="https://instagram.com/{{ $socialInstagram }}"
                                                target="_blank" class="social-btn social-instagram"
                                                aria-label="{{ translate('Instagram') }}">
                                                <i class="bi bi-instagram"></i>
                                            </a>
                                        @endif
                                        @if ($socialPinterest)
                                            <a href="https://pinterest.com/{{ $socialPinterest }}"
                                                target="_blank" class="social-btn social-pinterest"
                                                aria-label="{{ translate('Pinterest') }}">
                                                <i class="bi bi-pinterest"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <div class="col-12 {{ $showFooterAbout ? 'col-lg-8' : '' }}">
                        <div class="row footer-links-grid row-cols-2 row-cols-sm-3 g-4">
                            @foreach ($footerLinks as $footerLink)
                                @if ($footerLink->children->count() > 0)
                                    <div class="col">
                                        <div class="footer-link-group">
                                            <div class="footer-title">
                                                <span class="h5">{{ $footerLink->name }}</span>
                                                <div class="footer-title-divider"></div>
                                            </div>
                                            <ul class="footer-links list-unstyled mb-0">
                                                @foreach ($footerLink->children as $child)
                                                    <li class="footer-link">
                                                        <a href="{{ $child->link }}"
                                                            {{ $child->isExternal() ? 'target=_blank' : '' }}
                                                            class="footer-text">{{ $child->name }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @else
                                    <div class="col">
                                        <a href="{{ $footerLink->link }}"
                                            {{ $footerLink->isExternal() ? 'target=_blank' : '' }}
                                            class="footer-link">{{ $footerLink->name }}</a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="footer-lower">
        <div class="container container-custom">
            <div class="row footer-lower-row row-cols-1 row-cols-sm-auto align-items-center justify-content-between g-3">
                <div class="col">
                    <p class="footer-copyright text-center small mb-0">
                        &copy; <span data-year></span>
                        {{ ucfirst(config('app.name', 'Diget')) }} - {{ translate('All rights reserved') }}.
                    </p>
                </div>
                @if ($themeSettings->footer->footer_payment_methods)
                    <div class="col d-flex justify-content-center">
                        <div class="footer-payment">
                            <img src="{{ asset($themeSettings->footer->footer_payment_methods_logo) }}"
                                alt="{{ @$settings->general->site_name }}" />
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>
