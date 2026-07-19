<div class="socials {{ $socials_classes ?? '' }}">
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $link }}" target="_blank"
        class="social-btn social-facebook" aria-label="{{ translate('Share on Facebook') }}">
        <i class="fa-brands fa-facebook-f"></i>
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $link }}" target="_blank" class="social-btn social-x"
        aria-label="{{ translate('Share on X') }}">
        <i class="fa-brands fa-x-twitter"></i>
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $link }}" target="_blank"
        class="social-btn social-linkedin" aria-label="{{ translate('Share on LinkedIn') }}">
        <i class="fa-brands fa-linkedin-in"></i>
    </a>
    <a href="https://wa.me/?text={{ $link }}" target="_blank" class="social-btn social-whatsapp"
        aria-label="{{ translate('Share on WhatsApp') }}">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <a href="http://pinterest.com/pin/create/button/?url={{ $link }}" target="_blank"
        class="social-btn social-pinterest" aria-label="{{ translate('Share on Pinterest') }}">
        <i class="fa-brands fa-pinterest-p"></i>
    </a>
</div>
