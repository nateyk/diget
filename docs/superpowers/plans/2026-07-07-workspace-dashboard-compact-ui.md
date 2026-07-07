# Workspace Dashboard Compact UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Compact the logged-in workspace dashboard shell, cards, tables, forms, tabs, uploads, and workflow pages.

**Architecture:** Add one dashboard-specific CSS block in `public/themes/basic/assets/css/custom.css`. Update the existing PHPUnit CSS guard so public selectors remain isolated while dashboard selectors are expected only in the workspace compact block.

**Tech Stack:** Laravel 10, Blade, theme CSS, PHPUnit.

---

### Task 1: Update CSS Guard For Dashboard Block

**Files:**
- Modify: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] **Step 1: Split public and dashboard assertions**

Change the final dashboard exclusion so it checks only the CSS before `Public marketplace wide compact pass` ends and before `Workspace dashboard compact pass` begins. Add dashboard snippets:

```php
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
```

- [ ] **Step 2: Run focused test and verify it fails**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: FAIL because the dashboard compact marker and selectors are not yet present.

### Task 2: Add Workspace Dashboard Compact CSS

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Append dashboard compact CSS**

Append the `Workspace dashboard compact pass` CSS block covering sidebar, nav, containers, counters, cards, tables, tabs, forms, modals, alerts, dropzone, uploaded files, and footer.

- [ ] **Step 2: Run focused guard**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: PASS.

### Task 3: Verify

**Files:**
- Read: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Check CSS brace balance**

Run a PowerShell brace count on `public/themes/basic/assets/css/custom.css`.

Expected: `balanced`

- [ ] **Step 2: Clear Laravel caches**

Run: `php artisan optimize:clear`

Expected: exit code 0.

- [ ] **Step 3: Run full tests**

Run: `vendor/bin/phpunit`

Expected: all tests pass.
