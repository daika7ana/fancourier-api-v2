# AGENTS.md

PHP client library for the FAN Courier API v2.0 (JSON). Composer package
`daika7ana/fancourier-api`, namespace `Fancourier\`, PSR-4 root `src/Fancourier/`.
No framework, no CLI: it is a library plus runnable examples.

## Commands

```bash
composer install                                 # required before tests; composer.lock is committed
vendor/bin/phpunit --exclude-group integration   # hermetic unit suite
vendor/bin/phpunit --filter it_can_get_costs     # single test
vendor/bin/phpunit --group integration           # live API tests (needs env creds + token)
vendor/bin/phpstan analyse                       # static analysis, level 8
vendor/bin/rector process --dry-run              # consumer codemod canary
```

- There are **no composer scripts** (no `composer test`/`composer lint`).
- `pint.json` configures Laravel Pint (`per` preset + extra rules). Formatting has
  already been applied; run `vendor/bin/pint` to format and `vendor/bin/pint --test`
  to verify. CI enforces `pint --test` on PHP 8.3.
- Pint enables non-cosmetic rules (`protected_to_private`, `ordered_class_elements`,
  `array_push`), so a formatting pass can change visibility/member order — review the
  diff rather than blind-committing it.

## Tests hit the live API

`tests/FancourierTest.php` are **integration tests against `https://api.fancourier.ro/`**,
tagged `#[Group('integration')]` and excluded by default (see `phpunit.xml.dist`). Reading
the rest of `tests/` (unit tests with fixtures) is fully hermetic. To run the live suite,
set `FANCOURIER_LIVE_TOKEN` plus the account credentials `FANCOURIER_TEST_CLIENT_ID`,
`FANCOURIER_TEST_USERNAME` and `FANCOURIER_TEST_PASSWORD` (no hardcoded credentials). They
create/delete **real** AWBs and need network access plus a valid test account. Do not treat
a green live run as meaningful unit coverage, and avoid running the create/delete tests
repeatedly.

- `phpunit.xml.dist` bootstraps `vendor/autoload.php`; writing coverage to `build/` (gitignored).
- Tests use `/** @test */`/`#[Test]`, so `--filter <method_name>` works (no `test` prefix).
- Several live tests reference **hardcoded** AWB numbers (`2347300120337`, `2347300120340`)
  that belong to the shared test account; they fail if those AWBs don't exist there,
  independent of network.
- CI is **GitHub Actions** (`.github/workflows/ci.yml`): PHPUnit matrix on PHP 8.3/8.4/8.5,
  plus `composer validate --strict`, a syntax check, PHPStan and a Rector canary job on 8.3.
  There is no Travis config.

## Static analysis, Rector and Pint

- **PHPStan level 8** (`phpstan.neon`, `vendor/bin/phpstan analyse --no-progress`) with no
  baseline; keep it at `[OK] No errors`.
- Two Rector configs exist: `rector.php` is the consumer-facing rename codemod (old
  snake_case `Client` methods → camelCase), and `rector-internal.php` only adds
  `declare(strict_types=1)`. The CI canary runs `rector.php` over `canary/consumer-v1`.
- Pint (`per` preset) has been applied repo-wide and is enforced in CI (`pint --test` on
  PHP 8.3).

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
  `POSTPUT` / `POSTDELETE`, which route through `Client::postMultiArray()` — it hand-builds
  the query string because cURL mishandles nested arrays in `CURLOPT_POSTFIELDS`.
- `Client` (not the request) owns cURL, SSL verification, timeouts, and headers.
  `AbstractRequest` caches verify/timeout overrides in `$clientOverrides`.
- `Generic` provides `isOk()`, `getData()`, `getErrorCode()/getErrorMessage()`;
  subclasses add `getAll()`/typed getters. `ResponseInterface` is the shared contract.
- PHP floor is **8.3** (declared in `composer.json`), and `declare(strict_types=1)` is used
  repo-wide; fluent setters return `static`/`$this`, and enums accept both string and enum
  values via `string|Enum` setters.

## Reference material

- `examples/*.php` demonstrate every endpoint; `examples/README.md` indexes them.
  Each example header lists its endpoint and Request/Response classes. The shared
  bootstrap `examples/_init.php` requires `vendor/autoload.php` and caches the token in
  `examples/examples_token.txt` (gitignored). Credentials come from
  `FANCOURIER_TEST_CLIENT_ID`/`FANCOURIER_TEST_USERNAME`/`FANCOURIER_TEST_PASSWORD`.
- `docs/` holds the reference documentation (index: `docs/README.md`): endpoints,
  objects, enums, errors and getting-started.
- Auth: bearer token has a 24h lifetime; `getToken($refresh)` refreshes it. Examples
  cache it in `examples/examples_token.txt` (gitignored).
