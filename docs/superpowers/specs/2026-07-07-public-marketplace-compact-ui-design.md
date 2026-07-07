# Public Marketplace Compact UI Design

## Goal

Make the public marketplace homepage feel correctly sized at 100% browser zoom, close to the current visual density the user sees at 80% zoom, without changing the logged-in workspace/dashboard.

## Scope

This pass covers public theme surfaces loaded through `resources/views/themes/basic/layouts/app.blade.php`, especially the homepage:

- Primary and secondary public navigation
- Homepage hero and search form
- Public section spacing and headings
- Homepage category cards
- Latest item cards and category tabs
- FAQ accordion rows
- Testimonial cards
- Public footer spacing

Workspace/dashboard UI is intentionally out of scope for this pass.

## Approach

Use `public/themes/basic/assets/css/custom.css` as the override layer because it is loaded after `app.css` through `@themeCustomStyle`. This avoids editing the compiled theme stylesheet and keeps the change easy to find or roll back.

The compact scale should reduce desktop vertical height, card padding, large text, and control sizes by roughly 15-25%. Mobile should remain readable and not become cramped, so the strongest reductions apply at desktop breakpoints.

## Testing

Add a lightweight PHPUnit regression test that verifies:

- The compact CSS marker exists.
- The override touches the intended public selectors.
- The override remains out of the dashboard namespace.

Manual browser review should check the homepage at 100% zoom on desktop and a narrow mobile width.
