# Migration Guide — fancourier-api v1.x → v2.0.0

Status: **SEED / living document.** The breaking changes do not exist yet; the target names below
are the planned set and are finalized during **Phase 3b** of `UPGRADE_PLAN.md`. Once the Rector set
is generated it becomes the source of truth, and this file must be kept in sync with
`CHANGELOG.md`.

Audience: human developers **and** AI coding agents migrating consuming code.

> **Provisional — do not freeze.** The symbol/break list in this document is **provisional** and must
> not be frozen until `UPGRADE_PLAN.md` **Phase 0** enumerates internal consumers (classifying each
> as facade-only versus direct `Client` / `Request` / `Object` / subclass usage) and selects a
> **canary service**. If consumers are facade-only, large parts of the `Client` / `Request` / `Object`
> mapping tables below are irrelevant to them — re-scope before executing §3.

---

## 0. Instructions for AI agents (read first)

**Invariants**

1. Apply only the mappings in this document. **Do not invent** target names, and do not rename a
   symbol that is not listed — an unlisted symbol is unchanged.
2. Run the automated codemod **before** any manual edits. It is the deterministic layer.
3. Behaviour ("semantic") changes in §4 are **not** renames; a codemod cannot verify them. Apply
   them deliberately and rely on the consumer's test suite.
4. Never claim the migration succeeded without running the consumer's tests (`§5`).
5. If the consumer uses a symbol that appears neither as unchanged nor in a table, **stop and
   report it** rather than guessing.

**Procedure**

1. Bump the package requirement and PHP floor (§1).
2. Apply the Rector codemod (§2).
3. Optionally adopt enums where desired (§4.1) — string call sites keep working in 2.0.
4. Apply the remaining semantic changes (§4).
5. Run the consumer's full test suite; fix failures; do not suppress them.
6. Report any symbol used but not covered by this guide.

---

## 1. Requirements and packaging changes

