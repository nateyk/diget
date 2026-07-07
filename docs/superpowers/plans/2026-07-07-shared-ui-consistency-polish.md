# Shared UI Consistency Polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a final compact consistency layer for public marketplace pages and the user dashboard.

**Architecture:** Keep the implementation CSS-only and reuse the theme's current class vocabulary. Add a PHPUnit guard that checks the final layer exists and remains scoped away from unrelated admin UI.

**Tech Stack:** Laravel, Blade, CSS, PHPUnit.

---

### Task 1: Add CSS Guard

**Files:**
- Modify: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] **Step 1: Write the failing test**

Add required snippets for:

```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor\bin\phpunit tests\Unit\PublicMarketplaceCompactCssTest.php`

Expected: FAIL because `Shared UI consistency polish` is not in `custom.css`.

- [ ] **Step 3: Do not change production CSS until the guard fails**

The failing assertion proves the guard is testing the new layer.

### Task 2: Add Shared CSS Polish Layer

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Add shared variables and public polish**

Append a marker block named `/* Shared UI consistency polish */` with variables and compact rules for nav, headers, cards, buttons, forms, item cards, blog/help cards, footer, and mobile sizing.

- [ ] **Step 2: Add dashboard-scoped polish**

In the same block, add only `.dashboard`-scoped rules for cards, buttons, forms, tables, and footer so admin pages are not changed.

- [ ] **Step 3: Run focused test**

Run: `vendor\bin\phpunit tests\Unit\PublicMarketplaceCompactCssTest.php`

Expected: PASS.

### Task 3: Verify Application

**Files:**
- No production files.

- [ ] **Step 1: Clear Laravel caches**

Run: `php artisan optimize:clear`

Expected: cache clear commands succeed.

- [ ] **Step 2: Run full PHPUnit**

Run: `vendor\bin\phpunit`

Expected: PASS.

- [ ] **Step 3: Check served CSS**

Run: `Invoke-WebRequest -Uri 'http://127.0.0.1:8001/themes/basic/assets/css/custom.css' -UseBasicParsing -TimeoutSec 10`

Expected: status `200` and content contains `Shared UI consistency polish`.
