# Upgrading from 1.x to 2.0

This guide is for **applications that use the `fancourier-api` Composer package** and are moving
from version 1.x to 2.0.

Version 2.0 is a breaking major release: the package was renamed, the PHP floor was raised, the
`Client` API was switched from `snake_case` to `camelCase`, the public API gained type
declarations, a few dead methods were removed, and several long-standing bugs were fixed.

Most of the work is automated — one command renames the mechanical call sites — followed by a
short manual list. Work through the steps below in order.

> New to the library? Read [`../README.md`](../README.md) and the [`docs/`](README.md) reference
> instead. This document only covers what changed between 1.x and 2.0.

## What changed at a glance

| Area | 1.x | 2.0 |
|---|---|---|
| Package name | `shusaura85/fancourier-api` | `daika7ana/fancourier-api` |
| PHP version | 7.0+ | 8.3+ |
| Required extensions | `ext-curl`, `ext-json` | `ext-curl`, `ext-json`, **`ext-fileinfo`** |
| Namespace | `Fancourier\` | `Fancourier\` (unchanged) |
| `Client` methods | `snake_case` (`set_verify()`) | `camelCase` (`setVerify()`) |
| Public API | mostly untyped | parameter, property and return types added |
| Code lists | plain strings | optional string-backed enums (strings still work) |
| Removed | — | dead methods and hardcoded test credentials dropped |
| Bugs | several known defects | corrected (see [§C](#c-behaviour-fixes) — verify against your usage) |

## TL;DR

```bash
# 1. Update composer.json: package name, PHP floor and ext-fileinfo (see Step 1).
# 2. Apply the automated renames with Rector (see Step 2).
composer require --dev rector/rector
vendor/bin/rector process src tests --dry-run   # review the diff
vendor/bin/rector process src tests             # apply

# 3. Work through the manual changes (Steps 3-5), then run your test suite (Step 7).
```

---

## Upgrade steps

### Step 1 — Update your `composer.json`

Replace the requirement entry with the new package name, and make sure your runtime satisfies the
new floor (PHP 8.3+) and has `ext-fileinfo` enabled:

```json
"require": {
    "daika7ana/fancourier-api": "^2.0"
}
```

Then update the lockfile:

```bash
composer update daika7ana/fancourier-api
```

The PHP namespace is **unchanged** (`Fancourier\`), so `use` statements and class references do
not need editing for the rename alone.

### Step 2 — Apply the automated renames (Rector)

The `Client` `snake_case` → `camelCase` renames are encoded as a Rector set, so this part is a
command rather than hand-editing. Take the `rector.php` file from the package repository (repo
root), point its paths at your source, and run:

```bash
composer require --dev rector/rector
vendor/bin/rector process src tests --dry-run   # review
vendor/bin/rector process src tests             # apply
```

The set is **rename-only**: it changes no signatures, types or behaviour, and it only knows the
mappings in [§A](#a-renamed-client-methods). It cannot fix the parts in Steps 3-5 — those still
need your attention.

### Step 3 — Replace removed methods

The methods in [§B](#b-removed-methods) no longer exist. A Rector rename cannot cover them because
there is no 1:1 replacement — delete or rewrite each call site:

- `Client::get_error_no()`, `Client::headers_reset()`, `Auth::getClientUsername()`,
  `Auth::getClientPassword()` — dead code; delete the call sites.
- `Fancourier::testInstance()` and the `Fancourier::TEST_CLIENT_ID` / `TEST_USERNAME` /
  `TEST_PASSWORD` constants — these shipped hardcoded test credentials in the production path.
  Construct the client with your own credentials instead:

```php
// 1.x
$fan = Fancourier::testInstance();

