# Icon System Inventory

## Scope

This inventory was captured on `refactor/font-awesome-icon-system` from
`6346e5a` before the migration work began.

## Current Icon Sources

| Source | Status | Notes |
| --- | --- | --- |
| Font Awesome Free 6.4.2 | Retained | Loaded locally from `public/vendor/libs/fontawesome/fontawesome.min.css` in public, admin, reviewer, API-docs, error, and maintenance layouts. |
| Bootstrap Icons 1.13.1 | Remove | Imported from `resources/css/app.css` and installed through npm. |
| Inline SVG | Retained | Used for image assets and native control backgrounds, not as a competing application icon set. |

## Baseline Counts

- Bootstrap Icon class usages: 24 direct template usages plus 18 dynamic social-platform mappings.
- Bootstrap Icons package/import references: 7.
- Legacy Font Awesome class pairs (`fa`, `fas`, `far`, `fab`): 460 usages in 189 source files.
- Modern Font Awesome class pairs (`fa-solid`, `fa-regular`, `fa-brands`): 616 usages.

## Bootstrap Icons To Font Awesome Map

| Bootstrap icon | Font Awesome 6 class | Context |
| --- | --- | --- |
| `bi-globe2` | `fa-solid fa-globe` | Website profile link |
| `bi-facebook` | `fa-brands fa-facebook-f` | Social links and sharing |
| `bi-twitter-x` | `fa-brands fa-x-twitter` | X social links and sharing |
| `bi-instagram` | `fa-brands fa-instagram` | Social links |
| `bi-youtube` | `fa-brands fa-youtube` | Social links |
| `bi-tiktok` | `fa-brands fa-tiktok` | Social links |
| `bi-linkedin` | `fa-brands fa-linkedin-in` | Social links and sharing |
| `bi-pinterest` | `fa-brands fa-pinterest-p` | Social links and sharing |
| `bi-telegram` | `fa-brands fa-telegram` | Social links |
| `bi-whatsapp` | `fa-brands fa-whatsapp` | Social links and sharing |
| `bi-github` | `fa-brands fa-github` | Social links |
| `bi-discord` | `fa-brands fa-discord` | Social links |
| `bi-behance` | `fa-brands fa-behance` | Social links |
| `bi-dribbble` | `fa-brands fa-dribbble` | Social links |
| `bi-twitch` | `fa-brands fa-twitch` | Social links |
| `bi-spotify` | `fa-brands fa-spotify` | Social links |
| `bi-threads` | `fa-brands fa-threads` | Social links |
| `bi-reddit` | `fa-brands fa-reddit` | Social links |
| `bi-share` | `fa-solid fa-share-nodes` | Storefront/share actions |
| `bi-chat-left-text` | `fa-regular fa-message` | Creator contact action |
| `bi-person-plus-fill` | `fa-solid fa-user-plus` | Follow action |
| `bi-person-check-fill` | `fa-solid fa-user-check` | Following action |

## Compatibility Rules

- Every application icon uses a Font Awesome 6 style prefix: `fa-solid`,
  `fa-regular`, or `fa-brands`.
- Legacy prefixes are mechanically normalized: `fa` and `fas` become
  `fa-solid`, `far` becomes `fa-regular`, and `fab` becomes `fa-brands`.
- The local Font Awesome bundle includes its v4 compatibility aliases, so
  existing icon names remain supported while the style syntax is modernized.
- The migration does not alter routes, requests, authorization, payments, or
  database schema.
