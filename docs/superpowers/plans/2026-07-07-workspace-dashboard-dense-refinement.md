# Workspace Dashboard Dense Refinement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply a deeper compact pass to workspace dashboard settings/forms/sidebar/cards after the initial compact UI pass.

**Architecture:** Add a final dashboard-only CSS overlay in `public/themes/basic/assets/css/custom.css`, and extend the existing CSS guard in PHPUnit.

**Tech Stack:** Laravel 10, Blade theme CSS, PHPUnit.

---

### Task 1: Add Dense Guard

**Files:**
- Modify: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] **Step 1: Add dense selector snippets**

Add required snippets for:

```php
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
```

- [ ] **Step 2: Run focused guard and verify it fails**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: FAIL because the dense marker and selectors are missing.

### Task 2: Add Dense Dashboard CSS

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Append dense refinement block**

Append dashboard-only CSS for tighter sidebar, top nav, settings tabs, cards, forms, buttons, rows, image/social controls, tables, and mobile spacing.

- [ ] **Step 2: Run focused guard**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: PASS.

### Task 3: Verify

- [ ] **Step 1: Check CSS brace balance**

Run PowerShell brace count.

Expected: `balanced`

- [ ] **Step 2: Clear Laravel caches**

Run: `php artisan optimize:clear`

Expected: exit code 0.

- [ ] **Step 3: Run full suite**

Run: `vendor/bin/phpunit`

Expected: all tests pass.
