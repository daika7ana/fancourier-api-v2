# FAN Courier API v2 — Spec Gap Analysis

Companion to `UPGRADE_PLAN.md`. Compares the **official FAN Courier API v2.0 specification**
(the PDF `RO_FANCourier_API_130825.pdf`) against the **current code** at commit `204ec7b`.

- **Spec:** `RO_FANCourier_API_130825.pdf` — title "Documentatie api", v2.0, **Septembrie 2025**,
  61 pages, Romanian prose, identifiers untranslated. Base URL `https://api.fancourier.ro`.
- **Code:** 26 `Request` classes + `Auth` login, 26 `Response` classes, 20 `Objects` classes.
- **Method:** full-text extraction of all 61/61 pages, then a machine-readable code-side surface
  inventory, reconciled endpoint-by-endpoint and field-by-field.

> Line numbers refer to commit `204ec7b` and must be re-confirmed after any earlier refactor phase.

---

## 1. Executive summary

| Dimension | Result |
|---|---|
| **Endpoint coverage** | ✅ **28/28 documented endpoints implemented** (including `POST /login` via `Auth`). No missing endpoint. |
| **Extra endpoints in code** | None. Every facade method maps to a documented endpoint; the only unmatched facade entry is the commented `requestCourier()` (`Fancourier.php:139`). |
| **Field-name drift** | ⚠️ A handful of real mismatches (see §3) — most seriously `Branch` postal code. |
| **Enum drift** | ⚠️ Several: extern-awb payment default, external-tariff deliveryMode casing, missing `Altul` payment value, no constants for the documented service/option/event lists. |
| **Response coverage** | ⚠️ `CreateCourierOrder` exposes no typed getters despite the doc returning `data.id`. |
| **Undocumented code surface** | ⚠️ Code sends/reads several fields not in the spec (NUE tax fields, `info.awbNumber`, `info.currency`, `info.status`, `info.awbs`, `transactionType`). |
| **Spec gaps** | The PDF has **no error format, no HTTP status table**, no `/reports/counties` or `/reports/countries` response schema, and no county/country lists. |

**Bottom line:** coverage is complete; the work is field/enum alignment, better `status:"fail"`
handling, and encoding the documented code lists as constants. The previously-planned defect list
(dead code, typos, bugs) is unaffected and largely independent.

---

## 2. Endpoint coverage matrix

All documented endpoints. `Status` = present in code.

| # | Doc method + path | Code Request / facade | Status |
|---|---|---|---|
| 1 | `POST /login` | `Auth::retrieve_token()` | ✅ |
| 2 | `GET /reports/services` | `GetServices` / `getServices()` | ✅ |
| 3 | `GET /reports/service-options` | `GetServiceOptions` / `getServiceOptions()` | ✅ |
| 4 | `GET /reports/counties` | `GetCounties` / `getCounties()` | ✅ |
| 5 | `GET /reports/localities` | `GetCities` / `getCities()` | ✅ |
| 6 | `GET /reports/streets` | `GetStreets` / `getStreets()` | ✅ |
| 7 | `POST /intern-awb` | `CreateAwb` / `createAwb()` | ✅ |
| 8 | `GET /awb/label` | `PrintAwb` / `printAwb()` | ✅ |
| 9 | `DELETE /awb` | `DeleteAwb` / `deleteAwb()` | ✅ |
| 10 | `GET /reports/pickup-points?type=` | `GetPudo` / `getPudo()` | ✅ |
| 11 | `GET /reports/pickup-points?id=` | `GetPudo` / `getPudo()` | ✅ |
| 12 | `GET /reports/awb/internal-tariff` | `GetCosts` / `getCosts()` | ✅ |
| 13 | `POST /extern-awb` | `CreateAwbExternal` / `createAwbExternal()` | ✅ |
| 14 | `GET /reports/countries` | `GetCountries` / `getCountries()` | ✅ |
| 15 | `GET /reports/external-counties` | `GetCountiesExternal` / `getCountiesExternal()` | ✅ |
| 16 | `GET /reports/external-localities` | `GetCitiesExternal` / `getCitiesExternal()` | ✅ |
| 17 | `GET /reports/awb/external-tariff` | `GetCostsExternal` / `getCostsExternal()` | ✅ |
| 18 | `POST /order` | `CreateCourierOrder` / `createCourierOrder()` | ✅ |
| 19 | `DELETE /order` | `DeleteCourierOrder` / `deleteCourierOrder()` | ✅ |
| 20 | `GET /reports/awb` | `GetShippingSlip` / `getShippingSlip()` | ✅ |
| 21 | `GET /reports/awb-events` | `GetAwbEvents` / `getAwbEvents()` | ✅ |
| 22 | `GET /reports/awb/tracking` | `TrackAwb` / `trackAwb()` | ✅ |
| 23 | `GET /reports/bank-transfers` | `GetBankTransfers` / `getBankTransfers()` | ✅ |
| 24 | `GET /reports/get-awb-confirmations` | `GetAwbConfirmations` / `getAwbConfirmations()` | ✅ |
| 25 | `GET /reports/orders` | `GetCourierOrders` / `getCourierOrders()` | ✅ |
| 26 | `GET /reports/order-events` | `GetCourierOrderEvents` / `getCourierOrderEvents()` | ✅ |
| 27 | `GET /reports/orders/tracking` | `TrackCourierOrder` / `trackCourierOrder()` | ✅ |
| 28 | `GET /reports/branches` | `GetBranches` / `getBranches()` | ✅ |

