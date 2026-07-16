# Laravel 12 Compatibility Check

Date: 2026-07-16

## Current baseline

- Laravel framework: `v11.54.0`
- PHP: `8.3.28`
- Branch: `security/verified-remediation`

## Result

Laravel 12 was not upgraded in this remediation. Composer reports these compatibility constraints:

- `laravel/laravel` requires `laravel/framework ^11.0`.
- `nunomaduro/collision v8.5.0` conflicts with Laravel 12 or newer.
- `cviebrock/eloquent-sluggable 11.0.1` requires Illuminate 11 packages.
- `diglactic/laravel-breadcrumbs v9.0.0` supports through Laravel 11.
- Laravel 12 also requires dependency changes including `brick/math` and `nesbot/carbon` ranges that are not declared by the current application root.

The current application test suite passes on Laravel 11.54.0. A Laravel 12 upgrade needs a dedicated dependency and application compatibility cycle; it is intentionally out of scope for this security remediation.

## Security note

`composer audit --locked` reports three Laravel advisories affecting the current 11.x line, including one high-severity CRLF injection advisory and one medium signed-URL path-confusion advisory. They remain release risks until a patched compatible framework release is available or the application is upgraded through a reviewed Laravel 12 compatibility cycle.
