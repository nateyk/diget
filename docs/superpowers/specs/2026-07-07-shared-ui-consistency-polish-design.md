# Shared UI Consistency Polish Design

## Goal

Make the public marketplace pages and user dashboard feel like one compact product surface at normal browser zoom.

## Scope

This pass focuses on the existing basic theme CSS. It keeps Blade structure intact and adds a final shared polish layer that normalizes common UI pieces across homepage, public marketplace pages, auth/cart/checkout/content pages, footer, and dashboard.

## Design

The polish layer should use the existing class system instead of adding new templates or components. Shared classes such as `.card-v`, `.btn-md`, `.form-control-md`, `.section`, `.header`, `.footer`, `.nav-bar`, `.item`, `.blog-post`, `.support-article-link`, and dashboard-scoped classes will receive consistent sizing, border radius, spacing, and typography.

Public UI changes must remain before or outside dashboard-specific scoping when they target public pages. Dashboard changes must remain under `.dashboard` or dashboard-specific selectors so admin UI is not affected.

## Visual Rules

- Cards use a restrained 7-8px radius and tighter padding.
- Buttons use compact heights and consistent icon spacing.
- Headers and sections use shorter vertical rhythm.
- Forms and tables use smaller text, balanced line height, and stable input heights.
- Footer columns, counters, logo, social buttons, and payment strip use the same compact scale as the rest of the site.
- Mobile keeps readable touch targets while reducing oversized gaps.

## Testing

Extend `tests/Unit/PublicMarketplaceCompactCssTest.php` with required snippets for the final polish marker and key selectors. Run the focused PHPUnit test first and confirm it fails before adding CSS. After implementation, run the focused test and full PHPUnit suite.