---

## 3. Field-level gaps and mismatches

Severity: **H** = wrong data sent/received, **M** = incomplete mapping, **L** = cosmetic/confirm.

### G1 — H — `GetBranches` reads `zipCode`, the branches endpoint returns `zipcode`
- Doc §28 explicitly spells the branch address field **`zipcode`** (lowercase `c`), unlike
  `zipCode` used by every other endpoint.
- `Objects/Branch.php` constructor reads `address.zipCode`.
- **Effect:** `getPostalCode()` is `null` for all branches.
- **Action:** verify against a live response; parse both `zipCode` and `zipcode`.

### G2 — H — `AwbExtern` default payment is `'sender'`, not `expeditor`
- `Objects/AwbExtern.php:68` defaults `payment` to `'sender'`.
- Doc §13 payment values: `expeditor`; `destinatar`; `Altul`. `AbstractRequest::TYPE_SENDER`
  is `'expeditor'` and `AwbIntern` uses it correctly.
- **Effect:** export AWBs can be sent with an invalid payment value.
- **Action:** default to `AbstractRequest::TYPE_SENDER` (`'expeditor'`).

### G3 — H — `GetCostsExternal` default deliveryMode casing `'Rutier'`
- `Request/GetCostsExternal.php:23` defaults to `'Rutier'` (capital R); the same file uses
  `'rutier'`/`'aerian'` at `:89`, and `AwbExtern.php:13` uses `'rutier'`.
- Doc §5.6 deliveryMode: `rutier`; `aerian`.
- **Action:** normalise to lowercase `rutier`.

### G4 — M — `CreateCourierOrder` response exposes no typed getters
- Doc §18 response: `{ status:"success", data.id:int }`.
- `Response/CreateCourierOrder.php` reads `status` / `data.id` / `message` but defines **no**
  getters (only inherited `getData()`/`isOk()`), unlike every other create endpoint.
- **Action:** add `getId()` (additive, BC-safe).

### G5 — M — `AwbTracker::$paymentDate` parsed but unreadable
- `Objects/AwbTracker.php:12` stores `paymentDate` (`:32`); no getter.
- Doc §22 tracking response includes `paymentDate`.
- **Action:** add `getPaymentDate()` (additive). (Also listed as dead-data in `UPGRADE_PLAN.md` §8.)

### G6 — M — `AwbExtern::$currency` set but never sent
- `Objects/AwbExtern.php` has `setCurrency()` but the payload line is commented
  (`:106`); the property has a getter, never emitted.
- Doc §13 does **not** list `currency` for `/extern-awb`, but the borderou response
  (`/reports/awb`) does include `currency`.
- **Action:** decide — send it (if the API accepts it) or remove the setter/getter. Tracked as
  `UPGRADE_PLAN.md` defect #15.

### G7 — M — `CreateCourierOrder` emits `info.awbNumber` not in the spec
- Code emits `info.awbNumber` (`pack()`), doc §18 lists no such field.
- **Action:** confirm with FAN whether it is accepted; keep if needed.

### G8 — L — `info.currency` on `/intern-awb`
- `AwbIntern` emits `info.currency`; doc §7 does not list it (only the borderou response does).
- **Action:** confirm; likely accepted but undocumented.

### G9 — L — `ServiceOption` property is `code`, response field is `id`
- Doc §3 `/reports/service-options` returns `{ id:string, name:string }`.
- `Objects/ServiceOption.php` stores `code`; `GetServiceOptions::setData()` reads `data[].id`.
- **Action:** verify the mapping is intentional; no behavioural bug if `setData` assigns `id`→`code`.

### G10 — L — pickup-point address `reference` not mapped
- Doc §10/11 address includes `reference`; `Pudo` keeps `address` as a raw array.
- **Action:** none required (raw passthrough), note only.

