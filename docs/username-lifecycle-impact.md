# Username Lifecycle Impact Brief

## Baseline

- Source branch: `refactor/font-awesome-icon-system`
- Source commit: `65af5bd`
- Feature branch: `feat/secure-username-change-policy`
- Working tree at branch creation: clean
- Application environment: local Laragon
- Development database: `digetdb`
- PHPUnit database: `digetdb_test` from `phpunit.xml`
- Frontend build and Blade compilation: passing before feature work
- Full PHP suite: must be rerun because the captured baseline process was interrupted before its final summary was retained

## Current Identity Contract

- `users.username` is `varchar(50)` with the `users_username_unique` index.
- The MySQL database and table use `utf8mb4_unicode_ci`, so the unique index is case-insensitive.
- Existing creator usernames are stored without the public `@` prefix.
- Public profile routes are `/@{username}` plus portfolio, followers, following, reviews, and send-mail suffixes.
- Legacy `/user/{username}` GET routes permanently redirect to the current `/@username` route.
- Registration, OAuth completion, and admin user create/update currently repeat validation rules instead of using one domain policy.
- Current accepted format is Laravel `alpha_dash`, with a six-character minimum and fifty-character maximum for users.
- The existing `Username` rule only checks a hard-coded exact reserved-name list after lowercasing; it does not normalize input, check application settings, history, or availability.
- New registrations and updates do not currently force canonical lowercase storage.
- No user-facing username-change workflow or username history exists.

## Impact Matrix

### Registration

- Current behavior: validates `username`, `alpha_dash`, block patterns, and direct database uniqueness, then stores the raw request value.
- Risk: leading `@` cannot be accepted consistently, mixed-case storage is possible, and blacklist/history checks can diverge from other writers.
- Required change: normalize before validation and use the centralized username rule while retaining the database unique constraint.
- Files involved: `RegisterController`, registration tests, username rule/policy.
- Migration required: no registration-specific migration.
- Compatibility concern: preserve `alpha_dash`, minimum 6, maximum 50.
- Tests required: normalization, duplicate case variants, reserved, blacklisted, historical, and valid legacy format.

### OAuth Completion And Creator Onboarding

- Current behavior: OAuth completion repeats registration rules and stores the raw username.
- Risk: policy bypass through a second account-completion path.
- Required change: use the same normalization and policy as registration.
- Files involved: `OAuthController`, OAuth tests.
- Migration required: none beyond history/settings infrastructure.
- Compatibility concern: partially completed OAuth accounts may have nullable passwords and need a username without password confirmation during initial completion.
- Tests required: normalized success and every protected-name failure class.

### Account Settings

- Current behavior: username is displayed disabled on the account form; no change action exists. Password changes require the current password, and workspace routes already enforce authentication, verified email, account status, OAuth completion, and 2FA middleware.
- Risk: adding a plain mass-assignment field would bypass cooldown, history, locking, and confirmation.
- Required change: add a separate sensitive username form/action with visible `@`, current URL, warning, eligibility date, current-password confirmation for password accounts, and the transactional action.
- Files involved: workspace settings controller, route, Blade view, policy/action, tests.
- Migration required: username history.
- Compatibility concern: OAuth-only accounts do not have a usable password confirmation and must rely on the existing authenticated/verified/2FA boundary.
- Tests required: authorization, password confirmation, no-op, cooldown state, accessible errors, and mobile-safe markup.

### Admin User Management

- Current behavior: admin create/update repeats username validation and updates the model directly.
- Risk: admins can bypass normalization, configured blacklist, historical reservations, and redirects.
- Required change: central policy for create; transactional change action for update with an audited admin source and cooldown bypass only.
- Files involved: admin user controller, admin tests.
- Migration required: username history.
- Compatibility concern: reviewer/admin guard usernames are separate identity tables and are not public creator storefront identities; they remain outside this user policy.
- Tests required: blacklist/history enforcement and audited cooldown bypass.

### Public Storefront Routing

