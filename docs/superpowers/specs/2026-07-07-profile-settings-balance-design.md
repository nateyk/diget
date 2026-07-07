# Profile And Settings Balance Design

## Goal

Reduce the oversized spacing on public user profile pages and compact the workspace settings link list so both areas feel balanced with the rest of the marketplace UI.

## Scope

This is a CSS-only refinement in the basic theme. It does not change Blade structure or admin UI.

## Design

Add a final scoped CSS layer for:

- Public profile header: shorter hero height, tighter profile row spacing, smaller avatar, compact action buttons, closer stats, and tabs that sit nearer to the profile content.
- Workspace settings tabs: smaller fixed-height links, more even wrapping, smaller icons, reduced gap, and clearer active/hover states without oversized blocks.

## Testing

Extend the existing CSS guard to check the new marker and key selectors. Run the guard in red state before adding CSS, then run focused and full PHPUnit after the CSS is added.