### G11 — L — `check` undocumented fields the code reads (harmless extras)
- `BankTransfer` reads `transactionType` — not in doc §23.
- `CourierOrder` reads `info.status` and `info.awbs` — not in doc §25.
- `GetCountries` builds a `deliveryMode[1|2]` map — doc §14 shows no payload.
- **Action:** none; keep tolerant parsing.

---

## 4. Enum / code-list alignment

The doc enumerates several lists the code currently handles as raw strings or not at all.

### 4.1 Payment values — **mismatch to fix**
- Doc §5.5: `expeditor`; `destinatar`; `Altul` (JSON examples also use `sender`/`recipient`).
- Code: `TYPE_SENDER='expeditor'`, `TYPE_RECIPIENT='destinatar'` (correct) — but
  `AwbExtern` defaults to `'sender'` (see G2), and there is **no constant for `Altul`**.
- **Action:** fix G2; add an additive `TYPE_OTHER = 'Altul'` constant.

### 4.2 Delivery mode — casing
- Doc: `rutier`; `aerian`. Code: mixed (`'Rutier'` vs `'rutier'`). See G3.

### 4.3 Content / document type — consistent
- Doc: `document`; `non document`.
- Code: `/extern-awb` emits `info.contentType`; `/external-tariff` emits `info.documentType`.
  The code mirrors the doc's own naming inconsistency (see §5). No change needed.

### 4.4 Service option codes — no constants
- Doc §5.2 lists 14 codes: `A B C D E F M O P S V W X Y` with meanings.
- Code: free-form string via `addOption()`; only `PUDO_*` constants exist.
- **Action (enhancement):** add constants (additive) to reduce magic strings.

### 4.5 Order types — no constants
- Doc §5.12: `Standard`; `Express Loco 1h/2h/4h/6h`.
- Code: string, default `'Standard'` (`Request/CreateCourierOrder.php:26`).
- **Action (enhancement):** add constants.

### 4.6 Service types — no constants
- Doc §5.1 lists 25 service types (id 1–28, gaps) with names.
- Code: string, defaults `'Standard'` / `'Export'`.
- **Action (enhancement):** add a service-name list/constants.

### 4.7 AWB event codes — no constants
- Doc §5.10 lists ~50 codes (`C0`, `H0`–`H17`, `S1`–`S50`, …).
- Code: `GetAwbEvents` returns `id`/`name` verbatim; no enum.
- **Action (enhancement, optional):** expose as constants if callers branch on codes.

### 4.8 Order event codes — no constants
- Doc §5.11: `0,1,2,3,4,5,8,12,99`.
- Code: `GetCourierOrderEvents` returns raw. Optional constants.

### 4.9 PUDO types, print formats, languages — already modelled
- `PUDO_FANBOX/PAYPOINT/OFFICE`; `PrintAwb` sizes `A4/A5/A6`; languages `ro/en`. ✅

---

## 5. Spec inconsistencies (documentation defects — do NOT code to a guess)

These are contradictions inside the PDF; the code already chose one side. Confirm with FAN before
changing behaviour.

| # | Inconsistency | Code's choice | Assessment |
|---|---|---|---|
| D1 | `/intern-awb` schema uses `info.parcel`/`info.envelope`; every JSON example uses `info.packages.parcel/envelope` | `info.packages.*` | Code matches examples — likely correct. |
| D2 | `/extern-awb` heading says `POST` but a label reads "Metoda: HTTP GET" | `POST` | Code matches examples — likely correct. |
| D3 | `/extern-awb` uses `contentType`; `/external-tariff` uses `documentType` | mirrors both | Code matches doc verbatim. |
| D4 | Branches address uses `zipcode`; all others `zipCode` | `zipCode` on Branch | **Code likely wrong** — see G1. |
| D5 | No error body schema and no HTTP status table; only `DEL /order` shows `{status:"fail",message}` | parses `status`/`message` | See §6 — needs a deliberate convention. |
| D6 | `/reports/counties` and `/reports/countries` responses not shown | assumes `data[]` rows (`County`, `Country`) | Unverified; confirm shapes. |
| D7 | AWB event `H3` name has a typo ("Expeditis sortata pe banda") | passes through | Pass through verbatim; do not "fix" API data. |

---

## 6. Error / status handling (spec-silent)

The spec provides **no error-code enum and no HTTP status conventions**. Observed only:

- Success: `{ "status": "success", "data": ... }`; list endpoints add `total`, `perPage`, `currentPage`.
- Failure example (`DEL /order`): `{ "status": "fail", "message": "…" }`.
- `/intern-awb` returns a per-shipment `errors` field (format unspecified when populated).

