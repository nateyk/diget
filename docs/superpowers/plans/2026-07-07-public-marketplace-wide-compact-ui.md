# Public Marketplace Wide Compact UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Extend compact sizing across public marketplace buying flow and other public theme pages while leaving workspace/dashboard untouched.

**Architecture:** Keep all visual changes in `public/themes/basic/assets/css/custom.css`, which is the theme custom override layer. Extend the existing PHPUnit CSS guard to cover the wider public component selectors and dashboard exclusion.

**Tech Stack:** Laravel 10, Blade, Bootstrap-style theme CSS, PHPUnit.

---

### Task 1: Extend CSS Guard

**Files:**
- Modify: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] **Step 1: Add wider public selector expectations**

Add a second required snippet group to the existing test that expects the marker `Public marketplace wide compact pass` and selectors for:

```php
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
```

- [ ] **Step 2: Run the focused test and verify it fails**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: FAIL because the wider compact marker and selectors are not in `custom.css`.

### Task 2: Add Wider Public Compact CSS

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Append the wider public compact block**

Append CSS with marker `Public marketplace wide compact pass`. Include desktop rules for shared cards/forms, browse filters, item detail, cart/checkout, blog/help/profile/premium, and tables. Include smaller mobile adjustments for cards/forms/cart rows.

- [ ] **Step 2: Run focused guard**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: PASS.

### Task 3: Verify Public UI Assets

**Files:**
- Read: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Check CSS brace balance**

Run: PowerShell brace count against `public/themes/basic/assets/css/custom.css`.

Expected: `balanced`

- [ ] **Step 2: Clear Laravel cache**

Run: `php artisan optimize:clear`

Expected: Laravel cache clear output with exit code 0.

- [ ] **Step 3: Run full test suite**

Run: `vendor/bin/phpunit`

Expected: all tests pass.
