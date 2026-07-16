# Laravel 12 Compatibility Check

Date: 2026-07-16

## Verified baseline

- Laravel framework: `v12.64.0`
- Carbon: `v3.13.1`
- PHP runtime observed by Artisan: `8.4.21`
- Branch: `upgrade/laravel-12-secure`

## Result

The requested Laravel 12 dependency resolution completed successfully. The upgrade keeps
`nunomaduro/collision` and removes `jenssegers/date` in favor of Laravel's native Carbon 3
integration.

Verified package targets:

- `laravel/framework ^12.64`
- `nunomaduro/collision ^8.9.5`
- `phpunit/phpunit ^11.5.50`
- `cviebrock/eloquent-sluggable ^12.0`
- `diglactic/laravel-breadcrumbs ^10.1`

Date compatibility tests cover parsing, timezone preservation, relative time output, English
weekday/month translations, and Amharic weekday/month translations. No `ago()` or `timespan()`
usage was present in the repository.

## Security note

`composer audit --locked` reports no advisories on the upgraded lockfile. The security
remediation test suite and the full application test suite pass on this branch.
