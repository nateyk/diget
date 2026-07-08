<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CreatorStorefrontUiTest extends TestCase
{
    public function test_creator_storefront_profile_assets_are_present(): void
    {
        $root = dirname(__DIR__, 2);
        $controller = file_get_contents($root . '/app/Http/Controllers/ProfileController.php');
        $settingsController = file_get_contents($root . '/app/Http/Controllers/Workspace/SettingsController.php');
        $userModel = file_get_contents($root . '/app/Models/User.php');
        $indexView = file_get_contents($root . '/resources/views/themes/basic/profile/index.blade.php');
        $layoutView = file_get_contents($root . '/resources/views/themes/basic/profile/layout.blade.php');
        $settingsProfileView = file_get_contents($root . '/resources/views/themes/basic/workspace/settings/profile.blade.php');
        $css = file_get_contents($root . '/public/themes/basic/assets/css/custom.css');
        $profileCardDescriptionMigrations = glob($root . '/database/migrations/*_add_profile_card_description_to_users_table.php');
        $profileCardDescriptionBackfillMigrations = glob($root . '/database/migrations/*_backfill_profile_card_description_from_profile_description.php');

        $this->assertStringContainsString('\'items\' => $items', $controller);
        $this->assertStringContainsString('Item::where(\'author_id\', $user->id)', $controller);
        $this->assertStringContainsString('->approved()', $controller);

        $this->assertStringContainsString('profile-storefront-page', $layoutView);
        $this->assertMatchesRegularExpression(
            "/@unless \(request\(\)->routeIs\('profile\.index'\)\)\s*@include\('themes\.basic\.includes\.navbar'\)\s*@endunless/s",
            $layoutView
        );
        $this->assertMatchesRegularExpression(
            "/@unless \(request\(\)->routeIs\('profile\.index'\)\)\s*@include\('themes\.basic\.includes\.footer'\)\s*@endunless/s",
            $layoutView
        );
        $this->assertStringContainsString('creator-storefront', $indexView);
        $this->assertStringContainsString('creator-storefront-card', $indexView);
        $this->assertStringContainsString('creator-storefront-main', $indexView);
        $this->assertStringContainsString('creator-storefront-items', $indexView);
        $this->assertStringContainsString('storefront-item-card', $indexView);
        $this->assertStringContainsString('<div class="creator-storefront-cover">', $indexView);
        $this->assertStringContainsString('<img src="{{ $user->getProfileCover() }}" alt="{{ $user->getName() }}">', $indexView);
        $this->assertStringNotContainsString('style="background-image', $indexView);
        $this->assertStringNotContainsString('Available for work', $indexView);
        $this->assertStringNotContainsString('creator-storefront-status', $indexView);
        $this->assertStringContainsString('creator-storefront-follow', $indexView);
        $this->assertMatchesRegularExpression(
            '/creator-storefront-avatar.*creator-storefront-identity.*creator-storefront-follow/s',
            $indexView
        );
        $this->assertStringContainsString('<h1>{{ $user->getName() }}</h1>', $indexView);
        $this->assertStringContainsString('creator-storefront-username', $indexView);
        $this->assertStringContainsString('{{ \'@\' . $user->username }}', $indexView);
        $this->assertStringContainsString('creator-storefront-heading', $indexView);
        $this->assertStringNotContainsString('@{{ $user->username }}', $indexView);
        $this->assertStringContainsString('Storefront', $indexView);
        $this->assertStringContainsString('storefrontPortfolio', $indexView);
        $this->assertStringContainsString("{{ translate('Portfolio') }}", $indexView);
        $this->assertStringNotContainsString("{{ translate('Items') }}</a>", $indexView);
        $this->assertStringContainsString('$socialHandle = fn($value) => ltrim(trim($value), \'@\')', $indexView);
        $this->assertStringContainsString('creator-storefront-socials socials', $indexView);
        $this->assertStringContainsString('social-btn social-facebook', $indexView);
        $this->assertStringContainsString('social-btn social-x', $indexView);
        $this->assertStringContainsString('social-btn social-linkedin', $indexView);
        $this->assertStringContainsString('social-btn social-youtube', $indexView);
        $this->assertStringContainsString('social-btn social-instagram', $indexView);
        $this->assertStringContainsString('social-btn social-pinterest', $indexView);
        $this->assertStringContainsString('fab fa-linkedin', $indexView);
        $this->assertStringContainsString('fab fa-pinterest', $indexView);
        $this->assertStringContainsString('{{ \'https://youtube.com/@\' . $socialHandle($socialLinks->youtube) }}', $indexView);
        $this->assertStringNotContainsString('youtube.com/@{{ $socialHandle($socialLinks->youtube) }}', $indexView);
        $this->assertStringContainsString('$cardDescription = trim($user->profile_card_description ?? \'\')', $indexView);
        $this->assertStringNotContainsString('Str::words($profileDescription, 100', $indexView);
        $this->assertStringContainsString('creator-storefront-bio', $indexView);
        $this->assertMatchesRegularExpression('/creator-storefront-bio.*\\$cardDescription/s', $indexView);
        $this->assertMatchesRegularExpression('/creator-storefront-about-text.*\\$user->profile_description/s', $indexView);

        $this->assertNotEmpty($profileCardDescriptionMigrations);
        $migration = file_get_contents($profileCardDescriptionMigrations[0]);
        $this->assertStringContainsString('profile_card_description', $migration);
        $this->assertStringContainsString('after(\'profile_heading\')', $migration);
        $this->assertNotEmpty($profileCardDescriptionBackfillMigrations);
        $backfillMigration = file_get_contents($profileCardDescriptionBackfillMigrations[0]);
        $this->assertStringContainsString('profile_card_description', $backfillMigration);
        $this->assertStringContainsString('profile_description', $backfillMigration);
        $this->assertStringContainsString('Str::words', $backfillMigration);

        $this->assertStringContainsString('profile_card_description', $userModel);
        $this->assertStringContainsString('profile_card_description', $settingsController);
        $this->assertStringContainsString('str_word_count(strip_tags', $settingsController);
        $this->assertStringContainsString('profile_card_description', $settingsProfileView);
        $this->assertStringContainsString('Creator Card Description', $settingsProfileView);
        $this->assertStringContainsString('100 words', $settingsProfileView);

        $this->assertStringContainsString('Creator storefront profile polish', $css);
        $this->assertStringContainsString('.creator-storefront', $css);
        $this->assertStringContainsString('.creator-storefront-card', $css);
        $this->assertStringContainsString('.storefront-item-card', $css);
        $this->assertStringContainsString('Creator storefront proportion refinement', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(260px, 330px) minmax(0, 1fr)', $css);
        $this->assertStringContainsString('max-width: 1360px', $css);
        $this->assertStringContainsString('.creator-storefront-empty', $css);
        $this->assertStringContainsString('min-height: 190px', $css);
        $this->assertStringContainsString('.creator-storefront-socials.socials .social-btn', $css);
        $this->assertStringContainsString('border: 0', $css);
        $this->assertStringContainsString('Creator storefront media framing', $css);
        $this->assertStringContainsString('.creator-storefront-cover img', $css);
        $this->assertStringContainsString('object-fit: contain', $css);
        $this->assertStringContainsString('border-radius: 10px', $css);
        $this->assertStringContainsString('Creator storefront compact profile-card layout', $css);
        $this->assertStringContainsString('.creator-storefront-follow', $css);
        $this->assertStringContainsString('object-fit: cover', $css);
        $this->assertStringContainsString('width: 72px', $css);
        $this->assertStringContainsString('height: 72px', $css);
        $this->assertStringContainsString('Creator storefront compact stack fix', $css);
        $this->assertStringContainsString('max-width: 340px', $css);
        $this->assertStringContainsString('justify-self: center', $css);
        $this->assertStringContainsString('Creator storefront identity row placement', $css);
        $this->assertStringContainsString('grid-template-columns: 72px minmax(0, 1fr)', $css);
        $this->assertStringContainsString('padding-top: 42px', $css);
        $this->assertStringContainsString('position: absolute', $css);
        $this->assertStringContainsString('.creator-storefront-heading', $css);
    }
}
