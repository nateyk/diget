# Public Marketplace Compact UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the public marketplace homepage feel correctly sized at 100% browser zoom while leaving the workspace/dashboard untouched.

**Architecture:** Add scoped CSS overrides to the existing theme custom stylesheet, which loads after the compiled theme CSS. Add a small PHPUnit guard that checks the compact public marketplace CSS exists and does not target dashboard selectors.

**Tech Stack:** Laravel 10, Blade, Bootstrap 4/5-style theme CSS, PHPUnit.

---

### Task 1: Add CSS Regression Guard

**Files:**
- Create: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/PublicMarketplaceCompactCssTest.php`:

```php
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

        $this->assertStringNotContainsString('.dashboard-', $css);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: FAIL because `custom.css` does not yet contain the compact marker or required selectors.

### Task 2: Add Public Compact CSS Overrides

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Implement the compact public marketplace CSS**

Append the public compact override block to `custom.css`. It must include the marker `Public marketplace compact scale`, target only public selectors, and avoid `.dashboard-` selectors.

- [ ] **Step 2: Run the focused PHPUnit guard**

Run: `vendor/bin/phpunit tests/Unit/PublicMarketplaceCompactCssTest.php`

Expected: PASS.

### Task 3: Verify Styling Assets

**Files:**
- Read: `public/themes/basic/assets/css/custom.css`

- [ ] **Step 1: Check CSS syntax shape**

Run: `php -r "$css=file_get_contents('public/themes/basic/assets/css/custom.css'); echo substr_count($css,'{') === substr_count($css,'}') ? 'balanced' : 'unbalanced';"`

Expected: `balanced`

- [ ] **Step 2: Clear Laravel view/cache artifacts**

Run: `php artisan optimize:clear`

Expected: Laravel reports cached files cleared without an error exit.
