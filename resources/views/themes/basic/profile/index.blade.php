@extends('themes.basic.profile.layout')
@php
    $cardDescription = trim($user->profile_card_description ?? '');
    $profileHeading = $user->profile_heading ?: translate('Digital creator');
    $profileDescription = trim(strip_tags($user->profile_description ?? ''));
    $profileSeoDescription = $cardDescription ?: $profileDescription ?: translate(':name creator storefront on :website_name', [
        'name' => $user->getName(),
        'website_name' => @$settings->general->site_name,
    ]);
    $socialLinks = $user->profile_social_links;
    $publishedItemsCount = $items->total();
    $showSales = $user->total_sales > 0;
    $showReviews = $user->total_reviews > 0;
    $storefrontLink = $user->getProfileLink();
    $personSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Person',
        'name' => $user->getName(),
        'alternateName' => '@' . $user->username,
        'url' => $storefrontLink,
        'image' => $user->getAvatar(),
        'description' => shorterText($profileSeoDescription, 160),
    ];
@endphp
@section('title', $user->getName() . ' (@' . $user->username . ')')
@section('description', shorterText($profileSeoDescription, 160))
@section('og_image', $user->getProfileCover())
@section('canonical', $storefrontLink)
@push('schema')
    <script type="application/ld+json">{!! json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush
@section('content')
    <div class="creator-storefront">
        <aside class="card-v border item-detail-card item-detail-author-card creator-storefront-card" data-storefront-mobile-panel="profile">
            <div class="creator-storefront-cover-banner">
                <img src="{{ $user->getProfileCover() }}" alt="{{ $user->getName() }} cover">
            </div>
            <div class="creator-storefront-card-body">
                <div class="creator-storefront-profile-row">
                    <a href="{{ $user->getProfileLink() }}" class="creator-storefront-avatar">
                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->username }}">
                    </a>
                    <div class="creator-storefront-identity">
                        <div class="creator-storefront-name fw-semibold">
                            {{ $user->getName() }}
                            @if ($user->isAuthor())
                                <i class="fa-solid fa-circle-check creator-storefront-verified"></i>
                            @endif
                        </div>
                        <div class="creator-storefront-heading text-muted small">{{ $profileHeading }}</div>
                        <div class="creator-storefront-username text-muted small">{{ '@' . $user->username }}</div>
                    </div>
                </div>

                <div class="creator-storefront-actions">
                    <livewire:follow-button :user="$user" />
                    @if ($user->profile_contact_email)
                        <button type="button" class="btn btn-outline-secondary btn-padding"
                            data-bs-toggle="modal" data-bs-target="#storefrontContactModal"
                            aria-label="{{ translate('Message') }}">
                            <i class="fa-regular fa-message"></i>
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-padding"
                        data-bs-toggle="modal" data-bs-target="#storefrontShareModal"
                        aria-label="{{ translate('Share') }}">
                        <i class="fa-solid fa-share-nodes"></i>
                    </button>
                </div>

                @if ($cardDescription)
                    <p class="creator-storefront-bio">{{ $cardDescription }}</p>
                @endif

                @include('themes.basic.partials.profile-social-links', [
                    'socialLinks' => $socialLinks,
                    'class' => 'creator-storefront-socials socials',
                ])

                @if ($publishedItemsCount || $showSales || $showReviews)
                    <div class="creator-storefront-stats">
                        @if ($publishedItemsCount)
                            <div>
                                <strong>{{ numberFormat($publishedItemsCount) }}</strong>
                                <span>{{ translate('Products') }}</span>
                            </div>
                        @endif
                        @if ($showSales)
                            <div>
                                <strong>{{ numberFormat($user->total_sales) }}</strong>
                                <span>{{ translate('Sales') }}</span>
                            </div>
                        @endif
                        @if ($showReviews)
                            <div>
                                <strong>{{ number_format($user->avg_reviews, 1) }}</strong>
                                <span>{{ translate($user->total_reviews == 1 ? '1 review' : ':count reviews', [
                                    'count' => numberFormat($user->total_reviews),
                                ]) }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </aside>

        <main class="creator-storefront-main">
            <div class="creator-storefront-main-header">
                <div class="creator-storefront-tabs" role="tablist" aria-label="{{ translate('Storefront content') }}">
                    <button type="button" class="active" role="tab" aria-selected="true"
                        aria-controls="storefrontPortfolio" data-storefront-tab="portfolio">
                        {{ translate('Products') }}
                        <span>{{ numberFormat($publishedItemsCount) }}</span>
                    </button>
                    <button type="button" role="tab" aria-selected="false" aria-controls="storefrontAbout"
                        data-storefront-tab="about">{{ translate('About') }}</button>
                </div>
            </div>

            <div id="storefrontPortfolio" class="creator-storefront-panel" data-storefront-panel="portfolio" role="tabpanel">
                <div class="creator-storefront-items">
                    @forelse ($items as $item)
                        @include('themes.basic.partials.item', [
                            'item' => $item,
                            'item_classes' => 'border creator-storefront-item',
                            'show_creator' => false,
                        ])
                    @empty
                        <div class="creator-storefront-empty public-empty-state card-v border">
                            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
                            <h2 class="h5 mb-2">{{ translate('No products published yet') }}</h2>
                            <p class="text-muted mb-3">{{ translate('Learn more about this creator and check back for future releases.') }}</p>
                            <a href="#storefrontAbout" class="btn btn-outline-primary btn-md" data-storefront-tab="about">
                                {{ translate('About this creator') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                {{ $items->links() }}
            </div>

            <div id="storefrontAbout" class="creator-storefront-panel" data-storefront-panel="about" role="tabpanel" hidden>
                <div class="creator-storefront-about">
                    <h3>{{ translate('About') }}</h3>
                    @if ($user->profile_heading)
                        <h4>{{ $user->profile_heading }}</h4>
                    @endif
                    @if ($user->profile_description)
                        <div class="creator-storefront-about-text">
                            {!! $user->profile_description !!}
                        </div>
                    @else
                        <p>{{ translate('This creator has not added an about section yet.') }}</p>
                    @endif
                </div>
            </div>
        </main>

        <nav class="creator-storefront-mobile-nav" aria-label="{{ translate('Storefront mobile navigation') }}">
            <button type="button" class="active" data-storefront-mobile-tab="profile"
                aria-label="{{ translate('Mobile Profile') }}">
                <i class="fa-regular fa-user"></i>
                <span>{{ translate('Profile') }}</span>
            </button>
            <button type="button" data-storefront-mobile-tab="portfolio"
                aria-label="{{ translate('Mobile Products') }}">
                <i class="fa-solid fa-cube"></i>
                <span>{{ translate('Products') }}</span>
            </button>
            <button type="button" data-storefront-mobile-tab="about"
                aria-label="{{ translate('Mobile About') }}">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ translate('About') }}</span>
            </button>
        </nav>
    </div>

    <div class="modal fade" id="storefrontContactModal" tabindex="-1"
        aria-labelledby="storefrontContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="modal-header p-0 border-0 mb-3">
                    <h5 class="modal-title" id="storefrontContactModalLabel">
                        {{ translate('Contact :username', ['username' => $user->username]) }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ translate('Close') }}"></button>
                </div>
                <div class="modal-body p-0">
                    @if (authUser())
                        <form action="{{ route('profile.sendmail', $user->username) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ translate('From') }}</label>
                                <input class="form-control form-control-md" value="{{ authUser()->email }}" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ translate('Message') }}</label>
                                <textarea name="message" class="form-control form-control-md"
                                    placeholder="{{ translate('Enter Your Message') }}" rows="6" required>{{ old('message') }}</textarea>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-primary btn-md">{{ translate('Send') }}</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted mb-3">
                            {{ translate('Please sign in to send a message to this creator.') }}
                        </p>
                        <a href="{{ route('login') }}" class="btn btn-primary btn-md">
                            {{ translate('Sign in to message') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="storefrontShareModal" tabindex="-1"
        aria-labelledby="storefrontShareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="modal-header p-0 border-0 mb-3">
                    <h5 class="modal-title" id="storefrontShareModalLabel">
                        {{ translate('Share storefront') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ translate('Close') }}"></button>
                </div>
                <div class="modal-body p-0">
                    <p class="text-muted small mb-3">
                        {{ translate('Share this creator storefront with your audience.') }}
                    </p>
                    @include('themes.basic.partials.share-buttons', [
                        'link' => $storefrontLink,
                        'socials_classes' => 'creator-storefront-share-socials mb-3',
                    ])
                    <label for="storefrontShareLink" class="form-label">{{ translate('Storefront link') }}</label>
                    <div class="input-group">
                        <input id="storefrontShareLink" type="text" class="form-control form-control-md"
                            value="{{ $storefrontLink }}" readonly>
                        <button type="button" class="btn btn-outline-primary btn-md"
                            data-storefront-copy="#storefrontShareLink">
                            <i class="fa-regular fa-copy me-1"></i>
                            {{ translate('Copy') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        "use strict";

        const storefrontTabs = document.querySelectorAll('[data-storefront-tab]');
        const storefrontPanels = document.querySelectorAll('[data-storefront-panel]');
        const storefrontMobileTabs = document.querySelectorAll('[data-storefront-mobile-tab]');
        const storefrontMobilePanels = document.querySelectorAll('[data-storefront-mobile-panel]');
        const storefrontMobileQuery = window.matchMedia('(max-width: 991.98px)');
        let storefrontMobilePanel = 'profile';

        const showStorefrontPanel = (panelName, updateMobile = true) => {
            storefrontTabs.forEach((tab) => {
                const isActive = tab.dataset.storefrontTab === panelName;
                tab.classList.toggle('active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            storefrontPanels.forEach((panel) => {
                panel.hidden = panel.dataset.storefrontPanel !== panelName;
            });

            if (updateMobile && panelName !== 'profile') {
                showStorefrontMobilePanel(panelName, false);
            }
        };

        const showStorefrontMobilePanel = (panelName, updateDesktop = true) => {
            storefrontMobilePanel = panelName;

            storefrontMobileTabs.forEach((tab) => {
                tab.classList.toggle('active', tab.dataset.storefrontMobileTab === panelName);
            });

            storefrontMobilePanels.forEach((panel) => {
                panel.hidden = storefrontMobileQuery.matches && panel.dataset.storefrontMobilePanel !== panelName;
            });

            if (storefrontMobileQuery.matches) {
                document.querySelector('.creator-storefront-main')?.toggleAttribute('hidden', panelName === 'profile');
            } else {
                document.querySelector('.creator-storefront-main')?.removeAttribute('hidden');
                storefrontMobilePanels.forEach((panel) => panel.hidden = false);
            }

            if (updateDesktop && panelName !== 'profile') {
                showStorefrontPanel(panelName, false);
            }
        };

        storefrontTabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                event.preventDefault();
                showStorefrontPanel(tab.dataset.storefrontTab);
            });
        });

        storefrontMobileTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                showStorefrontMobilePanel(tab.dataset.storefrontMobileTab);
            });
        });

        storefrontMobileQuery.addEventListener('change', () => {
            showStorefrontMobilePanel(storefrontMobilePanel, false);
        });

        const openStorefrontModal = (modalId) => {
            const modalElement = document.getElementById(modalId);
            if (modalElement && window.bootstrap?.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        };

        const handleStorefrontHash = () => {
            if (window.location.hash === '#storefrontContact') {
                showStorefrontMobilePanel('profile', false);
                openStorefrontModal('storefrontContactModal');
            } else if (window.location.hash === '#storefrontShare') {
                showStorefrontMobilePanel('profile', false);
                openStorefrontModal('storefrontShareModal');
            } else if (window.location.hash === '#storefrontPortfolio') {
                showStorefrontPanel('portfolio');
            } else if (window.location.hash === '#storefrontAbout') {
                showStorefrontPanel('about');
            } else {
                showStorefrontMobilePanel('profile', false);
            }
        };

        handleStorefrontHash();
        window.addEventListener('load', handleStorefrontHash);

        document.querySelectorAll('[data-storefront-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                const input = document.querySelector(button.dataset.storefrontCopy);
                if (!input) return;

                input.select();
                input.setSelectionRange(0, input.value.length);

                try {
                    await navigator.clipboard.writeText(input.value);
                } catch (error) {
                    document.execCommand('copy');
                }

                toastr.success(config.translates.copied);
            });
        });
    </script>
@endpush
