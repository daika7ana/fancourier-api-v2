# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-10-06

The first modernised release. This is a **breaking major**: the public API is
re-typed, renamed, trimmed, and extended. Every break has a row or section in
[`MIGRATION.md`](./MIGRATION.md), and the mechanical renames are codemoddable via
`rector.php` (see §2 of the migration guide).

### Removed

- `Client::get_error_no()` and the private `$error_no` state it read.
- `Client::headers_reset()`.
- `Auth::getClientUsername()` and `Auth::getClientPassword()`.
- `Fancourier::testInstance()` and the `Fancourier::TEST_CLIENT_ID`,
  `TEST_USERNAME`, `TEST_PASSWORD` constants. Construct the client explicitly
  with credentials supplied by your own config or environment.

### Renamed

`Fancourier\Client` snake_case → camelCase (breaking; see `MIGRATION.md` §3.1):

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

Acronym-only renames (case-insensitive, therefore **BC-safe**; applied in the
library, no consumer action): `getUITCode`/`setUITCode` and
`getOPODAwbNumber`.

### Added

- `Fancourier\Enums\` — string-backed enums `PaymentType`, `DeliveryMode`,
  `DocumentType`, `OrderType`, `PudoType`, `Language`, `LabelFormat`.
- Code-list constants on `AbstractRequest` (payment/order-type/service/option/
  PUDO/event codes), kept parallel to the enums.
- Setters accept `string|Enum` where a code list applies; string call sites keep
  working and enum adoption is optional.
- `Response\CreateCourierOrder::getId()`.
- `Objects\AwbTracker::getPaymentDate()`.
- `Response\PaginatedResponse` and the `Request\PaginationTrait`,
  `Request\LanguageTrait`, `Request\AwbStringListTrait` bases. Internal dedup
  only, no consumer action.

### Changed

- Native parameter, property, and return types across the public API.
- `declare(strict_types=1)` in every PHP file. Note: `strict_types` is per
  **calling** file, so the consumer-facing break is the **declared types**, not
  `strict_types` itself (see `MIGRATION.md` §3.4).

### Fixed

Behaviour corrections (a codemod cannot apply these; see the `MIGRATION.md` §4
B-list for the full table). Headline fixes: `getPerPage()` returned the page
instead of the page size; `PrintAwb::getSize()`/`setHtml()`; `getPickupDate()`;
`Branch` postal-code key; `AwbExtern` default payment; `GetCostsExternal`
delivery mode; `Generic::isOk()` error-state handling; `GetBranches::get()`
returns `?Branch` (null on miss, not `[]`); `GetAwbConfirmations` return types;
`Pudo::getAddress()` fallback; `ShippingSlip` payment return types; a
`CreateAwbExternal` empty-body guard; token-expiry refresh + retry exactly once;
auth-failure guard (`\RuntimeException`, no request sent); and an empty-response
named failure in `Client::postJson()`.