| Change | Before | After |
|---|---|---|
| Package name | `shusaura85/fancourier-api` | `<your-org>/fancourier-api` |
| PHP floor | `>= 7.0` | `^8.3` |
| Extensions | `ext-curl`, `ext-json` | `+ ext-fileinfo` |
| Namespace | `Fancourier\` | `Fancourier\` (unchanged) |

The symbol and break lists in §3/§4 remain provisional until `UPGRADE_PLAN.md` Phase 0 enumerates
internal consumers and picks a canary service (see the note at the top of this document).

---

## 2. Automated migration (Rector)

The mechanical renames and signature changes are encoded as a Rector set so migration is a command,
not hand-editing.

```bash
composer require --dev rector/rector
# copy the vendor-provided rector.php from the v2.0.0 tag / internal repo
vendor/bin/rector process src tests --dry-run   # review
vendor/bin/rector process src tests             # apply
```

The exact rules live in `rector.php` (added in Phase 3b). If a mapping is missing from the codemod,
add it in Phase 3b rather than documenting a manual step here.

This is **not** a consumer-only artifact: the same Rector set is applied to this repo's own `src/`
during Phase 3b, so the rules stay exercised and cannot silently rot.

---

## 3. Symbol mappings (deterministic)

> **Status: proposed.** Finalized in Phase 3b; until then treat as the intended target.

### 3.1 `Fancourier\Client` — snake_case → camelCase

| Old | New |
|---|---|
| `set_verify()` | `setVerify()` |
| `set_timeout()` | `setTimeout()` |
| `set_put_request()` | `setPutRequest()` |
| `set_delete_request()` | `setDeleteRequest()` |
| `post_json()` | `postJson()` |
| `post_ma()` | `postMultiArray()` |
| `headers_add()` | `addHeader()` |
| `headers_delete()` | `deleteHeader()` |
| `headers_reset()` | `resetHeaders()` |
| `get_error()` | `getError()` |
| `get_error_no()` | `getErrorNo()` |

`prepare_file()` and `prepare_file_string()` keep their names.

### 3.2 BC-safe cosmetic cleanups (not part of the migration)

PHP method names are **case-insensitive**, so the acronym-casing changes below are **not breaking**
and need no codemod or consumer action. They are applied while refactoring the library during
Phase 3b, not by consumers:

- `AwbTracker::getOpodAwbNumber` → `getOPODAwbNumber`
- `AwbIntern::getUitCode` / `setUitCode` → `getUITCode` / `setUITCode`
- `AwbExtern::getUitCode` / `setUitCode` → `getUITCode` / `setUITCode`

Additional **genuine** (case-sensitive) renames introduced by Phase 3b must be added to §3.1.

### 3.3 Removed methods

| Removed | Replacement |
|---|---|
| `Auth::getClientUsername()` | none (unused) |
| `Auth::getClientPassword()` | none (unused) |
| `Client::get_error_no()` | `getErrorNo()` |
| `Client::headers_reset()` | `resetHeaders()` |
| other dead public getters (§8.2b) | none — delete call sites |

### 3.4 Type declarations added

v2.0.0 adds parameter and return types across the public API. The **consumer-facing break is the
declared types themselves**: passing a non-coercible value where a parameter is now typed (or
depending on a now-typed return) throws a `TypeError` even in weak mode. Fix the call site; do not
cast blindly.

`declare(strict_types=1)` is **per calling file**: declaring it in the library only affects coercion
of calls *made inside* the library, not consumer call sites. The library will adopt it repo-wide via
Rector's `DeclareStrictTypesRector`, applied in **Phase 3b** after the Phase 3 correctness fixes and
a green PHPStan run. Consumer subclass overrides of changed signatures must still be updated to
match, or PHP raises a fatal error.

---

## 4. Behaviour changes (semantic — verify with tests)

These are **not** renames; a codemod cannot detect them. Each is a deliberate correction.

| # | Symbol / endpoint | Before (buggy) | After (correct) |
|---|---|---|---|
| B1 | `Request::getPerPage()` (5 classes: `GetCitiesExternal`, `GetCourierOrders`, `GetShippingSlip`, `GetBankTransfers`, `GetStreets`) | returned `$this->page` | returns `$this->perPage` |
| B2 | `PrintAwb::getSize()` | returned `$this->lang` | returns `$this->size` |
| B3 | `CreateCourierOrder::getPickupDate()` | returned `$this->notes` | returns the pickup date |
| B4 | `CreateCourierOrder::getAwb($awbNo)` | required unused parameter | parameter removed |
| B5 | `AwbIntern::getIsValueUnderThreshold()` | threw when set (inverted guard) | returns the stored value |
| B6 | `Objects\CourierOrder` dimension getters | returned `[]` (→ `TypeError`) | return `0.0` / nullable |
| B7 | `Response\GetBranches::get()` | returned `false` (→ `TypeError`) | returns `[]` |
| B8 | `Response\GetAwbConfirmations::getRAWbytes()`/`getLength()` | wrong return shapes | corrected |
| B9 | `Objects\Pudo::getAddress()` | fallback `''` (→ `TypeError`) | fallback `[]` |
| B10 | `Client::postJson()` | closed handle before reading error | error read before close |
| B11 | `AbstractRequest::send()` | undefined `$responseString` for unknown method | throws `DomainException` |
| B12 | `PrintAwb::setHtml($active)` | ignored the argument | honours it |
| B13 | `Objects\Branch` postal code | read `zipCode` (always null) | reads `zipcode` (API's key) |
| B14 | `Objects\AwbExtern` default payment | `'sender'` (invalid) | `'expeditor'` |
| B15 | `GetCostsExternal` default delivery mode | `'Rutier'` | `'rutier'` |
| B16 | `Response\CreateCourierOrder` | no getters | gains `getId()` |
| B17 | `Objects\AwbTracker` | `paymentDate` unreadable | gains `getPaymentDate()` |
| B18 | `Response\Generic::isOk()` | could report success on `status:"fail"` | reflects failure |
| B19 | Hardcoded test account — `Fancourier::TEST_CLIENT_ID` / `TEST_USERNAME` / `TEST_PASSWORD` and public `Fancourier::testInstance()` | shipped in the production path | credentials removed; `testInstance()` removed from the public API (test-only fixture) |
| B20 | Auth failure — `Auth::getToken()` returning `false` | `AbstractRequest::send()` sent an empty `'Bearer '` header and issued an unauthenticated request | fails explicitly with a clear exception; no request is sent on a missing token |
| B21 | Token expiry — cached token older than 24h | stale token used until a request failed | decide and document: refresh on expiry (recommended) vs. rely on the `false` path; must be consistent with B20 |

### 4.1 Code lists — additive (constants + enums), no forced cutover

2.0 ships each code list **additively**: as constants on the relevant classes **and** as backed enum
classes. Where practical, the setters accept `string|BackedEnum`, so existing string calls keep
working unchanged. Consumers are **not required** to switch to enums in 2.0 — adoption is **optional
and can be done incrementally**.

Rector **cannot** rewrite string literals passed as arguments, so enum adoption is manual; it must
not be a hard break. The raw string values below remain valid and are kept as reference:

| Domain | Allowed string values | Constants + enum |
|---|---|---|
| Payment | `expeditor`, `destinatar`, `Altul` | `PaymentType` |
| Delivery mode | `rutier`, `aerian` | `DeliveryMode` |
| Document/content type | `document`, `non document` | `DocumentType` |
| Order type | `Standard`, `Express Loco 1h/2h/4h/6h` | `OrderType` |
| PUDO type | `fanbox`, `paypoint`, `office` | `PudoType` |
| Language | `ro`, `en` | `Language` |
| Print format | `A4`, `A5`, `A6` | `LabelFormat` |

Constant/enum names are indicative; finalized in Phase 3b.

---

## 5. Per-consumer migration checklist

- [ ] `composer.json` requirement bumped to the v2 package + PHP `^8.3`.
- [ ] `ext-fileinfo` available in the runtime image.
- [ ] Rector codemod run; diff reviewed.
- [ ] All removed methods (`§3.3`) have their call sites deleted.
- [ ] All renamed calls (`§3.1`) updated (codemod should cover these). `§3.2` is cosmetic only.
- [ ] Subclass overrides updated for the new signatures (`§3.4`).
- [ ] (Optional) Adopt payment/delivery/document/order/PUDO/language/format enums — strings still work (`§4.1`).
- [ ] Behaviour changes in `§4` reviewed against the consumer's usage.
- [ ] Consumer's full test suite passes.
- [ ] Any symbol used but not covered by this guide reported back.

---

## 6. Single source of truth

There is **no separate machine-readable appendix** (the former YAML block was removed). The §3
symbol-mapping tables and §4 behaviour list are the single source of truth, and `rector.php` is the
executable form of §3. Three parallel lists (tables + YAML + Rector rules) would only drift.

---

## 7. Changelog

See `CHANGELOG.md` → `## [2.0.0]` for the human-readable list. Every breaking entry there must have
a corresponding row in this guide.
