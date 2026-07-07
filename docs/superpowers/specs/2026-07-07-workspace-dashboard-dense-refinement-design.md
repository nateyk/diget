# Workspace Dashboard Dense Refinement Design

## Goal

Make the workspace dashboard significantly more compact than the first pass, with special attention to settings tabs, forms, buttons, sidebar rows, cards, and page spacing shown in the user's screenshot.

## Scope

This refinement targets only logged-in workspace UI:

- Sidebar width, logo area, balance block, nav rows, icons, and text
- Top navigation height, menu button, author button, avatar, user menu text
- Page title and breadcrumb spacing
- Settings tab pills and other workspace tabs
- Dashboard cards, form sections, labels, inputs, selects, textareas, buttons
- Upload/image boxes, social buttons, modals, tables, alerts, and dense row gaps

Public marketplace blocks remain untouched.

## Approach

Append a new `/* Workspace dashboard dense refinement */` CSS block after the existing workspace compact pass in `public/themes/basic/assets/css/custom.css`.

The dense pass intentionally overrides earlier dashboard values with smaller heights and tighter padding. It keeps minimum interactive controls near usable tap/click sizes, but prioritizes a compact admin-tool feel on desktop.

## Testing

Extend the CSS PHPUnit guard to require the dense marker and critical selectors. Run the focused guard, CSS brace balance check, Laravel cache clear, and full PHPUnit suite.
