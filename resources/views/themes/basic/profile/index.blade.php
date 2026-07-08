@extends('themes.basic.profile.layout')
@section('title', $user->username)
@section('content')
    @php
        $cardDescription = trim($user->profile_card_description ?? '');
        $profileHeading = $user->profile_heading ?: translate('Digital creator');
        $socialLinks = $user->profile_social_links;
        $socialHandle = fn($value) => ltrim(trim($value), '@');
        $publishedItemsCount = $items->total();
    @endphp

    <div class="creator-storefront">
        <aside class="creator-storefront-card">
            <div class="creator-storefront-cover">
                <img src="{{ $user->getProfileCover() }}" alt="{{ $user->getName() }}">
            </div>
            <div class="creator-storefront-card-body">
                <div class="creator-storefront-card-top">
                    <a href="{{ $user->getProfileLink() }}" class="creator-storefront-avatar">
                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->username }}">
                    </a>
                    <div class="creator-storefront-identity">
                        <div class="d-flex align-items-center gap-2">
                            <h1>{{ $user->getName() }}</h1>
                            @if ($user->isAuthor())
                                <i class="fa-solid fa-circle-check"></i>
                            @endif
                        </div>
                        <a href="{{ $user->getProfileLink() }}" class="creator-storefront-username">
                            {{ '@' . $user->username }}
                        </a>
                    </div>
                    <div class="creator-storefront-follow">
                        <livewire:follow-button :user="$user" />
                    </div>
                </div>

                <p class="creator-storefront-heading">{{ $profileHeading }}</p>

                <div class="creator-storefront-actions">
                    @if ($user->profile_contact_email)
                        <a href="#storefrontContact" class="btn btn-outline-secondary btn-md">
                            <i class="fa-regular fa-message me-1"></i>
                            {{ translate('Message') }}
                        </a>
                    @endif
                    @if ($user->isAuthor())
                        <a href="{{ $user->getPortfolioLink() }}" class="btn btn-outline-secondary btn-md">
                            <i class="fa-regular fa-eye me-1"></i>
                            {{ translate('Portfolio') }}
                        </a>
                    @endif
                </div>

                @if ($cardDescription)
                    <p class="creator-storefront-bio">{{ $cardDescription }}</p>
                @endif

                @if ($socialLinks)
                    <div class="creator-storefront-socials socials">
                        @if ($socialLinks->facebook)
                            <a href="https://facebook.com/{{ $socialHandle($socialLinks->facebook) }}" target="_blank"
                                class="social-btn social-facebook" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        @endif
                        @if ($socialLinks->x)
                            <a href="https://x.com/{{ $socialHandle($socialLinks->x) }}" target="_blank"
                                class="social-btn social-x" aria-label="X">
                                <i class="fab fa-x-twitter"></i>
                            </a>
                        @endif
                        @if ($socialLinks->instagram)
                            <a href="https://instagram.com/{{ $socialHandle($socialLinks->instagram) }}" target="_blank"
                                class="social-btn social-instagram" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        @endif
                        @if ($socialLinks->linkedin)
                            <a href="https://linkedin.com/in/{{ $socialHandle($socialLinks->linkedin) }}" target="_blank"
                                class="social-btn social-linkedin" aria-label="LinkedIn">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        @endif
                        @if ($socialLinks->youtube)
                            <a href="{{ 'https://youtube.com/@' . $socialHandle($socialLinks->youtube) }}" target="_blank"
                                class="social-btn social-youtube" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        @endif
                        @if ($socialLinks->pinterest)
                            <a href="https://pinterest.com/{{ $socialHandle($socialLinks->pinterest) }}" target="_blank"
                                class="social-btn social-pinterest" aria-label="Pinterest">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        @endif
                    </div>
                @endif

                <div class="creator-storefront-stats">
                    <div>
                        <strong>{{ numberFormat($publishedItemsCount) }}</strong>
                        <span>{{ translate('Items') }}</span>
                    </div>
                    <div>
                        <strong>{{ numberFormat($user->total_sales) }}</strong>
                        <span>{{ translate('Sales') }}</span>
                    </div>
                    <div>
                        <strong>{{ $user->avg_reviews > 0 ? number_format($user->avg_reviews, 1) : '0.0' }}</strong>
                        <span>{{ translate($user->total_reviews == 1 ? '1 review' : ':count reviews', [
                            'count' => numberFormat($user->total_reviews),
                        ]) }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="creator-storefront-main">
            <div class="creator-storefront-main-header">
                <div>
                    <h2>{{ translate('Storefront') }}</h2>
                    <p>{{ translate($publishedItemsCount == 1 ? '1 published item' : ':count published items', [
                        'count' => numberFormat($publishedItemsCount),
                    ]) }}</p>
                </div>
                <div class="creator-storefront-tabs">
                    <a href="#storefrontPortfolio" class="active">{{ translate('Portfolio') }}</a>
                    <a href="#storefrontAbout">{{ translate('About') }}</a>
                </div>
            </div>

            <div id="storefrontPortfolio" class="creator-storefront-items">
                @forelse ($items as $item)
                    <a href="{{ $item->getLink() }}" class="storefront-item-card">
                        <span class="storefront-item-preview">
                            @if ($item->isPreviewFileTypeImage() || $item->isPreviewFileTypeVideo() || $item->isPreviewFileTypeAudio())
                                <img src="{{ $item->getPreviewImageLink() }}" alt="{{ $item->name }}">
                            @else
                                <span class="storefront-item-placeholder">
                                    <i class="fa-regular fa-file"></i>
                                </span>
                            @endif
                            <span class="storefront-item-price">
                                @if ($item->isFree())
                                    {{ translate('Free') }}
                                @else
                                    {{ getAmount($item->getRegularPrice(), 2, '.', '', true) }}
                                @endif
                            </span>
                        </span>
                        <span class="storefront-item-body">
                            <strong>{{ $item->name }}</strong>
                            <span class="storefront-item-meta">
                                @if (@$settings->item->reviews_status && $item->hasReviews())
                                    <span>
                                        <i class="fa-solid fa-star"></i>
                                        {{ number_format($item->avg_reviews, 1) }}
                                        ({{ numberFormat($item->total_reviews) }})
                                    </span>
                                @endif
                                @if ($item->hasSales())
                                    <span>{{ translate($item->total_sales == 1 ? '1 sale' : ':count sales', [
                                        'count' => numberFormat($item->total_sales),
                                    ]) }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @empty
                    <div class="creator-storefront-empty">
                        <i class="fa-regular fa-file-lines"></i>
                        <p>{{ translate('No published items yet') }}</p>
                    </div>
                @endforelse
            </div>

            {{ $items->links() }}

            <div id="storefrontAbout" class="creator-storefront-about">
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

            @if ($user->profile_contact_email)
                <div id="storefrontContact" class="creator-storefront-contact">
                    <h3>{{ translate('Contact :username', ['username' => $user->username]) }}</h3>
                    @if (authUser())
                        <form action="{{ route('profile.sendmail', $user->username) }}" method="POST">
                            @csrf
                            <textarea name="message" class="form-control form-control-md"
                                placeholder="{{ translate('Enter Your Message') }}" rows="4" required>{{ old('message') }}</textarea>
                            <button class="btn btn-primary btn-md mt-2">{{ translate('Send message') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-md">
                            {{ translate('Sign in to message') }}
                        </a>
                    @endif
                </div>
            @endif
        </main>
    </div>
@endsection
