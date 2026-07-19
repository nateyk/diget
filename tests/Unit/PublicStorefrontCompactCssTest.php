<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PublicStorefrontCompactCssTest extends TestCase
{
    public function test_public_storefront_compact_overrides_are_present_and_scoped(): void
    {
        $cssPath = dirname(__DIR__, 2) . '/public/themes/basic/assets/css/custom.css';
        $workspaceCssPath = dirname(__DIR__, 2) . '/public/themes/basic/assets/css/app.css';

        $this->assertFileExists($cssPath);
        $this->assertFileExists($workspaceCssPath);

        $css = file_get_contents($cssPath);
        $workspaceCss = file_get_contents($workspaceCssPath);

        $requiredSnippets = [
            'Public storefront compact scale',
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
            'Public storefront wide compact pass',
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

        $itemLayoutPath = dirname(__DIR__, 2) . '/resources/views/themes/basic/items/layout.blade.php';

        $this->assertFileExists($itemLayoutPath);

        $itemLayout = file_get_contents($itemLayoutPath);

        $this->assertStringContainsString('item-detail-page', $itemLayout);
        $this->assertStringNotContainsString("@include('themes.basic.includes.navbar')", $itemLayout);
        $this->assertStringNotContainsString("@include('themes.basic.includes.footer')", $itemLayout);
        $this->assertStringNotContainsString('item_page_top', $itemLayout);
        $this->assertStringNotContainsString('item_page_bottom', $itemLayout);
        $this->assertStringNotContainsString("@yield('breadcrumbs')", $itemLayout);

        $itemDetailSnippets = [
            'Product detail clean professional polish',
            '.item-detail-page.section',
            '.item-detail-page .section-header',
            '.item-detail-page .item-single-title',
            '.item-detail-page .item-detail-card',
            'box-shadow: none',
            '.item-detail-page .item-single-img img',
            '.item-detail-page .item-slide-img',
            '.item-detail-page .card-v-header',
            '.item-detail-page .item-detail-sidebar .card-v',
            '.item-detail-page .item-detail-meta-row',
            '.item-detail-page .socials .social-btn',
        ];

        foreach ($itemDetailSnippets as $snippet) {
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

        $dashboardTableCardSnippets = [
            'Workspace dashboard table card density polish',
            '.dashboard .dashboard-card .table-search.p-4',
            'padding: 14px !important',
            '.dashboard .dashboard-card .table-search .row.g-3',
            'row-gap: 10px !important',
            '.dashboard .dashboard-card .table-search .form-control.form-control-md',
            'min-height: 36px',
            '.dashboard .dashboard-card .table-container .dashboard-table',
            '.dashboard .dashboard-card .dashboard-table tbody td',
            'padding: 8px 10px',
            '.dashboard .dashboard-card .item-img.item-img-sm',
            'width: 58px',
            '.dashboard .dashboard-card .btn.btn-padding',
            'min-width: 32px',
            '.dashboard .dashboard-card .badge',
            'padding: 5px 9px !important',
        ];

        foreach ($dashboardTableCardSnippets as $snippet) {
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
            'Workspace sidebar',
            '.workspace-dashboard .workspace-sidebar .dashboard-sidebar-body',
            '.workspace-dashboard.toggle .workspace-sidebar .dashboard-sidebar-body',
            'width: 240px',
            'left: -240px',
            '.dashboard-sidebar-link .dashboard-sidebar-link-title',
            '.dashboard-balance',
        ];

        foreach ($workspaceMobileSidebarSnippets as $snippet) {
            $this->assertStringContainsString($snippet, $workspaceCss);
        }
    }
}
