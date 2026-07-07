# Workspace Dashboard Compact UI Design

## Goal

Bring the logged-in workspace/dashboard UI to the same professional compact scale as the public marketplace, while preserving the dashboard's operational feel for tables, forms, uploads, and repeated workflows.

## Scope

This pass covers workspace pages under `resources/views/themes/basic/workspace`:

- Dashboard shell, sidebar, sidebar balance panel, top nav, page header, footer
- Dashboard counters, chart cards, list cards, empty cards, and dashboard item rows
- Workspace tables, pagination, tabs, modals, alerts, forms, buttons, select/input controls
- Item create/edit/statistics/history pages, file upload/dropzone, uploaded file rows
- Purchases, transactions, balance, withdrawals, refunds, tickets, settings, referrals, tools

Public marketplace rules remain in their existing public compact blocks.

## Approach

Append a new `/* Workspace dashboard compact pass */` block to `public/themes/basic/assets/css/custom.css`. This keeps the dashboard work separate from public page styling while using the same custom override layer.

The dashboard should be denser than marketing/public pages: reduce large shell dimensions, card padding, table row padding, upload row height, and control sizes by roughly 10-20%, keeping mobile tap targets usable.

## Constraints

- Do not edit compiled `app.css`.
- Do not change Blade markup or routes for this pass.
- Keep the existing public compact blocks free of `.dashboard-` selectors.
- Dashboard selectors are allowed only in the new workspace compact block.

## Testing

Update the CSS guard so it:

- Verifies public compact blocks still exist.
- Checks public compact content before the dashboard marker does not include `.dashboard-`.
- Verifies the dashboard compact marker and workspace selectors exist.

Then run the focused guard, brace balance check, Laravel cache clear, and full PHPUnit suite.
