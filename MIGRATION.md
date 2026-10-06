# Migration Guide — fancourier-api v1.x → v2.0.0

Status: **FINAL.** The symbol and break lists below are frozen and reflect what `2.0.0` actually
ships (branch `refactor/2.0.0`). This guide is kept in sync with `CHANGELOG.md` (see §7).

Audience: human developers **and** AI coding agents migrating consuming code.

> **The canary-consumer exit criterion is satisfied by a fixture consumer.** This fork has no
> downstream consumers to enumerate, so instead of a real canary service the migration path is
> exercised by the fixture consumer `canary/consumer-v1`, run through `rector.php` in CI. The break
> list is therefore final; there is no separate downstream-consumer inventory to wait on.

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
| Package name | `shusaura85/fancourier-api` | `daika7ana/fancourier-api` |
| PHP floor | `>= 7.0` | `^8.3` |
| Extensions | `ext-curl`, `ext-json` | `+ ext-fileinfo` |
| Namespace | `Fancourier\` | `Fancourier\` (unchanged) |

---

## 2. Automated migration (Rector)

The mechanical renames are encoded as a Rector set so migration is a command, not hand-editing.

```bash
composer require --dev rector/rector
# copy the vendor-provided rector.php from the v2.0.0 tag / fork repo
vendor/bin/rector process src tests --dry-run   # review
vendor/bin/rector process src tests             # apply
```

`rector.php` is the **consumer set**: rename-only, no signature or type changes, and it is the
executable form of §3.1. It is also applied to this repo's own `src/` so the rules stay exercised
and cannot silently rot.

`rector-internal.php` is **repo-only and is not shipped**: it carries
`DeclareStrictTypesRector` and is used to add `declare(strict_types=1)` to this repository. It is
not part of the consumer migration story.

---

## 3. Symbol mappings (deterministic)

> **Status: FINAL.** Frozen as shipped in 2.0.0.

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
| `get_error()` | `getError()` |

`prepare_file()` and `prepare_file_string()` keep their names.

### 3.2 BC-safe cosmetic cleanups (no consumer action)

PHP method names are **case-insensitive**, so the acronym-casing changes below are **not breaking**
and need no codemod or consumer action. They were applied while refactoring the library (Phase 3),
not by consumers:

- `AwbTracker::getOpodAwbNumber` → `getOPODAwbNumber`
- `AwbIntern::getUitCode` / `setUitCode` → `getUITCode` / `setUITCode`
- `AwbExtern::getUitCode` / `setUitCode` → `getUITCode` / `setUITCode`

**Already applied** — no action required.

### 3.3 Removed methods

| Removed | Replacement |
|---|---|
| `Auth::getClientUsername()` | none (unused — delete call sites) |
| `Auth::getClientPassword()` | none (unused — delete call sites) |
| `Client::get_error_no()` | none (dead — delete call sites) |
| `Client::headers_reset()` | none (dead — delete call sites) |
| `Fancourier::testInstance()` | none — construct `new Fancourier($clientId, $username, $password, $token)` with your own credentials |
| `Fancourier::TEST_CLIENT_ID` / `TEST_USERNAME` / `TEST_PASSWORD` | none — supply credentials explicitly or via environment variables |

### 3.4 Type declarations added

v2.0.0 adds parameter, property, and return types across the public API. The **consumer-facing break
is the declared types themselves**: passing a non-coercible value where a parameter is now typed (or
depending on a now-typed return) throws a `TypeError` even in weak mode. Fix the call site; do not
cast blindly.

`declare(strict_types=1)` is **per calling file**: declaring it in the library only affects coercion
of calls *made inside* the library, not consumer call sites. The library adopted it repo-wide in
Phase 3b — after the Phase 3 correctness fixes and a green PHPStan run. Consumer subclass overrides
of changed signatures must be updated to match, or PHP raises a fatal error.

---

## 4. Behaviour changes (semantic — verify with tests)

These are **not** renames; a codemod cannot detect them. Each is a deliberate correction.

| # | Symbol / endpoint | Before (buggy) | After (correct) |
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
| B20 | Auth failure — `Auth::getToken()` returning `false` | `AbstractRequest::send()` sent an empty `'Bearer '` header and issued an unauthenticated request | throws `\RuntimeException` with a clear message; **no request is sent** |
| B21 | Token expiry — cached token older than 24h | stale token used until a request failed | refresh + retry **exactly once** on a transport failure against a stale local token |
| B22 | `Client::postJson()` empty success body | could be treated as success | named failure: `FAN Courier returned an empty response` |
| B23 | `Objects\ShippingSlip::getPayment()` / `getReturnPayment()` | returned non-string shapes | return `string` |
| B24 | `Response\CreateAwbExternal::setData()` | an empty successful body was treated as an empty result | empty body is flagged as an error (`setErrorFromBody()`) instead of silently returning `[]` |

### 4.1 Code lists — additive (constants + enums), no forced cutover

2.0 ships each code list **additively**: as constants on `AbstractRequest` **and** as backed enum
classes in `Fancourier\Enums\`. Where practical, the setters accept `string|BackedEnum`, so existing
string calls keep working unchanged. Consumers are **not required** to switch to enums in 2.0 —
adoption is **optional and can be done incrementally**.

Rector **cannot** rewrite string literals passed as arguments, so enum adoption is manual; it is not
a hard break. The raw string values below remain valid and are kept as reference:

| Domain | Allowed string values | Enum class |
|---|---|---|
| Payment | `expeditor`, `destinatar`, `Altul` | `Fancourier\Enums\PaymentType` |
| Delivery mode | `rutier`, `aerian` | `Fancourier\Enums\DeliveryMode` |
| Document/content type | `document`, `non document` | `Fancourier\Enums\DocumentType` |
| Order type | `Standard`, `Express Loco 1h/2h/4h/6h` | `Fancourier\Enums\OrderType` |
| PUDO type | `fanbox`, `paypoint`, `office` | `Fancourier\Enums\PudoType` |
| Language | `ro`, `en` | `Fancourier\Enums\Language` |
| Print format | `A4`, `A5`, `A6` | `Fancourier\Enums\LabelFormat` |

The constant sets on `AbstractRequest` (`TYPE_*`, `PUDO_*`, `OPTION_*`, `ORDER_TYPE_*`, `SERVICE_*`,
`AWB_EVENT_*`, `ORDER_EVENT_*`) remain parallel to the enums.

**Additive internal bases (no consumer action):** `Response\PaginatedResponse` and the
`Request\PaginationTrait`, `Request\LanguageTrait`, `Request\AwbStringListTrait` bases;
`GetCostsExternal extends GetCosts` and `DeleteCourierOrder extends DeleteAwb`.

---

## 5. Per-consumer migration checklist

- [ ] `composer.json` requirement bumped to `daika7ana/fancourier-api` + PHP `^8.3`.
- [ ] `ext-fileinfo` available in the runtime image.
- [ ] Rector codemod (`rector.php`) run; diff reviewed.
- [ ] All removed methods (`§3.3`) have their call sites deleted.
- [ ] All renamed calls (`§3.1`) updated (codemod covers these). `§3.2` needs no action.
- [ ] Subclass overrides updated for the new signatures (`§3.4`).
- [ ] (Optional) Adopt payment/delivery/document/order/PUDO/language/format enums — strings still work (`§4.1`).
- [ ] Behaviour changes in `§4` reviewed against the consumer's usage.
- [ ] Consumer's full test suite passes.
- [ ] Any symbol used but not covered by this guide reported back.

---

## 6. Single source of truth

There is **no separate machine-readable appendix** (the former YAML block was removed). The §3
symbol-mapping tables and §4 behaviour list are the single source of truth, and `rector.php` is the
executable form of §3. `rector-internal.php` is repo-only (strict types) and is not shipped. Three
parallel lists (tables + YAML + Rector rules) would only drift.

---

## 7. Changelog

See `CHANGELOG.md` → `## [2.0.0]` for the human-readable list. Every breaking entry there has a
corresponding row in this guide.

---

## 8. Resolved during Phase 3b

Deviations from the seed version of this document:

- `Client::get_error_no()` / `headers_reset()` were **removed**, not renamed (they were dead) — see
  §3.3. The seed §3.1/§3.3 provisional "rename" rows are gone.
- Acronym renames (`getUitCode` → `getUITCode`, `getOpodAwbNumber` → `getOPODAwbNumber`) were
  completed in Phase 3 as BC-safe cosmetic cleanups — no consumer action (§3.2).
- The canary-consumer enumeration was substituted by a fixture canary (`canary/consumer-v1`) because
  this fork has no downstream consumers.
- PHPStan was ratcheted to **level 8** (the seed expected "likely level 5"). Level 9 (≈474 errors)
  is deferred as a bounded follow-up.
- The "internal fork" wording was superseded by commit `fe9763a`; this is a public fork.
