<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HomeLandingHeroTest extends TestCase
{
    public function test_homepage_uses_creator_storefront_landing_hero(): void
    {
        $root = dirname(__DIR__, 2);
        $homeView = file_get_contents($root . '/resources/views/themes/basic/home.blade.php');
        $layoutView = file_get_contents($root . '/resources/views/themes/basic/layouts/app.blade.php');
        $css = file_get_contents($root . '/public/themes/basic/assets/css/custom.css');

        $this->assertStringContainsString('home-landing-hero', $homeView);
        $this->assertStringContainsString('home-landing-hero-grid', $homeView);
        $this->assertStringContainsString('home-landing-copy', $homeView);
        $this->assertStringContainsString('home-landing-visual', $homeView);
        $this->assertStringContainsString('<img src="{{ asset($themeSettings->home_page->header_background) }}"', $homeView);
        $this->assertStringContainsString('Premium creator storefronts', $homeView);
        $this->assertStringContainsString('Launch your storefront, sell digital products, and grow a polished creator brand from one workspace.', $homeView);
        $this->assertStringContainsString('Start selling', $homeView);
        $this->assertStringContainsString('Browse products', $homeView);
        $this->assertStringNotContainsString('home-landing-search', $homeView);
        $this->assertStringNotContainsString('Search storefront products', $homeView);
        $this->assertStringNotContainsString("partials.search-form", $homeView);
        $this->assertStringContainsString("@continue(\$homeSection->alias === 'categories')", $homeView);

        $this->assertStringNotContainsString('class="header header-image"', $homeView);
        $this->assertStringNotContainsString('style=\'background-image', $homeView);

        $this->assertStringContainsString("@include('themes.basic.includes.navbar')", $layoutView);
        $this->assertStringNotContainsString("routeIs('home')", $layoutView);

        $this->assertStringContainsString('Creator storefront landing hero', $css);
        $this->assertStringContainsString('.home-landing-hero', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) minmax(320px, 520px)', $css);
        $this->assertStringContainsString('.home-landing-visual img', $css);
        $this->assertStringContainsString('object-fit: contain', $css);
        $this->assertStringContainsString('.home-landing-actions', $css);
        $this->assertStringNotContainsString('.home-landing-search', $css);
    }
}
