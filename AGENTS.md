# AGENTS.md

PHP client library for the FAN Courier API v2.0 (JSON). Composer package
`shusaura85/fancourier-api`, namespace `Fancourier\`, PSR-4 root `src/Fancourier/`.
No framework, no CLI: it is a library plus runnable examples.

## Commands

```bash
composer install          # required before tests (no vendor/ committed, no lock file)
vendor/bin/phpunit        # runs the whole suite
vendor/bin/phpunit --filter it_can_get_costs   # single test
pint                      # formatter (see note below)
```

- There are **no composer scripts** (no `composer test`/`composer lint`).
- `pint.json` configures Laravel Pint (`per` preset + extra rules), but Pint is
  **not** a dev dependency and `pint.json` is **untracked**. Pint is installed
  globally here, so run `pint`, not `vendor/bin/pint`.

## Tests hit the live API

`tests/FancourierTest.php` are **integration tests against `https://api.fancourier.ro/`**,
using the hardcoded test account in `Fancourier::testInstance()` (constants in
`src/Fancourier/Fancourier.php`). They create/delete **real** AWBs and need network
access plus a valid test account. Do not treat a green run as meaningful unit
coverage, and avoid running the create/delete tests repeatedly.

- `phpunit.xml.dist` bootstraps `vendor/autoload.php`; writing coverage to `build/` (gitignored).
- Tests use `/** @test */` annotations, so `--filter <method_name>` works (no `test` prefix).
- Several tests reference **hardcoded** AWB numbers (`2347300120337`, `2347300120340`) that
  belong to the shared test account; they fail if those AWBs don't exist there, independent
  of network. One test is commented out and references a nonexistent `TrackAwbBulk`/`getBody()`.
- CI is legacy `.travis.yml` only (PHP 8.1, `composer update` then `vendor/bin/phpunit`).
  No GitHub Actions.

## Request/Response architecture (where to change what)

Execution flow: `Fancourier` facade method → `send()` → request object →
`Client` (cURL) → response object. To add or extend an endpoint you usually touch:

1. `src/Fancourier/Request/<Name>.php` — extends `AbstractRequest`; sets
   `$gateway` (API path) and `$method`, implements `pack()` returning the payload array.
2. `src/Fancourier/Response/<Name>.php` — extends `Generic`; parse the raw JSON
   string in `setData()`, add typed getters.
3. `src/Fancourier/Objects/<Name>.php` — mutable fluent data holder (only if the
   endpoint needs one).
4. A facade method in `src/Fancourier/Fancourier.php`.
5. An example in `examples/` (see below).

Non-obvious details:

- `$method` accepts non-standard values: `GET`, `POST`, `PUT`, `DELETE`, and
  `POSTPUT` / `POSTDELETE`, which route through `Client::post_ma()` — it hand-builds
  the query string because cURL mishandles nested arrays in `CURLOPT_POSTFIELDS`.
- `Client` (not the request) owns cURL, SSL verification, timeouts, and headers.
  `AbstractRequest` caches verify/timeout overrides in `$clientOverrides`.
- `Generic` provides `isOk()`, `getData()`, `getErrorCode()/getErrorMessage()`;
  subclasses add `getAll()`/typed getters. `ResponseInterface` is the shared contract.
- Fluent setters return `$this`; there is no typed-property style yet (composer
  requires PHP >= 7.0, though README notes a future move to 8.1).

## Reference material

- `examples/*.php` are the **de-facto API docs** — the README calls docs incomplete.
  Each example lists the available request/response methods in comments. Use
  `examples/_init.php` for the shared bootstrap (manual autoloader + cached token).
- Run examples **from the `examples/` directory** (`cd examples && php getCosts.php`):
  `_init.php` uses CWD-relative paths (`../src/autoload.php`, `./examples_token.txt`),
  so invoking `php examples/getCosts.php` from the repo root fails.
- `docs/Classes overview.md` maps FAN Courier doc sections to PHP Request/Response/Object classes.
- Auth: bearer token has a 24h lifetime; `getToken($refresh)` refreshes it. Examples
  cache it in `examples/examples_token.txt` (gitignored).
- Non-Composer usage loads `src/autoload.php` (a hand-rolled autoloader), not vendor.
