<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PublicMarketplaceCompactCssTest extends TestCase
{
    public function test_public_marketplace_compact_overrides_are_present_and_scoped(): void
    {
        $cssPath = dirname(__DIR__, 2) . '/public/themes/basic/assets/css/custom.css';

        $this->assertFileExists($cssPath);

        $css = file_get_contents($cssPath);

        $requiredSnippets = [
            'Public marketplace compact scale',
            '@media (min-width: 992px)',
            '.nav-bar .nav-bar-container',
            '.nav-bar.nav-bar-sm .nav-bar-container',
            '.header .header-inner',
            '.header .header-title',
            '.header .header-text',
            '.header .header-search .search',
            '.section',
            '.section .section-title-text',
            '.home-category',
            '.item .item-body',
            '.custom-tabs-item',
            '.accordion-custom .accordion-button',
            '.testimonial',
            '.footer .footer-upper',
        ];

        foreach ($requiredSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $widePublicSnippets = [
            'Public marketplace wide compact pass',
            '.card-v.border.p-4',
            '#searchFiltersSidebar .card-v',
            '#searchFiltersMenu .offcanvas-body',
            '.filter-item',
            '.item-single-title',
            '.item-single-preview',
            '.tabs-custom .card-v',
            '.item-single-paragraph',
            '.item-review',
            '.comment',
            '.cart-item',
            '.payment-method',
            '.blog-post',
            '.blog-container',
            '.support-article-link',
            '.profile-header',
            '.plan',
            '.form-control.form-control-md',
            '.table-container .table',
        ];

        foreach ($widePublicSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $dashboardSnippets = [
            'Workspace dashboard compact pass',
            '.dashboard-sidebar',
            '.dashboard-sidebar .dashboard-sidebar-header',
            '.dashboard-sidebar-link .dashboard-sidebar-link-title',
            '.dashboard-balance',
            '.dashboard-body',
            '.dashboard-nav',
            '.dashboard-container',
            '.dashboard-counter',
            '.dashboard-counter .dashboard-counter-icon',
            '.dashboard-card',
            '.dashboard-chart',
            '.dashboard-item:not(:last-child)',
            '.dashboard-tabs .dashboard-tabs-nav-item',
            '.table.dashboard-table',
            '.dropzone-drag .dropzone-drag-inner',
            '.dropzone .dz-preview',
            '.uploaded-file',
            '.dashboard-footer',
        ];

        foreach ($dashboardSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $dashboardDenseSnippets = [
            'Workspace dashboard dense refinement',
            '.settings-links',
            '.settings-links-inner',
            '.settings-link',
            '.dashboard .form-label',
            '.dashboard .form-control.form-control-md',
            '.dashboard .form-select.form-select-md',
            '.dashboard .btn.btn-md',
            '.dashboard .row.g-3',
            '.dashboard .mb-4',
            '.dashboard .p-4',
            '.dashboard .social-btn',
            '.dashboard .image-preview-box',
        ];

        foreach ($dashboardDenseSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $sharedConsistencySnippets = [
            'Shared UI consistency polish',
            ':root',
            '--ui-card-radius',
            '.nav-bar .logo img',
            '.nav-bar .btn',
            '.section .section-header',
            '.card-v',
            '.btn.btn-md',
            '.form-search .form-control',
            '.item .item-title',
            '.blog-post .blog-post-title',
            '.footer .logo img',
            '.footer-counter .footer-counter-text',
            '.dashboard .card-v',
            '.dashboard .btn',
        ];

        foreach ($sharedConsistencySnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $profileSettingsBalanceSnippets = [
            'Profile and settings balance polish',
            '.header-profile .header-inner',
            '.header-profile .user-avatar.user-avatar-xl',
            '.header-profile .tabs-custom.v2',
            '.dashboard .settings-links',
            '.dashboard .settings-links-inner',
            '.dashboard .settings-link',
        ];

        foreach ($profileSettingsBalanceSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $settingsEqualHeightSnippets = [
            'Settings link equal-height polish',
            '.dashboard .settings-link',
            'height: 38px',
            'white-space: nowrap',
            'text-overflow: ellipsis',
        ];

        foreach ($settingsEqualHeightSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $workspaceMobileSidebarSnippets = [
            'Workspace mobile sidebar compact polish',
            '.dashboard-sidebar .dashboard-sidebar-body',
            '.dashboard.toggle .dashboard-sidebar .dashboard-sidebar-body',
            'width: 236px',
            'width: 218px',
            '.dashboard-sidebar-link .dashboard-sidebar-link-title',
            '.dashboard-balance',
        ];

        foreach ($workspaceMobileSidebarSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $css);
        }

        $dashboardMarker = '/* Workspace dashboard compact pass */';
        $this->assertStringContainsString($dashboardMarker, $css);

        $publicCss = strstr($css, $dashboardMarker, true);

        $this->assertIsString($publicCss);
        $this->assertStringNotContainsString('.dashboard-', $publicCss);
    }
}
