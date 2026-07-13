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
        $this->assertStringContainsString('card-v border item-detail-card item-detail-author-card creator-storefront-card', $indexView);
        $this->assertStringContainsString('data-storefront-mobile-panel="profile"', $indexView);
        $this->assertStringContainsString('creator-storefront-main', $indexView);
        $this->assertStringContainsString('creator-storefront-items', $indexView);
        $this->assertStringContainsString("@include('themes.basic.partials.item'", $indexView);
        $this->assertStringContainsString("'item_classes' => 'border'", $indexView);
        $this->assertStringContainsString('creator-storefront-cover-banner', $indexView);
        $this->assertStringContainsString('$user->getProfileCover()', $indexView);
        $this->assertStringNotContainsString('style="background-image', $indexView);
        $this->assertStringNotContainsString('Available for work', $indexView);
        $this->assertStringNotContainsString('creator-storefront-status', $indexView);
        $this->assertStringNotContainsString('row row-cols-auto align-items-center g-2', $indexView);
        $this->assertStringNotContainsString('user-avatar user-avatar-lg', $indexView);
        $this->assertStringNotContainsString('<h5 class="mb-0">', $indexView);
        $this->assertStringContainsString('{{ $user->getName() }}', $indexView);
        $this->assertStringContainsString('text-muted small', $indexView);
        $this->assertStringNotContainsString('creator-storefront-action-follow', $indexView);
        $this->assertStringContainsString('creator-storefront-avatar', $indexView);
        $this->assertStringContainsString('creator-storefront-identity', $indexView);
        $this->assertStringNotContainsString('creator-storefront-username', $indexView);
        $this->assertStringNotContainsString('{{ \'@\' . $user->username }}', $indexView);
        $this->assertStringContainsString('creator-storefront-heading', $indexView);
        $this->assertStringNotContainsString('@{{ $user->username }}', $indexView);
        $this->assertStringNotContainsString('$user->getPortfolioLink()', $indexView);
        $this->assertStringNotContainsString('<h2>{{ translate(\'Storefront\') }}</h2>', $indexView);
        $this->assertStringNotContainsString(':count published items', $indexView);
        $this->assertStringContainsString('storefrontPortfolio', $indexView);
        $this->assertStringContainsString("{{ translate('Portfolio') }}", $indexView);
        $this->assertStringNotContainsString("{{ translate('Items') }}</a>", $indexView);
        $this->assertStringContainsString('data-storefront-tab="portfolio"', $indexView);
        $this->assertStringContainsString('data-storefront-tab="about"', $indexView);
        $this->assertStringContainsString('data-storefront-panel="portfolio"', $indexView);
        $this->assertStringContainsString('data-storefront-panel="about"', $indexView);
        $this->assertStringContainsString('creator-storefront-mobile-nav', $indexView);
        $this->assertStringContainsString('data-storefront-mobile-tab="profile"', $indexView);
        $this->assertStringContainsString('data-storefront-mobile-tab="portfolio"', $indexView);
        $this->assertStringContainsString('data-storefront-mobile-tab="about"', $indexView);
        $this->assertStringContainsString('storefrontMobileQuery', $indexView);
        $this->assertStringContainsString('showStorefrontMobilePanel', $indexView);
        $this->assertStringContainsString('storefrontTabs.forEach', $indexView);
        $this->assertStringContainsString('storefrontPanels.forEach', $indexView);
        $this->assertStringContainsString('event.preventDefault()', $indexView);
        $this->assertStringContainsString('$socialHandle = fn($value) => ltrim(trim($value), \'@\')', $indexView);
        $this->assertStringContainsString('class="creator-storefront-socials socials"', $indexView);
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
        $this->assertStringContainsString('{{ $cardDescription }}', $indexView);
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

        $this->assertStringContainsString('Creator storefront', $css);
        $this->assertStringContainsString('.creator-storefront', $css);
        $this->assertStringContainsString('.creator-storefront-items .item', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(260px, 330px) minmax(0, 1fr)', $css);
        $this->assertStringContainsString('.creator-storefront-empty', $css);
        $this->assertStringContainsString('.creator-storefront-panel[hidden]', $css);
        $this->assertStringContainsString('border-bottom: 1px solid var(--border_color)', $css);
        $this->assertStringContainsString('border-bottom: 2px solid transparent', $css);
        $this->assertStringContainsString('border-bottom-color: var(--secondary_color)', $css);
        $this->assertStringContainsString('width: calc(100% - 56px)', $css);
        $this->assertStringContainsString('.creator-storefront-mobile-nav', $css);
        $this->assertStringContainsString('bottom: calc(14px + env(safe-area-inset-bottom))', $css);
        $this->assertStringContainsString('grid-template-columns: 1fr', $css);
        $this->assertStringContainsString('min-width: 94px', $css);
        $this->assertStringContainsString('background-color: var(--secondary_color)', $css);
        $this->assertStringContainsString('border-radius: 10px', $css);
        $this->assertStringContainsString('.creator-storefront-stats', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-card', $css);
        $this->assertStringNotContainsString('.creator-storefront-card-top', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-avatar', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-identity', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-heading', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-actions', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-socials', $css);
        $this->assertStringContainsString('.profile-storefront-page .creator-storefront-cover-banner', $css);
        $this->assertStringContainsString('gap: 8px', $css);
        $this->assertStringContainsString('margin-top: -30px', $css);
        $this->assertStringNotContainsString('margin-bottom: -', $css);
        $this->assertStringNotContainsString('background-color: #202124', $css);
    }
}