Current code implications to verify in Phase 3:

1. `Response\Generic::isOk()` is `empty(errorCode) && empty(errorMessage)`. API-level
   `status:"fail"` responses must explicitly populate `errorMessage` in each `setData()`,
   otherwise `isOk()` returns **true for a failed request**.
2. Confirm every concrete `Response::setData()` sets the error on `status !== 'success'`
   (not just HTTP-level failure in `AbstractRequest::send()`).
3. `CreateAwb` per-shipment errors are surfaced via `AwbIntern::hasErrors()`/`getErrors()` — good;
   ensure the top-level `isOk()` also reflects a partial failure appropriately.
4. Token expiry is not documented as an error body; `AbstractRequest::send()` sends
   `Bearer …` and `Auth::getToken()` refreshes only on construction/`$refresh=true`. Add
   detection of an auth-expiry response → refresh + retry, or document the limitation.

**Action:** add a dedicated error-handling test matrix (fixtures for `status:success`,
`status:fail`, HTTP-level failure, expired token, per-shipment `errors`).

---

## 7. Auth alignment

| Item | Doc | Code | Status |
|---|---|---|---|
| Endpoint | `POST /login` | `Auth` gateway `'login'` | ✅ |
| Params | query `username`, `password` | `post()` body `username`/`password` | ✅ (verify query vs body accepted) |
| Response | `data.token`, `data.expiresAt` | parsed in `Auth::retrieve_token()` | ✅ |
| Lifetime | 24h | 24h, refresh via `getToken($refresh)` | ✅ |
| Header | `Bearer Token` | `'Bearer '.$token` | ✅ |
| Host | `https://api.fancourier.ro` (no slash) | `API_URL` with trailing slash + gateway concat | ✅ |

No separate sandbox URL is documented; prod/test differ only by credentials. The hardcoded test
account constants (`Fancourier.php:39-41`) match the doc's example, but should not ship in a
production-default path (see `UPGRADE_PLAN.md`).

---

## 8. Undocumented code surface (code beyond the spec)

Feature work already present in code but absent from the PDF:

- **NON-UE parcel tax fields** on `/intern-awb`: `info.isValueUnderThreshold`, `info.countryCode`,
  `info.vatId`, `info.company` (`AwbIntern` `NUE_*` properties). Added by an earlier commit
  ("Add support for NON-UE parcel tax"); the Sept 2025 PDF omits them.
- `info.awbNumber` on `/order` (G7).
- `info.currency` on `/intern-awb` (G8).
- `transactionType` on bank transfers; `info.status`/`info.awbs` on orders (G11).
- `Pudo` extras: `phones`, `email`, `highDemand`, `paymentMethods`.

**Action:** keep, but mark as extensions; document them or raise with FAN to be added to the spec.

---

## 9. Recommended actions (fold into `UPGRADE_PLAN.md` Phase 3 and a new Phase 3b)

Ordered by severity:

| ID | Action | Type | BC-safe |
|---|---|---|---|
| G1 | Fix `Branch` postal-code key (`zipcode`) | bug | yes |
| G2 | Fix `AwbExtern` default payment to `expeditor` | bug | yes |
| G3 | Normalise `deliveryMode` default to `rutier` | bug | yes |
| G4 | Add `CreateCourierOrder` response getters (`getId()`) | completion | yes |
| G5 | Add `AwbTracker::getPaymentDate()` | completion | yes |
| G6 | Decide `AwbExtern::$currency` (send or remove) | decision | yes |
| G7–G8 | Confirm undocumented `awbNumber`/`currency` fields | decision | — |
| §6.1–6.4 | Add explicit `status:"fail"` handling + error test matrix; token-expiry handling | correctness | yes |
| §4.1 | Add `TYPE_OTHER='Altul'` constant | additive | yes |
| §4.4–4.8 | Encode documented code lists as constants (options, order types, services, events) | enhancement | yes |

These are **new** findings relative to `UPGRADE_PLAN.md` §7's defect list and should be added to
Phase 3. None require breaking the public API.

---

## 10. Coverage & limitations

**Read in full:** all 61/61 PDF pages; no unread or image-only pages.

**Not present in the document** (a spec gap, not an extraction gap):
- Full `/reports/counties` and `/reports/countries` response schemas.
- Complete county/country lists.
- HTTP status codes and a structured error-code list.
- A sandbox/SIT host.
- The PUDO map section is a JS-library integration guide, not REST.

**Requires live confirmation** (cannot be verified from spec + code alone): G1, G7, G8, D6 — run
against a test account and capture fixtures (which also become Phase-1 test fixtures).