- Current behavior: current username is queried directly; lowercase middleware handles route case; historical usernames return 404.
- Risk: changing a username breaks external links, while careless redirects could reveal banned/incomplete accounts.
- Required change: resolve current visible users first, then historical names; redirect historical URLs directly to the latest current route with 301; preserve supported suffix and query parameters; apply the same visibility rules before redirecting.
- Files involved: profile controller, routes/resolver, redirect tests.
- Migration required: username history with a unique historical key.
- Compatibility concern: current profile query requires completed data but does not explicitly apply active status; feature implementation must preserve or strengthen the existing status middleware semantics without exposing hidden users.
- Tests required: current, historical, multiple-history direct redirect, unknown, query preservation, and inactive/incomplete non-disclosure.

### SEO And Sharing

- Current behavior: profile canonical and Open Graph URL use `getProfileLink()`; sharing and internal creator links call current model helpers; sitemap is crawler-based.
- Risk: stale canonical URLs or crawling historical/legacy aliases can create duplicate identities.
- Required change: keep current model helpers authoritative, emit current canonical/OG/structured data, prevent historical aliases from being emitted, and reject legacy/history aliases from sitemap output.
- Files involved: profile view/head, schema data, sitemap command, tests.
- Migration required: none beyond history.
- Compatibility concern: external search engines need time to process 301 redirects.
- Tests required: canonical, OG, structured data, share/product links, and sitemap exclusion.

### Social And Internal Links

- Current behavior: user menu, product cards, creator cards, dashboard sharing, and storefront actions dynamically call current profile helpers.
- Risk: any hard-coded username URL can remain stale.
- Required change: keep all generated links model/route based; no historical URL should be rendered.
- Files involved: existing Blade partials and regression tests.
- Migration required: none.
- Compatibility concern: queued notification payloads contain display usernames captured when dispatched; they are not route identities and should not be rewritten.
- Tests required: current links after a change.

### Referrals

- Current behavior: referral cookies contain a username and registration resolves it by direct current-username lookup.
- Risk: a username change can invalidate an existing referral cookie.
- Required change: resolve a historical username back to its owning current user without permitting reassignment.
- Files involved: referral middleware/listener or shared resolver, tests.
- Migration required: username history.
- Compatibility concern: existing cookies remain strings and require no format migration.
- Tests required: current and historical referral attribution.

### Caching

- Current behavior: no username-keyed cache was found. The featured-author cache is invalidated for avatar, cover, and featured status only.
- Risk: cached featured markup could retain an old profile URL after a username change.
- Required change: invalidate the featured-author cache after a successful username change and leave unrelated caches intact.
- Files involved: post-commit listener or user model cache invalidation.
- Migration required: none.
- Compatibility concern: avoid global cache flushes.
- Tests required: event/listener behavior where practical.

### Files And Storage

- Current behavior: user assets are stored under ID-derived hashed directories; demo assets may use username-readable filenames.
- Risk: moving runtime files by username would be destructive and unnecessary.
- Required change: none for runtime storage; do not rename files.
- Files involved: none for production storage.
- Migration required: none.
- Compatibility concern: demo filenames are presentation fixtures, not identity lookup keys.
- Tests required: existing avatar/storefront regression tests.

### API And Livewire

- Current behavior: `AccountResource` exposes the current username. No username-writing API or Livewire component was found. Livewire follow/favorite components consume user IDs/models.
- Risk: future endpoints could bypass the policy if they write username directly.
- Required change: keep API output current; central action/rule becomes the required write boundary.
- Files involved: account resource regression test; no current Livewire writer.
- Migration required: none beyond history.
- Compatibility concern: response shape must not change.
- Tests required: current username API output after change.

### Blacklist Settings

- Current behavior: settings are JSON objects keyed by rows; profile settings currently contain only default avatar/cover. `updateSettings` only updates pre-existing object properties. Duplicate legacy profile-setting rows exist in the local data set.
- Risk: storing the blacklist in an absent JSON property silently fails; naive comparisons permit case/`@` bypasses.
- Required change: add a dedicated `username` settings row/property through a safe migration, expose it on the existing profile settings screen, normalize comma-separated values in the policy, and use exact set membership.
- Files involved: migration, settings controller/view, policy, parser tests.
- Migration required: yes, idempotent settings seed plus history table.
- Compatibility concern: do not rename or block existing users when an administrator later adds their current name.
- Tests required: null, empty, spaces, duplicates, case variants, optional `@`, and exact-not-substring behavior.

