# Profile And Settings Balance Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Compact the public profile header and workspace settings tab list.

**Architecture:** Add a small final CSS override block in `custom.css` and protect it with the existing PHPUnit CSS guard.

**Tech Stack:** Laravel, Blade, CSS, PHPUnit.

---

### Task 1: Add Guard

**Files:**
- Modify: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] Add required snippets for `Profile and settings balance polish`, `.header-profile .header-inner`, `.header-profile .user-avatar.user-avatar-xl`, `.header-profile .tabs-custom.v2`, `.dashboard .settings-links`, `.dashboard .settings-links-inner`, and `.dashboard .settings-link`.
- [ ] Run `vendor\bin\phpunit tests\Unit\PublicMarketplaceCompactCssTest.php` and confirm it fails on the missing marker.

### Task 2: Add CSS

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] Append `/* Profile and settings balance polish */`.
- [ ] Add profile-specific public rules for compact hero height, profile row gap, avatar, buttons, stats, and tabs.
- [ ] Add dashboard-scoped settings rules for compact tabs.
- [ ] Run the focused CSS guard and full PHPUnit.
