# Workspace Mobile Sidebar Compact Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Compact the workspace sidebar drawer on mobile and tablet screens.

**Architecture:** Add one final CSS override block in `custom.css`, scoped to dashboard sidebar selectors and mobile media queries. Protect it with the existing PHPUnit CSS guard.

**Tech Stack:** Laravel, Blade, CSS, PHPUnit.

---

### Task 1: Add CSS Guard

**Files:**
- Modify: `tests/Unit/PublicMarketplaceCompactCssTest.php`

- [ ] Add snippets for `Workspace mobile sidebar compact polish`, `.dashboard-sidebar .dashboard-sidebar-body`, `.dashboard.toggle .dashboard-sidebar .dashboard-sidebar-body`, `width: 236px`, `width: 218px`, `.dashboard-sidebar-link .dashboard-sidebar-link-title`, and `.dashboard-balance`.
- [ ] Run `vendor\bin\phpunit tests\Unit\PublicMarketplaceCompactCssTest.php` and confirm it fails on the missing marker.

### Task 2: Add Mobile Sidebar CSS

**Files:**
- Modify: `public/themes/basic/assets/css/custom.css`

- [ ] Append `/* Workspace mobile sidebar compact polish */`.
- [ ] Add `max-width: 1199.98px` rules for 236px drawer body, 52px header/body top, compact balance card, compact links, and darker overlay.
- [ ] Add `max-width: 575.98px` rules for 218px drawer body and slightly smaller link text.
- [ ] Run the focused CSS guard and full PHPUnit.
