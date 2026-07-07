<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CreatorStorefrontUiTest extends TestCase
{
    public function test_creator_storefront_profile_assets_are_present(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Http/Controllers/ProfileController.php');
        $indexView = file_get_contents($root . '/resources/views/themes/basic/profile/index.blade.php');
        $layoutView = file_get_contents($root . '/resources/views/themes/basic/profile/layout.blade.php');
        $css = file_get_contents($root . '/public/themes/basic/assets/css/custom.css');

        $this->assertStringContainsString('\'items\' => $items', $controller);
        $this->assertStringContainsString('Item::where(\'author_id\', $user->id)', $controller);
        $this->assertStringContainsString('->approved()', $controller);

        $this->assertStringContainsString('profile-storefront-page', $layoutView);
        $this->assertStringContainsString('creator-storefront', $indexView);
        $this->assertStringContainsString('creator-storefront-card', $indexView);
        $this->assertStringContainsString('creator-storefront-main', $indexView);
        $this->assertStringContainsString('creator-storefront-items', $indexView);
        $this->assertStringContainsString('storefront-item-card', $indexView);
        $this->assertStringContainsString('Storefront', $indexView);

        $this->assertStringContainsString('Creator storefront profile polish', $css);
        $this->assertStringContainsString('.creator-storefront', $css);
        $this->assertStringContainsString('.creator-storefront-card', $css);
        $this->assertStringContainsString('.storefront-item-card', $css);
    }
}
