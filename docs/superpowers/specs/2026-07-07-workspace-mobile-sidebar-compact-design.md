# Workspace Mobile Sidebar Compact Design

## Goal

Make the workspace sidebar drawer feel proportional on mobile and tablet screens.

## Scope

This is a CSS-only refinement for the basic theme workspace sidebar. Desktop sidebar sizing remains unchanged.

## Design

Add a final mobile-specific override layer for `max-width: 1199.98px`. The drawer body should be narrower than the full-screen overlay, the header should use the same compact height as the desktop dense layout, and the balance card plus navigation rows should use smaller padding and type. On narrow phones, the drawer should reduce again so it does not dominate the viewport.

## Testing

Extend the existing CSS guard with a marker and key snippets for the mobile sidebar polish. Run the guard in red state before adding CSS, then run the focused guard and full PHPUnit suite after implementation.
