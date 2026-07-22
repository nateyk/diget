<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PublicUiFoundationTest extends TestCase
{
    public function test_public_ui_uses_semantic_navigation_and_trustworthy_fallbacks(): void
    {
        $root = dirname(__DIR__, 2);
        $navbar = file_get_contents($root . '/resources/views/themes/basic/includes/navbar.blade.php');
        $footer = file_get_contents($root . '/resources/views/themes/basic/includes/footer.blade.php');
        $head = file_get_contents($root . '/resources/views/themes/basic/includes/head.blade.php');
        $authLayout = file_get_contents($root . '/resources/views/themes/basic/layouts/auth.blade.php');
        $cookieNotice = file_get_contents($root . '/resources/views/themes/basic/components/partials.blade.php');
        $javascript = file_get_contents($root . '/public/themes/basic/assets/js/app.js');
        $emptyState = file_get_contents($root . '/resources/views/themes/basic/partials/public-empty-state.blade.php');

        $this->assertStringContainsString('<button type="button" class="drop-down-btn">', $navbar);
        $this->assertStringContainsString('aria-controls="primary-navigation"', $navbar);
        $this->assertStringNotContainsString('class="announcement"', $navbar);
        $this->assertStringContainsString('brand-wordmark', $navbar);
        $this->assertStringContainsString('brand-wordmark', $footer);
        $this->assertStringNotContainsString('footer-counter-wrap', $footer);
        $this->assertStringContainsString("trim('/')->lower()->value() !== 'page-example'", $footer);
        $this->assertStringContainsString('brand-wordmark', $authLayout);
        $this->assertStringContainsString("config('app.name', 'Diget')", $head);
        $this->assertStringNotContainsString('seoTitle($__env)', $head);

        $this->assertStringContainsString('role="region"', $cookieNotice);
        $this->assertStringContainsString('aria-live="polite"', $cookieNotice);
        $this->assertStringContainsString('id="acceptCookie"', $cookieNotice);

        $this->assertStringContainsString('aria-controls', $javascript);
        $this->assertStringContainsString('closeNavbarMenu(true)', $javascript);
        $this->assertStringContainsString('!el.contains(event.target)', $javascript);

        $this->assertStringContainsString('public-empty-state', $emptyState);
        $this->assertStringContainsString('$actionUrl && $actionLabel', $emptyState);
    }
}
