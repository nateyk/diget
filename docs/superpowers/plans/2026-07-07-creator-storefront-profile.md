# Creator Storefront Profile Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a creator storefront layout for `/user/{username}`.

**Architecture:** Reuse the existing public profile route and theme layout. Scope the new layout to `profile.index`, load approved items in the controller, and add final CSS overrides in `custom.css`.

**Tech Stack:** Laravel, Blade, CSS, PHPUnit.

---

### Task 1: Add Storefront Guard

**Files:**
- Create: `tests/Unit/CreatorStorefrontUiTest.php`

- [ ] Add assertions for `ProfileController@index` loading approved `Item` records.
- [ ] Add assertions for storefront Blade markers in `profile.index` and `profile.layout`.
- [ ] Add assertions for the `Creator storefront profile polish` CSS marker and major storefront selectors.
- [ ] Run `vendor\bin\phpunit tests\Unit\CreatorStorefrontUiTest.php` and confirm it fails before implementation.

### Task 2: Feed Storefront Items

**Files:**
- Modify: `app/Http/Controllers/ProfileController.php`

- [ ] In `index`, query approved items for the profile user.
- [ ] Eager-load `author` and `category`.
- [ ] Paginate 12 items and pass them as `items` to `profile.index`.

### Task 3: Build Storefront Markup

**Files:**
- Modify: `resources/views/themes/basic/profile/layout.blade.php`
- Modify: `resources/views/themes/basic/profile/index.blade.php`

- [ ] Add a `profile-storefront-page` body class for `profile.index`.
- [ ] Hide the old profile header only on `profile.index`.
- [ ] Render the profile index as a two-column `creator-storefront` layout.
- [ ] Add the left creator card and right storefront item grid.
- [ ] Keep contact and about content available lower on the storefront page.

### Task 4: Add Storefront Styling

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] Append `/* Creator storefront profile polish */`.
- [ ] Style the creator card, stats, social buttons, tabs, storefront grid, item cards, and responsive mobile stacking.

### Task 5: Verify

**Files:**
- Test: `tests/Unit/CreatorStorefrontUiTest.php`

- [ ] Run `vendor\bin\phpunit tests\Unit\CreatorStorefrontUiTest.php`.
- [ ] Run `vendor\bin\phpunit`.
- [ ] Run `php artisan optimize:clear`.
- [ ] Request `http://127.0.0.1:8000/user/zafalegese` and confirm status `200` with storefront markup.
