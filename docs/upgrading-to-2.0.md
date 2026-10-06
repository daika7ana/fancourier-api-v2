# Upgrading to 2.0

2.0 is a **breaking major** release: the public API is re-typed, `Client` methods are
renamed to camelCase, dead methods are removed, and backed enums were added. The
authoritative, complete list lives in [`../MIGRATION.md`](../MIGRATION.md); this page is a
short orientation and a pointer.

## What changed at a glance

| Area | Change |
|---|---|
| Package name | `shusaura85/fancourier-api` → `daika7ana/fancourier-api` |
| PHP floor | `>= 7.0` → `^8.3` |
| Extensions | `ext-curl`, `ext-json` → `+ ext-fileinfo` |
| Namespace | `Fancourier\` (unchanged) |
| Typing | Native parameter/property/return types across the public API; `declare(strict_types=1)` repo-wide |
| `Client` methods | snake_case → camelCase (`set_verify()` → `setVerify()`, `post_json()` → `postJson()`, …) |
| Removed | `Client::get_error_no()`, `Client::headers_reset()`, `Auth::getClientUsername()`, `Auth::getClientPassword()`, `Fancourier::testInstance()` and `Fancourier::TEST_*` |
| Code lists | Added as backed enums in `Fancourier\Enums\` and constants on `AbstractRequest`; adopting the enums is optional |
| Credentials | Supplied by the caller or environment variables; the hardcoded test account is gone |

## Automated migration

The mechanical renames are encoded as a Rector set (`rector.php`). Run it before any manual
edits:

```bash
composer require --dev rector/rector
vendor/bin/rector process src tests --dry-run   # review
vendor/bin/rector process src tests             # apply
```

The Rector set cannot rewrite string literals or apply the behaviour fixes, so review the
remaining semantic changes in `MIGRATION.md` §4 by hand and run your test suite.

## Adopting enums (optional)

Setters that accept `string|Enum` keep working with plain strings. You can migrate
incrementally; see the [enums reference](enums.md) for the classes and values.

## Notes on types and `strict_types`

`strict_types` is declared **per calling file**. Declaring it in the library does not change
coercion of your call sites; the consumer-facing break is the newly **declared types**
themselves. Passing a non-coercible value where a parameter is now typed (or relying on a
now-typed return) throws a `TypeError` even in weak mode. If you subclass request/response
classes, update overrides to match the new signatures or PHP raises a fatal error.

## Where to go next

- [`../MIGRATION.md`](../MIGRATION.md) — full break list, symbol mappings, behaviour fixes,
  and the per-consumer checklist.
- [`../CHANGELOG.md`](../CHANGELOG.md) — the human-readable `[2.0.0]` entry.
- [Getting started](getting-started.md) — the 2.0 usage flow.
