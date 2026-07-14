<footer class="footer mt-auto">
    <div class="footer-upper">
        <div class="container container-custom">
            @if (isAddonActive('newsletter'))
                <livewire:newsletter.footer />
            @endif
            @if ($themeSettings->footer->footer_about || $footerLinks->count() > 0)
                <div class="row footer-main-row g-4">
                    @if ($themeSettings->footer->footer_about)
                        <div class="col-12 col-lg-4">
                            <div class="footer-brand">
                                <a href="{{ route('home') }}" class="logo h3 mb-3 fw-700">
                                    <img src="{{ asset($themeSettings->footer->footer_logo) }}"
                                        alt="{{ @$settings->general->site_name }}" />
                                </a>
                                <p class="footer-text">{{ $themeSettings->footer->footer_about_content }}</p>
                                @php
                                    $socialLinksSettings = @$settings->social_links;
                                    $hasSocialLinks =
                                        $socialLinksSettings->facebook ||
                                        $socialLinksSettings->x ||
                                        $socialLinksSettings->linkedin ||
                                        $socialLinksSettings->youtube ||
                                        $socialLinksSettings->instagram ||
                                        $socialLinksSettings->pinterest;
                                @endphp
                                @if ($hasSocialLinks)
                                    <div class="socials socials-footer mt-3">
                                        @if ($socialLinksSettings->facebook)
                                            <a href="https://facebook.com/{{ $socialLinksSettings->facebook }}"
                                                target="_blank" class="social-btn social-facebook"
                                                aria-label="{{ translate('Facebook') }}">
                                                <i class="bi bi-facebook"></i>
                                            </a>
                                        @endif
                                        @if ($socialLinksSettings->x)
                                            <a href="https://x.com/{{ $socialLinksSettings->x }}" target="_blank"
                                                class="social-btn social-x" aria-label="{{ translate('X') }}">
                                                <i class="bi bi-twitter-x"></i>
                                            </a>
                                        @endif
                                        @if ($socialLinksSettings->linkedin)
                                            <a href="https://linkedin.com/in/{{ $socialLinksSettings->linkedin }}"
                                                target="_blank" class="social-btn social-linkedin"
                                                aria-label="{{ translate('LinkedIn') }}">
                                                <i class="bi bi-linkedin"></i>
                                            </a>
                                        @endif
                                        @if ($socialLinksSettings->youtube)
                                            <a href="https://youtube.com/{{ '@' . $socialLinksSettings->youtube }}"
                                                target="_blank" class="social-btn social-youtube"
                                                aria-label="{{ translate('YouTube') }}">
                                                <i class="bi bi-youtube"></i>
                                            </a>
                                        @endif
                                        @if ($socialLinksSettings->instagram)
                                            <a href="https://instagram.com/{{ $socialLinksSettings->instagram }}"
                                                target="_blank" class="social-btn social-instagram"
                                                aria-label="{{ translate('Instagram') }}">
                                                <i class="bi bi-instagram"></i>
                                            </a>
                                        @endif
                                        @if ($socialLinksSettings->pinterest)
                                            <a href="https://pinterest.com/{{ $socialLinksSettings->pinterest }}"
                                                target="_blank" class="social-btn social-pinterest"
                                                aria-label="{{ translate('Pinterest') }}">
                                                <i class="bi bi-pinterest"></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                                <div class="footer-counter-wrap mt-3">
                                    <div class="row row-cols-auto align-items-center g-3">
                                        <div class="col">
                                            <div class="footer-counter">
                                                <p class="footer-counter-text">
                                                    {{ number_format($themeSettings->footer->footer_items_sold) }}</p>
                                                <h6 class="footer-counter-title">
                                                    {{ translate('Items Sold') }}
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="footer-counter">
                                                <p class="footer-counter-text">
                                                    {{ getAmount($themeSettings->footer->footer_authors_earnings, 0, '.', ',') }}
                                                </p>
                                                <h6 class="footer-counter-title">
                                                    {{ translate('Authors Earnings') }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-12 {{ $themeSettings->footer->footer_about ? 'col-lg-8' : '' }}">
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
                        {{ @$settings->general->site_name }} - {{ translate('All rights reserved') }}.
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
