# Public Marketplace Wide Compact UI Design

## Goal

Extend the compact 100% zoom treatment from the homepage across public marketplace and public support pages, while keeping the logged-in workspace/dashboard unchanged.

## Scope

This pass covers public-facing theme pages under `resources/views/themes/basic` except `workspace`:

- Marketplace browse/search pages, category pages, filters, grid controls, pagination, empty states
- Item detail pages, preview cards, purchase/sidebar cards, tabs, reviews/comments/support/changelogs
- Cart and checkout public purchase flow
- Profile, portfolio, blog, help center, premium, auth, contact, and generic public page layouts
- Shared public card, form, table, button, tab, media, and author/comment components

Dashboard selectors remain out of scope.

## Approach

Continue using `public/themes/basic/assets/css/custom.css` because it is the loaded custom override layer after the compiled theme CSS. Add a second marker block for the wider public page compact pass.

The styling should reduce oversized public UI spacing and component heights by about 10-20% beyond the homepage pass, with page-specific selectors for cards, filters, item detail panels, cart rows, checkout/payment cards, profile/blog/help blocks, forms, and tables.

## Constraints

- Do not edit the compiled `app.css`.
- Do not target `.dashboard-` selectors.
- Keep mobile readable; compact mobile spacing only where components are clearly oversized.
- Keep visual behavior intact; no data or route changes.

## Testing

Extend the existing PHPUnit CSS guard to verify the wider public compact marker and selectors exist, and to keep the no-dashboard-selector guard. Run the full PHPUnit suite and clear Laravel caches after implementation.
