@php
    $socialPlatforms = config('profile_socials.platforms', []);
    $socialLinksArray = collect((array) ($socialLinks ?? null))
        ->filter(fn($value) => filled($value))
        ->take(config('profile_socials.max_links', 7));

    $profileSocialUrl = function ($platform, $value) use ($socialPlatforms) {
        $value = trim((string) $value);

        if (preg_match('/^(https?:|mailto:|tel:)/i', $value)) {
            return $value;
        }

        if ($platform === 'website') {
            return 'https://' . ltrim($value, '/');
        }

        if ($platform === 'whatsapp') {
            return ($socialPlatforms[$platform]['prefix'] ?? '') . preg_replace('/\D+/', '', $value);
        }

        $prefix = $socialPlatforms[$platform]['prefix'] ?? '';

        return $prefix . ltrim($value, '@/');
    };
@endphp

@if ($socialLinksArray->count() > 0)
    <div class="{{ $class ?? 'socials' }}">
        @foreach ($socialLinksArray as $platform => $value)
            @continue(!isset($socialPlatforms[$platform]))
            <a href="{{ $profileSocialUrl($platform, $value) }}" target="_blank" rel="noopener"
                class="social-btn {{ $socialPlatforms[$platform]['class'] }}"
                aria-label="{{ $socialPlatforms[$platform]['label'] }}">
                <i class="{{ $socialPlatforms[$platform]['icon'] }}"></i>
            </a>
        @endforeach
    </div>
@endif
