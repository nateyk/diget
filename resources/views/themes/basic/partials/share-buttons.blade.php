<div class="socials {{ $socials_classes ?? '' }}">
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $link }}" target="_blank"
        class="social-btn social-facebook">
        <i class="bi bi-facebook"></i>
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $link }}" target="_blank" class="social-btn social-x">
        <i class="bi bi-twitter-x"></i>
    </a>
    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ $link }}" target="_blank"
        class="social-btn social-linkedin">
        <i class="bi bi-linkedin"></i>
    </a>
    <a href="https://wa.me/?text={{ $link }}" target="_blank" class="social-btn social-whatsapp">
        <i class="bi bi-whatsapp"></i>
    </a>
    <a href="http://pinterest.com/pin/create/button/?url={{ $link }}" target="_blank"
        class="social-btn social-pinterest">
        <i class="bi bi-pinterest"></i>
    </a>
</div>