// 2.0
$fan = new Fancourier($clientId, $username, $password);
```

Supply `$clientId`, `$username` and `$password` from your own configuration or environment
variables.

### Step 4 — Update typed signatures and subclass overrides

2.0 adds parameter, property and return types across the public API. **The consumer-facing break is
the declared types themselves:** passing a non-coercible value where a parameter is now typed — or
depending on a now-typed return — throws a `TypeError` even in weak mode. Fix the call site; do not
cast blindly.

If you **subclass** a library class and override a method whose signature changed, update your
override to match, or PHP raises a fatal error.

> `declare(strict_types=1)` is applied per **calling file** in your application, not by the
> library. Declaring it in the library only affects coercion of calls made *inside* the library,
> so you do not need to add it to your own files to use 2.0.

### Step 5 — Review the behaviour fixes

[§C](#c-behaviour-fixes) lists the bug fixes shipped in 2.0. These are **not renames** — a codemod
cannot detect them. Each one may change the result your code gets back, so read the list and check
it against how you use the affected endpoint or object. The return-shape fixes in particular can
turn a previously silent wrong value into a correct one (or, if you relied on the old shape, a
visible change).

### Step 6 — (Optional) adopt enums

2.0 ships each code list as a string-backed enum in `Fancourier\Enums\`. Setters accept
`string|BackedEnum`, so **your existing string values keep working** and you can adopt enums
incrementally — or not at all. Rector cannot rewrite string arguments, so this step is manual. See
[§D](#d-enums-and-code-lists) for the code list ↔ enum mapping.

### Step 7 — Run your tests

Run your application's full test suite and fix every failure — do not suppress them. If your suite
does not cover the affected endpoints, exercise them manually before deploying.

---

## Reference

### A. Renamed `Client` methods

These are the deterministic renames applied by the Rector set in Step 2.

| Old (1.x) | New (2.0) |
|---|---|
| `set_verify()` | `setVerify()` |
| `set_timeout()` | `setTimeout()` |
| `set_put_request()` | `setPutRequest()` |
| `set_delete_request()` | `setDeleteRequest()` |
| `post_json()` | `postJson()` |
| `post_ma()` | `postMultiArray()` |
| `headers_add()` | `addHeader()` |
| `headers_delete()` | `deleteHeader()` |
| `get_error()` | `getError()` |

`prepare_file()` and `prepare_file_string()` keep their names.

Any method not listed here is **unchanged** — do not rename it.

### B. Removed methods

| Removed in 2.0 | Replacement |
|---|---|
| `Auth::getClientUsername()` | none — unused; delete call sites |
| `Auth::getClientPassword()` | none — unused; delete call sites |
| `Client::get_error_no()` | none — dead; delete call sites |
| `Client::headers_reset()` | none — dead; delete call sites |
| `Fancourier::testInstance()` | none — construct `new Fancourier($clientId, $username, $password, $token)` with your own credentials |
| `Fancourier::TEST_CLIENT_ID` / `TEST_USERNAME` / `TEST_PASSWORD` | none — supply credentials explicitly or via environment variables |

### C. Behaviour fixes

These are deliberate corrections, not renames. Review each against your usage.

| # | Symbol / endpoint | 1.x behaviour | 2.0 behaviour |
|---|---|---|---|
| B1 | `Request\PaginationTrait::getPerPage()` (shared by `GetCitiesExternal`, `GetCourierOrders`, `GetShippingSlip`, `GetBankTransfers`, `GetStreets`) | returned `$this->page` | returns `$this->perPage` |
| B2 | `Request\PrintAwb::getSize()` | returned `$this->lang` | returns `$this->size` |
| B3 | `Request\CreateCourierOrder::getPickupDate()` | returned `$this->notes` | returns `$this->pickupDate` |
| B4 | `Request\CreateCourierOrder::getAwb($awbNo)` | required an unused parameter | `getAwb(): string` — parameter removed |
| B5 | `Objects\AwbIntern::getIsValueUnderThreshold()` | inverted guard → threw when the value *was* set | returns the stored value |
| B6 | `Objects\CourierOrder` dimension getters | returned `[]` (→ `TypeError`) | return `0.0` |
| B7 | `Response\GetBranches::get()` | returned `false` (→ `TypeError`) | returns `?Branch` — **null** on miss (not `[]`) |
| B8 | `Response\GetAwbConfirmations::getRAWbytes()` / `getLength()` | wrong return shapes | `getRAWbytes(): ?string`, `getLength(): int` |
| B9 | `Objects\Pudo::getAddress()` | fallback `''` (→ `TypeError`) | fallback `[]` |
| B10 | `Client::postJson()` | closed the handle before reading the error | error is read before close |
| B11 | `AbstractRequest::send()` | undefined `$responseString` for an unknown method | throws `DomainException` |
| B12 | `Request\PrintAwb::setHtml($active)` | ignored the argument | honours it |
| B13 | `Objects\Branch` postal code | read `zipCode` (always null) | reads `zipcode` (API's key), with `zipCode` fallback |
| B14 | `Objects\AwbExtern` default payment | `'sender'` (invalid) | `'expeditor'` |
| B15 | `Request\GetCostsExternal` default delivery mode | `'Rutier'` | `'rutier'` |
| B16 | `Response\CreateCourierOrder` | no getters | gains `getId(): string\|int\|null` |
| B17 | `Objects\AwbTracker` | `paymentDate` unreadable | gains `getPaymentDate(): string` |
| B18 | `Response\Generic::isOk()` | could report success on `status:"fail"` | reflects failure |
| B19 | Hardcoded test account — `Fancourier::TEST_CLIENT_ID` / `TEST_USERNAME` / `TEST_PASSWORD` and public `Fancourier::testInstance()` | shipped in the production path | credentials and `testInstance()` removed; pass credentials explicitly / via environment variables |
| B20 | Auth failure — `Auth::getToken()` returning `false` | sent an empty `'Bearer '` header and issued an unauthenticated request | throws `\RuntimeException` with a clear message; **no request is sent** |
| B21 | Token expiry — cached token older than 24h | stale token used until a request failed | refresh + retry **exactly once** on a transport failure against a stale local token |
| B22 | `Client::postJson()` empty success body | could be treated as success | named failure: `FAN Courier returned an empty response` |
| B23 | `Objects\ShippingSlip::getPayment()` / `getReturnPayment()` | returned non-string shapes | return `string` |
| B24 | `Response\CreateAwbExternal::setData()` | an empty successful body was treated as an empty result | empty body is flagged as an error (`setErrorFromBody()`) instead of silently returning `[]` |

### D. Enums and code lists

Each code list below is a string-backed enum in `Fancourier\Enums\`. The raw string values remain
valid and are kept as reference — adoption is optional.

| Domain | Allowed string values | Enum class |
|---|---|---|
| Payment | `expeditor`, `destinatar`, `Altul` | `Fancourier\Enums\PaymentType` |
| Delivery mode | `rutier`, `aerian` | `Fancourier\Enums\DeliveryMode` |
| Document/content type | `document`, `non document` | `Fancourier\Enums\DocumentType` |
| Order type | `Standard`, `Express Loco 1h/2h/4h/6h` | `Fancourier\Enums\OrderType` |
| PUDO type | `fanbox`, `paypoint`, `office` | `Fancourier\Enums\PudoType` |
| Language | `ro`, `en` | `Fancourier\Enums\Language` |
| Print format | `A4`, `A5`, `A6` | `Fancourier\Enums\LabelFormat` |

### E. Changes with no consumer action

These were applied inside the library and require nothing from you:

- **Acronym casing** in method names (`getUitCode` → `getUITCode`, `setUitCode` → `setUITCode`,
  `getOpodAwbNumber` → `getOPODAwbNumber`). PHP method names are case-insensitive, so these are
  **not breaking**.
- **New internal base classes/traits** — `Response\PaginatedResponse`, `Request\PaginationTrait`,
  `Request\LanguageTrait`, `Request\AwbStringListTrait` — and inheritance tweaks such as
  `GetCostsExternal extends GetCosts` and `DeleteCourierOrder extends DeleteAwb`.

---

## Migration checklist

- [ ] `composer.json` requirement changed to `daika7ana/fancourier-api` + PHP `^8.3`.
- [ ] `ext-fileinfo` available in the runtime image.
- [ ] Rector codemod run; diff reviewed.
- [ ] Removed methods ([§B](#b-removed-methods)) have their call sites updated or deleted.
- [ ] Renamed calls ([§A](#a-renamed-client-methods)) updated (the codemod covers these).
- [ ] Subclass overrides updated for the new signatures (Step 4).
- [ ] Behaviour fixes ([§C](#c-behaviour-fixes)) reviewed against your usage.
- [ ] (Optional) payment / delivery / document / order / PUDO / language / format enums adopted — strings still work ([§D](#d-enums-and-code-lists)).
- [ ] Your full test suite passes.
- [ ] Any symbol you use that is not covered by this guide reported back to the maintainers.

---

## Appendix: instructions for AI coding agents

This guide is also written to be executed by an automated agent. Follow these rules exactly.

**Invariants**

1. Apply only the mappings in this document. **Do not invent** target names, and do not rename a
   symbol that is not listed — an unlisted symbol is unchanged.
2. Run the automated codemod (Step 2) **before** any manual edits. It is the deterministic layer.
3. The behaviour fixes in [§C](#c-behaviour-fixes) are **not** renames; a codemod cannot verify
   them. Apply them deliberately and rely on the consumer's test suite.
4. Never claim the migration succeeded without running the consumer's tests (Step 7).
5. If the consumer uses a symbol that appears neither as unchanged nor in a table, **stop and
   report it** rather than guessing.

**Procedure**

1. Bump the package requirement and PHP floor (Step 1).
2. Apply the Rector codemod (Step 2).
3. Optionally adopt enums ([§D](#d-enums-and-code-lists)) — string call sites keep working.
4. Apply the remaining manual changes (Steps 3-5).
5. Run the consumer's full test suite; fix failures; do not suppress them.
6. Report any symbol used but not covered by this guide.

---

## See also

- [`../CHANGELOG.md`](../CHANGELOG.md) → `## [2.0.0]` — human-readable release notes. Every
  breaking entry there has a corresponding row or section in this guide.
- [`../README.md`](../README.md) and the [`docs/`](README.md) reference — current 2.0 usage.
