# Guzzle 7.15.1 Security Verification

## Scope

This change updates the HTTP client dependency without changing application payment,
OAuth, storage, captcha, or webhook behavior.

- `guzzlehttp/guzzle`: `7.14.2` to `7.15.1`
- `guzzlehttp/psr7`: `2.12.5` to `2.13.0`
- `guzzlehttp/promises`: unchanged at `2.5.1`
- Application and add-on production code: unchanged

The update was resolved with `composer update guzzlehttp/guzzle
--with-dependencies`. Composer reported no package installs or removals and no other
version changes.

## Advisory Review

The previous lockfile was affected by three medium-severity Guzzle advisories, all
fixed in `7.15.1`:

| Advisory | Package | Severity | Affected versions | Patched version | Affected behavior | Application exposure |
| --- | --- | --- | --- | --- | --- | --- |
| `PKSA-fy2t-3c5f-827y` / `GHSA-h95v-h523-3mw8` | `guzzlehttp/guzzle` | Medium | Below `7.15.1` | `7.15.1` | URI fragments could be disclosed in redirect Referer headers | Potential indirect exposure through default redirect handling. No application code explicitly sets Referer or `allow_redirects`. |
| `PKSA-qxvb-2bpp-dnk6` / `GHSA-wm3w-8rrp-j577` | `guzzlehttp/guzzle` | Medium | Below `7.15.1` | `7.15.1` | Host-only cookie scope could be lost | No application-level `CookieJar` usage was found. Exposure was limited to dependency or SDK internals. |
| `PKSA-bbs6-q5q9-f3t4` / `GHSA-f283-ghqc-fg79` | `guzzlehttp/guzzle` | Medium | Below `7.15.1` | `7.15.1` | Unbounded response cookies could consume excessive resources | No application-level cookie handling was found. Exposure was limited to dependency or SDK internals. |

`composer audit --locked` reports no remaining advisories or abandoned packages after
the update.

## Integration Review

The following HTTP consumers were reviewed:

- Chapa: direct Guzzle client with 15-second request timeout, 5-second connection
  timeout, TLS verification, bearer authentication, and JSON payloads.
- Paystack and Coinbase: direct Guzzle requests; no explicit cookies, Referer, or
  redirect policy.
- Stripe, Razorpay, Iyzico, Midtrans, Mollie, CoinGate, PayPal, and Xendit: vendor
  SDK integrations; no application integration changes required.
- Socialite and OAuth: transitive Guzzle consumers; no application integration
  changes required.
- S3-compatible storage: AWS SDK and Flysystem use Guzzle transitively; no storage
  configuration changes required.
- Captcha providers and PayPal IPN: Laravel HTTP client consumers; no application
  changes required.

Focused Chapa tests use Guzzle's mock handler and do not make network requests. They
verify timeout and TLS defaults plus the request method, URL, authorization header,
Accept header, and JSON body.

## Verification Controls

- The full test suite uses `APP_ENV=testing`, `DB_CONNECTION=mysql`, and
  `DB_DATABASE=digetdb_test`.
- Development database `digetdb` table and core record counts are checked before and
  after the suite.
- The pre-update suite passed 68 tests with 578 assertions. The post-update suite
  passed 70 tests with 589 assertions, including the focused HTTP compatibility tests.
- The development database remained at 82 tables, 7 users, 10 items, 32 settings,
  and 1 currency. Its username history table has not yet been migrated locally, so
  that count is not applicable to the development database baseline.
- Frontend build, Blade compilation, route discovery, Composer validation, Composer
  audit, and whitespace checks are included in the release verification.

## Verification Results

- `composer validate --strict --no-check-publish`: passed
- `composer install --dry-run --no-interaction`: passed; nothing to install, update,
  or remove
- `composer audit --locked --format=json`: passed; zero advisories and zero
  abandoned packages
- Focused Chapa tests: 8 passed with 17 assertions
- Full test suite: 70 passed with 589 assertions
- Production frontend build: passed with 112 transformed modules
- Blade view compilation: passed
- Route discovery: passed with 644 routes
- Development database before and after tests: unchanged at 82 tables, 7 users,
  10 items, 32 settings, and 1 currency

## Browser QA

The in-app browser backend was unavailable during this verification, so the required
interactive username lifecycle and eight-viewport matrix could not be completed.
This dependency update must remain blocked from final merge approval until that
manual browser QA is performed.