### System Reservations

- Current behavior: a hard-coded reserved list exists in the rule.
- Risk: deleting it would reopen route, brand, support, and impersonation names.
- Required change: move the compatible list to configuration, add only names justified by current routes/brand/security identities, and keep it separate from administrator policy values.
- Files involved: username configuration and policy.
- Migration required: none.
- Compatibility concern: do not broaden format rules or destructively normalize existing accounts.
- Tests required: route/brand/support identities and benign prefix names such as `admincreative`.

### Username History And Audit

- Current behavior: no activity package or username audit table exists.
- Risk: old links break, names can be recycled for impersonation, and successful changes are not attributable.
- Required change: add username history storing user, old/new canonical names, actor identity, source, and successful change timestamp; use it as cooldown authority and redirect reservation.
- Files involved: migration, model, relationships, action, tests.
- Migration required: yes.
- Compatibility concern: do not collect IP, user agent, tokens, or full request payloads.
- Tests required: history, actor/source, reservation, and rollback.

### Database Uniqueness And Concurrency

- Current behavior: case-insensitive unique index protects only current usernames; no transaction or row lock surrounds updates.
- Risk: simultaneous requests can pass application checks and race, or the same user can bypass cooldown with parallel submissions.
- Required change: retain the unique index, lock the user row, re-evaluate policy/cooldown inside a transaction, make historical old names unique, and convert duplicate-key failures to validation errors.
- Files involved: action, migrations, policy, tests.
- Migration required: history indexes.
- Compatibility concern: MySQL is the production/test engine; concurrency tests must not use the development database.
- Tests required: duplicate constraint authority, same-user serialized update, rollback, and race-oriented coverage supported by the test environment.

### Events And Notifications

- Current behavior: no username event exists. Jobs capture username strings for notification copy.
- Risk: dispatching before commit could expose rolled-back identity; listener failure could interrupt persistence.
- Required change: persist synchronously and dispatch `UsernameChanged` after commit for targeted cache invalidation and optional non-critical notification.
- Files involved: event/listener/action/tests.
- Migration required: none.
- Compatibility concern: do not rewrite old queued notification payloads.
- Tests required: event dispatch only after success, none after rejection/rollback.

### Demo Data And Existing Tests

- Current behavior: demo seeders create known lowercase usernames; UI tests inspect storefront markup; PHPUnit is configured for `digetdb_test`.
- Risk: policy validation in factories/seeders could make deterministic fixtures fail or tests could accidentally target development data if configuration drifts.
- Required change: preserve valid fixtures, add explicit username tests, and verify database isolation before and after the suite.
- Files involved: tests and only seeders that directly exercise account creation.
- Migration required: test database migrations only.
- Compatibility concern: never reset `digetdb`.
- Tests required: full authentication, storefront, payment, refund, withdrawal, download, and security regression suite.

## Chosen Domain Design

- `UsernamePolicy` is the single normalization, format, reservation, availability, blacklist, and cooldown authority.
- `Username` remains the Laravel validation-rule adapter so existing `username` validation aliases can continue to work.
- `ChangeUsername` is the only post-registration mutation path.
- `username_histories` is authoritative for cooldown and historical redirects.
- Current usernames remain protected by `users_username_unique`; historical names are uniquely reserved.
- Stored form: lowercase, `alpha_dash`-compatible Unicode, no leading `@`.
- Displayed form: `@username`.
- User cooldown: rolling 30 days from the latest successful history record.
- Admin correction: may bypass cooldown only; all other policy/history rules still apply and actor/source are recorded.
- Historical redirects: one-hop 301 to the latest current route after current visibility checks.
- Non-critical side effects: after-commit event/listener.
