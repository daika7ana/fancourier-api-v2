# FAN Courier API v2 — Fork & Refactor Upgrade Plan

Status: **IN PROGRESS — Phases 0–3 and Phase 3b waves W0–W5 are DONE.** Phase 4 (dead-code &
docs), Phase 5 (`pint`), Phase 6 (examples), and Phase 7 (release/tag `2.0.0`) remain.
Target: forked public library, major overhaul.
Baseline commit: `204ec7b` (Merge PR #36), branch `main`; work on `refactor/2.0.0`.

Phases 0–3 and Phase 3b waves W0–W5 are complete (see the per-phase statuses below and the
deviation log in §12). Phase 3b is code-complete: the suite is green at 341 tests / 1453
assertions with PHPStan level 8, but 2.0.0 is **not tagged** yet — Phases 4–7 still gate the
release.

This document is the authoritative plan for forking and modernising the
`fancourier-api-v2` package for public use. It covers the confirmed scope,
the current-state assessment, a phased execution plan with exit criteria, the
concrete configuration artifacts, and the full inventory of defects, dead code,
typos, and duplication discovered during reconnaissance.

---

## 1. Locked decisions

| Decision | Choice | Notes |
|---|---|---|
| Minimum PHP | **8.3** | 7.0/7.4 are EOL; 8.1 is EOL as of Jan 2026. 8.3 unlocks typed class constants, readonly classes, `json_validate()`, `#[\Override]`. 8.3 security support ends ~Dec 2026, so a floor bump (8.4+) must be **explicitly scheduled** as follow-up — this pass keeps the floor at 8.3. |
| Public API compatibility | **Break in one major (2.0.0)** | Full modernization allowed: types, renames, removals, enums, dedup. Every break is documented in `MIGRATION.md` and automated via a Rector ruleset. |
| Tooling | **PHPStan ^2 + Pint + GitHub Actions CI** | Replace legacy Travis. Greenfield: no pre-existing baseline to preserve. `pint.json` is already authored (`per` preset); `laravel/pint` must be added as a dev dependency so CI can run it. PHPStan starts at the **highest level that is green on the current (untyped) code — likely level 5** — with **no committed baseline**, and is **ratcheted upward as types land in Phase 3b** (do not set level 8–9 up front: with ~470 untyped properties that is thousands of errors). |
| Analyzer alternative | **mago on hold (revisit at 2.0)** | Evaluated 2026-10: fast, unified (format/lint/analyze), but explicitly *not* a PHPStan parity replacement (maintainer Discussion #379; no automated parity suite) and 2.0 is a ground-up analyzer rewrite with a CLI change (`mago analyze`/`lint` → likely `mago check`). The refactor's core risk is type correctness — PHPStan's home turf — so PHPStan stays authoritative. Revisit trigger: mago 2.0 ships (stable analyzer, semver'd Rust API, LSP), or a time-boxed spike shows `mago analyze` is an acceptable near-superset of PHPStan with controlled false positives. |
| Test strategy | **Hermetic unit tests** | Cover the pure surface with fixture tests; use test-only `Client` doubles for `send()` (no production seam); gate the live integration suite behind an env token. |
| Namespace | Keep `Fancourier\` | Renaming is pure churn; PSR-4 root stays `src/Fancourier/`. |
| Composer package name | `<your-org>/fancourier-api` | TBD (see §12). |

### Breaking-change policy (single 2.0.0)

Backwards compatibility is **not** preserved. The library is refactored freely and released as
**`2.0.0`**. To keep migration tractable:

- **One break, one release.** All breaking changes land together in `2.0.0`; the public API is
  frozen again after it. No rolling breaks across releases.
- **Every break is documented** in `MIGRATION.md` (deterministic mapping tables + before/after
  snippets), not discovered by the consumer.
- **Every mechanical break is codemoddable** via the `rector.php` ruleset, so consumers run a
  command instead of hand-editing. `MIGRATION.md` is the fallback for the rest.
- **Semantic (behaviour) changes** are listed separately from renames, because a codemod cannot
  verify intent; each consumer's own test suite is the acceptance gate.
- Allowed breaks this pass: full type declarations + `strict_types`, identifier renames, removal
  of dead public methods, `Client` naming normalisation, and deduplication of identical
  Request/Response pairs. Code lists ship **additively** as constants + enum classes (no forced
  enum-only cutover; setters accept `string|BackedEnum` where practical).

This supersedes the earlier "preserve public API" decision. It unlocks the modernization in
Phase 3b and removes the BC constraints noted throughout §5–§9.

---

## 2. Current-state assessment

### 2.1 Repository

- Git remote `origin` = `https://github.com/daika7ana/fancourier-api-v2.git` — already a fork
  of `shusaura85/fancourier-api`.
- Branch `main` at `204ec7b`; remote branches `main`, `fix/pudo_missing_data`.
- **Untracked files:** `AGENTS.md`, `pint.json` (the user added `pint.json` but never ran it).
- No `composer.lock` committed; no Composer scripts. CI is legacy `.travis.yml` only (PHP 8.1,
  `composer update` then `vendor/bin/phpunit`). No GitHub Actions.

### 2.2 Size & layout

| Area | Count | Notes |
|---|---|---|
| `src/Fancourier/` PHP files | 79 | facade, `Auth`, `Client`, `Request/*`, `Response/*`, `Objects/*`, `src/autoload.php` |
| `examples/*.php` | 29 | de-facto API documentation |
| `tests/` | 1 | `FancourierTest.php`, live integration only |
| **Total PHP files** | **109** | |

### 2.3 Runtime / version truth

`composer.json` declares `"php": ">=7.0"`, but the code already requires newer:

- **7.1+**: nullable type `?string` at `src/Fancourier/Client.php:23`.
- **7.3+**: `array_key_first()` at `Response/GetPudo.php:56`; `array_key_last()` at
  `Objects/CourierOrderTracker.php:48` and `Objects/AwbTracker.php:107`.
- **8.1 (runtime API)**: `new \CURLStringFile(...)` at `Client.php:129` inside
  `prepare_file_string()`. This is the only genuine 8.x-only API. Targeting 8.3 makes it
  valid with no guard.
- **No PHP 8-only syntax exists** anywhere (0 nullsafe, 0 `match`, 0 enums, 0 readonly,
  0 attributes, 0 `str_contains` family, 0 constructor promotion, 0 union types in code,
  0 named args, 0 first-class callables, 0 `array_is_list`).

Modernisation headroom (as of the current code):

- **0 typed properties**; **471 untyped property declarations** (Request 153, Objects 244,
  Response 47, root classes 27) — the largest single modernisation surface.
- **0** arrow-function candidates (no anonymous closures exist at all).
- **0** `array()` literals (already `[]`).
- **0** `??=` collapse opportunities; `??` is already used ~60 times.
- **0** `declare(strict_types=1)` — absent from all 109 files.
- No removed 7.x constructs (`each`, `create_function`, curly-brace offsets, `list()`,
  reversed `implode`) — all zero.

### 2.4 Extensions

- Declared: `ext-curl`, `ext-json`.
- **Undeclared but used: `ext-fileinfo`** — `mime_content_type()` at `Client.php:116`.
- No `mb_*`, `openssl_*`, `preg_*`, `intl`, `bcmath`, `gd`, `sodium`, `pdo_*` usage.

### 2.5 Testability

The only test file runs against **live `https://api.fancourier.ro/`**, creates/deletes **real
AWBs**, and depends on hardcoded AWB numbers (`2347300120337`, `2347300120340`) that live on a
shared test account. A green run is not meaningful coverage; the suite cannot be used as a
regression net during refactoring. This is the single biggest blocker and is addressed in Phase 1.

### 2.6 Architecture (where changes land)

Execution flow:
`Fancourier` facade method → `send()` → request object (`AbstractRequest::send()`) →
`Client` (cURL) → response object (`Response\Generic` + subclass `setData()`).

- Requests extend `AbstractRequest`, set `$gateway` + `$method`, implement `pack()`.
- `$method` accepts non-standard `POSTPUT` / `POSTDELETE`, routed through `Client::post_ma()`
  because cURL mishandles nested arrays in `CURLOPT_POSTFIELDS`.
- Responses extend `Response\Generic`, parse raw JSON in `setData()`, add typed getters.
- `Objects/*` are mutable fluent data holders.
- `Client` owns cURL, SSL verification, timeouts, headers; `AbstractRequest` caches
  verify/timeout overrides in `$clientOverrides`.
- `Auth` handles the bearer token (24h lifetime).
- `src/autoload.php` is a hand-rolled `spl_autoload_register` loader, **still used** by
  `examples/_init.php:3` and documented for non-Composer usage (`README.md:42-44`). It is
  redundant for Composer users (PSR-4) but not dead.

---

## 3. Target state

1. **PHP 8.3** baseline, correctly declared, with `ext-fileinfo` required.
2. **Hermetic test suite** for the pure surface (`pack()`, `setData()`, getters) plus a test-only
   fake `Client` double so request `send()` behaviour is unit-testable; live tests opt-in only.
3. **PHPStan ^2** in CI at the highest level green on current code, with no committed baseline,
   ratcheted upward as types land (Phase 3b).
4. **GitHub Actions** matrix across supported PHP versions; Travis removed.
5. **Zero known defects** from the §7 inventory, each covered by a regression test.
6. **Dead code removed**, comment/docblock typos fixed, README/examples accurate.
7. **Modernised public API (breaking, 2.0.0)** — full PHP 8.3 types and `strict_types`, clean
   identifiers, backed enums; every break captured in `MIGRATION.md` and codemoddable via Rector.
8. `pint` (`per` preset) applied in one isolated, reviewable commit.
9. Composer distribution with a lockfile for reproducible installs.

Non-goals (this pass):

- A full rewrite of the `Client` (cURL) layer — only the bug fixes and naming normalisation.
- Behaviour redesign of the API surface beyond the correctness fixes in §7.
- Backwards compatibility with 1.x APIs — superseded by the `2.0.0` migration path.

---

## 4. Phase plan

Phases are ordered so that each one establishes the safety net or prerequisite for the next.
Phases 3–5 may be parallelised by directory (`Request/`, `Response/`, `Objects/`) **after**
Phase 1's tests land, provided write scopes do not overlap.

### Phase 0 — Fork & baseline (no source changes)

**Status: DONE** — `7d6d16a` (bootstrap 2.0.0 toolchain and hermetic test config), `b124564`
(refactor plan). Downstream-consumer enumeration was substituted by a fixture canary — see §12.

**Goal:** a forked, tracked, CI-covered starting point that builds and runs without source edits.

Tasks:

1. Commit the untracked `pint.json` and `AGENTS.md` (decide whether `AGENTS.md` stays repo-local).
2. Create the fork destination and push; keep `origin` pointed at upstream for cherry-picks and
   add a second remote for the fork.
3. Rename the Composer package to `<your-org>/fancourier-api` (namespace unchanged).
4. Add a GitHub Actions CI workflow that runs on the **untouched** code (§6.3). Expect it to be
   red on the integration suite; configure CI to run unit-only from the start (none yet → pass).
5. Decide the fate of `.travis.yml` (delete in Phase 0 or Phase 2).
6. Add a `.gitignore` review: ensure `build/`, `examples/examples_token.txt`, `vendor/` are ignored.
7. **Remove `composer.lock` from `.gitignore`** (it currently ignores it) so the fork can
   commit a lockfile.
8. Add `laravel/pint` as a **dev dependency**, and ensure the CI workflow does **not** run
   `pint --test` until Phase 5 (otherwise Phases 0–4 are red by construction).
9. **Enumerate downstream consumers** (promoted from §12.10): list the downstream repos/services
   depending on `shusaura85/fancourier-api`; grep each for usage (facade-only vs direct
   `Client`/`Request`/`Object`/subclass use); pick ONE canary service. This is both a Phase 0
   deliverable and exit criterion — the Phase 3b break list is frozen only after this enumeration.
   **Superseded:** the fork has no downstream consumers, so this was replaced by a fixture canary
   (`canary/consumer-v1`) exercised through `rector.php` in CI.
10. Freeze `main`; do all work on a `refactor` branch (or per-phase branches) with PRs.

**Deliverables:** fork remote, tracked formatter config, CI workflow, `composer.lock` policy,
`laravel/pint` dev dependency, fixture canary (`canary/consumer-v1`), `refactor` branch.

**Exit criteria:** CI workflow triggers and is green (unit-only) on unchanged source; the fixture
canary is migrated by `rector.php` in CI (no named downstream canary service — see the supersession
note above).

---

### Phase 1 — Make it testable (highest priority)

**Status: DONE** — `ed31435` (hermetic offline unit suite, fixtures, fake `Client`).

**Goal:** a regression net that does not require the live API, so later phases are safe.

Tasks:

1. **Pure-surface unit tests** (no network, no seam needed): exercise `Request::pack()` output for
   every request class and `Response::setData()` + getters for every response class using captured
   fixture JSON. This directly covers the majority of the §7 bugs.
2. **Test-only fake `Client`** so request `send()` can be exercised:
   - `AbstractRequest::send()` also calls `headers_add()`, `get_error()`, `set_put_request()`,
     `set_delete_request()` — none covered by a minimal transport interface — so an interface seam
     cannot be type-satisfied, and it would also ship snake_case names that Phase 3b renames one
     phase later.
   - Instead, tests use a **test-only fake `Client`** (anonymous/subclass, since `Client` is
     non-final with non-final public methods) or a test subclass of the request that assigns
     `$this->client`.
   - **No production interface is introduced in Phase 1.**
   - Note: the `Auth` token/error path remains untestable without refactoring
     (`Auth::retrieve_token()` constructs `Client` directly) and is covered only by the
     live/integration group for now.
3. **Move fixtures out of live tests:** add `tests/fixtures/*.json` with recorded API responses
   (sanitised of tokens/AWBs) to drive the response parser tests. Include a fixture matrix with
   **MISSING optional keys and API-FAILURE bodies** (not just happy-path responses), because typed
   properties make degraded payloads fragile (e.g. `Objects/Branch.php`, `Objects/CourierOrder.php`
   dimension getters).
4. **Gate live tests:** annotate the existing live assertions with `@group integration`; skip them
   unless an env var (e.g. `FANCOURIER_LIVE_TOKEN`) is present. Remove/repair the dead
   commented-out bulk-tracking test (or convert it to a documented pending test).
5. **`phpunit.xml.dist` schema migration:** update for PHPUnit 10+ (`backupStaticAttributes`, the
   `<filter><whitelist>` block, and the removed TAP logger). Note that a coverage baseline cannot be
   produced in CI while `coverage: none`.
6. Establish a coverage baseline for `src/Fancourier/` (locally; see the `coverage: none` caveat).

**Deliverables:** `tests/Unit/*`, `tests/fixtures/*` (including missing-key/failure fixtures),
updated `phpunit.xml.dist` (PHPUnit 10+ schema + default non-integration group).

**Exit criteria:** `phpunit --exclude-group integration` runs offline and green; coverage report
generated locally; at least one test exercises `send()` via a fake `Client` double.

---

### Phase 2 — Version floor, CI, static analysis

**Status: DONE** — `9baa0da` (retire Travis, enable PHPStan, require PHP 8.3), `fe9763a`
(describe the project as a fork), `7ac3d32` (PHPDoc annotations, unknown-method guard).

**Goal:** declared floor matches reality; tooling enforces the target.

Tasks:

1. `composer.json`: set `"php": "^8.3"`, add `ext-fileinfo`, bump dev deps to `phpunit ^12`
   (preferred at the 8.3+ floor), `phpstan ^2`, and add `laravel/pint ^1`. Reconsider
   `"minimum-stability": "dev"` + `"prefer-stable": true` — for a fork a committed
   `composer.lock` is now committable (Phase 0) and preferable for reproducibility.
2. Run PHPStan at the **highest level green on current code (likely 5)**, commit `phpstan.neon`,
   and commit **no baseline**. Ratchet the level upward in Phase 3b as types land. Do not attempt
   to clear thousands of pre-type errors up front. Exit criterion is **no new errors at the
   last-green level**.
3. Finalise CI matrix (§6.3) and remove `.travis.yml`.
4. Update `README.md` requirements (`PHP >= 7.0` → `>= 8.3`; correct the "future 8.1" note).
5. Apply 8.3-language wins where safe and BC-compatible:
   - `json_decode(..., JSON_THROW_ON_ERROR)` in place of the `json_decode` + `json_last_error()`
     dance (e.g. `Auth::retrieve_token()`, response parsers); Rector's `JsonThrowOnErrorRector`
     automates the `json_last_error()` pattern.
   - `#[\Override]` on genuinely overriding methods — this also acts as an API-drift guard.
   - Typed class constants and readonly classes where internal.
6. `declare(strict_types=1)` rollout is **deferred to Phase 3b**: declared repo-wide via Rector's
   `DeclareStrictTypesRector` after the Phase 3 fixes and green PHPStan, so latent `TypeError`s are
   already resolved. Correct semantics: `strict_types` is per **calling** file, so the
   consumer-facing break is the **declared parameter/return types**, not `strict_types` itself.

**Deliverables:** updated `composer.json` (+ lockfile), `phpstan.neon` (no baseline),
`.github/workflows/ci.yml`, updated README.

**Exit criteria:** CI green on 8.3/8.4/8.5 with PHPStan (at the last-green level, no baseline) +
unit tests.

---

### Phase 3 — Defect fixes

**Status: DONE** — `7379f35` (override sites + typed class constants), `21dbb16` (request getters,
`PrintAwb` size/html, external-tariff casing), `259d183` (object parsing defaults, NUE pack fields,
currency, acronym casing), `36fea35` (example fixes), `64bc9d2` (response error states, branch /
confirmation getters), `1d572d2` (ShippingSlip payment string getters), `48f78ef` (auth guard,
token refresh/retry, empty-response guard).

**Goal:** eliminate every known correctness bug, each with a regression test.

Work the inventory in §7. Ordering guidance: fix parser/getter bugs first (covered by Phase 1
fixture tests), then the `Client`/`AbstractRequest` control-flow bugs.

Rules:

- One commit (or small PR) per defect or per tightly-related cluster.
- Every fix adds a regression test that fails before and passes after.
- Correctness fixes keep the original method names where practical; any rename or signature change
  is breaking and must be recorded in `MIGRATION.md` (Phase 3b).
- For anything with an ambiguous intended behaviour, note it and, if unresolved, mark it in the
  code with a `// ponytail:`-style ceiling comment and in §12 — do not guess silently.

Additional Phase 3 scope:

- Fix the three newly-surfaced defects **#25–#27** (§7): hardcoded credentials / public
  `testInstance()`, `Auth::getToken()` returning `false` flowing into `'Bearer '.false`, and the
  unspecified token-expiry behaviour.
- Acronym-casing changes (`getUitCode`→`getUITCode`, `getOpodAwbNumber`→`getOPODAwbNumber`, and the
  examples' `SetUitCode`/`GetServices` "typos") are **BC-safe cosmetic cleanups** because PHP method
  names are case-insensitive — so they belong **here in Phase 3**, NOT in the 2.0 breaking
  migration/Rector set.

**Exit criteria:** all §7 defects fixed or explicitly deferred with a tracking note; every fix has
a test; `phpstan analyse` shows no new errors at the last-green level (no baseline).

---

### Phase 3b — Breaking modernization (target: 2.0.0)

**Status: DONE (code-complete).** Waves and their commits:

| Wave | Scope | Commit(s) |
|---|---|---|
| W0 | Toolchain + hermetic test config | `7d6d16a` |
| W1 | Drop dead public API, rename `Client`, add Rector codemod | `76badfa` |
| W2 | Native types across Request/Response/Objects | `9042fb1`, `9284f15` |
| W3 | Deduplicate Request/Response pairs and pagination | `02d420c` |
| W4 | Backed enums + `string\|Enum` setters | `4966106` |
| W5 | `declare(strict_types=1)` repo-wide | `838bf10` |
| — | PHPStan ratchet: level 5 → level 8 | `ec1e6ab`, `56067b2` |

Remaining for 2.0.0: tag the release in Phase 7 (Phases 4–6 still outstanding). PHPStan level 9
(≈474 errors) is deferred as a bounded follow-up. See §12 for the deviation log.

**Goal:** land the deliberate breaking changes in one coordinated major, with the migration path
shipped alongside.

Tasks:

1. **Types**: typed properties on all 471 untyped declarations; parameter + return types on public
   and internal methods; `declare(strict_types=1)` repo-wide (land after Phase 3 fixes so latent
   `TypeError`s are already resolved).
2. **Identifiers**: normalise `Client`'s snake_case public API (`set_verify`, `post_json`,
   `headers_add`, `get_error`) to camelCase; drop unused parameters (`getAwb($awbNo)`). (Acronym
   casing — `getOpodAwbNumber`, `getUitCode`/`setUitCode` — is BC-safe and handled in Phase 3.)
3. **Removals**: delete dead public methods now that breaking is allowed (§8.2b), e.g.
   `Auth::getClientUsername`/`getClientPassword`, unused getters,
   `Client::get_error_no`/`headers_reset`.
4. **Code lists (additive)**: ship constants + enum classes for
   payment/service-option/order-type/event/status values, using the value sets extracted in
   `API_GAP_ANALYSIS.md` (§4). Where practical, setters accept `string|BackedEnum`; do **not** force
   enum-only parameters — Rector cannot rewrite string literals passed as arguments, and
   dynamic/config consumers would break.
5. **Deduplication** (§9): prefer **trivial subclasses**
   (`class GetCostsExternal extends GetCosts {}`, `class DeleteCourierOrder extends DeleteAwb {}`)
   over merging/deleting; extract shared pagination/list/language bases; factor only the genuine
   `AwbIntern`/`AwbExtern` overlap. Do **not** merge `AwbIntern`/`AwbExtern` — they have different
   wire schemas.
6. **Rector ruleset**: add `rector/rector` + a `rector.php` set encoding every rename/signature
   change mechanically, so consumers run a codemod instead of hand-editing. The set must be applied
   to THIS repo's own `src/` as the mechanism for the renames — not only shipped as a consumer
   artifact — so the rules cannot go stale.
7. **`MIGRATION.md`**: document every break — deterministic mapping tables, before/after snippets,
   the Rector command, and a per-consumer verification checklist.
8. **`CHANGELOG.md`**: a `2.0.0` entry enumerating the breaking changes.

**Deliverables:** fully typed `src/`, `rector.php`, `MIGRATION.md`, `CHANGELOG.md`, updated examples
and README.

**Exit criteria:** `strict_types` on all files; PHPStan ratcheted upward from the last-green level;
unit suite green; `vendor/bin/rector --dry-run` reports no remaining changes on a migrated fixture;
a **canary consumer compiles and passes its own test suite using the Rector output**; every entry in
`CHANGELOG.md`'s breaking section appears in `MIGRATION.md`.

---

### Phase 4 — Dead code & documentation cleanups

**Status: NOT STARTED.**

**Goal:** remove genuine dead weight (breaking public-method removals are handled in Phase 3b).

Tasks:

1. Remove commented-out code and stubs listed in §8.1 (they are not part of the public API).
2. Remove confirmed-dead **private/protected** helpers and properties (§8.2). Public-method
   removals belong to Phase 3b (§8.2b).
3. Fix comment/docblock/README typos and the incorrect `@return` docblocks (§8.3, §8.4).
4. Repair examples and README code that call nonexistent methods (§8.4) — these are user-facing
   bugs even though they are documentation.
5. Update `AGENTS.md` to reflect the new test/CI reality.
6. Delete or update `docs/Classes overview.md` entries naming nonexistent classes.

**Exit criteria:** no commented-out code blocks remain; `pint` clean; docs match code; no public
symbols removed in THIS phase (Phase 3b owns public removals).

---

### Phase 5 — Apply `pint`

**Status: NOT STARTED.**

**Goal:** normalise formatting in one isolated, reviewable commit.

Tasks:

1. Run `pint` **after** Phase 1 tests exist, as its own commit, so behaviour is not conflated with
   formatting.
2. **Manually review** the non-cosmetic rules in `pint.json`:
   - `protected_to_private` — changes visibility; confirm no internal subclass relies on
     `protected`. (Breaking changes are allowed, but still verify no runtime reliance.)
   - `ordered_class_elements` — reorders members; verify constants/properties/methods keep
     intended order and nothing depends on declaration order.
   - `array_push` — rewrites `array_push($a, $x)` to `$a[] = $x`.
3. Confirm tests still pass after the formatting commit.

**Exit criteria:** `pint --test` clean; formatting diff reviewed and behaviour unchanged.

---

### Phase 6 — Examples & docs polish

**Status: DONE.**

- `src/autoload.php` is **kept**: non-Composer/manual usage remains supported, so the README
  "Manual" section stays.
- `examples/_init.php` is now **CWD-independent**: it resolves every path from `__DIR__`, prefers
  `vendor/autoload.php` when Composer is present and falls back to `src/autoload.php`.
- `examples/getPudo.php` and `examples/getPudoDetails.php` were **merged** into a single
  `examples/getPudo.php` that demonstrates both `setType(...)` + `getAll()` and `setId(...)` + `get()`.

**Goal:** the de-facto docs (examples) and README are correct and runnable.

Tasks:

1. Fix the example/README defects in §8.4 (nonexistent methods, wrong case, off-by-one pagination,
   typos).
2. Fix `examples/_init.php` CWD-relative path fragility (`../src/autoload.php`) or document the
   required working directory. Decide whether examples should use `vendor/autoload.php` for
   Composer users.
3. Decide the fate of `src/autoload.php`: keep (manual usage supported) or retire (and drop the
   README "Manual" section). If retired, update `examples/_init.php` accordingly.
4. Consider consolidating near-duplicate examples (`getPudo.php` vs `getPudoDetails.php`).

**Exit criteria:** every example runs from its documented directory; README matches the code.

---

### Phase 7 — Release (2.0.0)

**Status: NOT STARTED** — blocked on Phases 4–6.

Tasks: tag **`2.0.0`**, finalize `CHANGELOG.md` and `MIGRATION.md`, publish the Rector set and the
package to the Composer repository (path/VCS), document the install command, the PHP 8.3
requirement, and the migration command.

**Exit criteria:** a consuming service migrates using only `MIGRATION.md` + the Rector command and
passes its own test suite.

---

## 5. Verification strategy

| Layer | Command | Runs in CI |
|---|---|---|
| Unit (hermetic) | `vendor/bin/phpunit --exclude-group integration` | Yes, all matrix jobs |
| Integration (live) | `FANCOURIER_LIVE_TOKEN=… vendor/bin/phpunit --group integration` | No (manual/opt-in) |
| Static analysis | `vendor/bin/phpstan analyse` | Yes |
| Formatting | `pint --test` | Yes |
| Composer validity | `composer validate --strict` | Yes |
| Syntax (all files) | `php -l` over `src/`, `examples/`, `tests/` | Yes |

Per-phase evidence requirement: Phase 1 establishes the offline suite; Phases 2–6 must each end
with the CI table above green and PHPStan showing no new errors at the last-green level.

---

## 6. Configuration artifacts

### 6.1 `composer.json` (target shape)

```json
{
    "name": "<your-org>/fancourier-api",
    "description": "Fork of the FanCourier API v2.0 client library",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "ext-curl": "*",
        "ext-json": "*",
        "ext-fileinfo": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^12",
        "phpstan/phpstan": "^2",
        "laravel/pint": "^1"
    },
    "autoload": {
        "psr-4": { "Fancourier\\": "src/Fancourier/" }
    },
    "autoload-dev": {
        "psr-4": { "Fancourier\\Tests\\": "tests/" }
    }
}
```

Notes: retain MIT + original attribution (see §10); drop `minimum-stability: dev` /
`prefer-stable` and commit a `composer.lock` for reproducible installs. Decide whether
`src/autoload.php` remains (it is not a Composer concern either way).

### 6.2 `phpstan.neon` (target shape)

```neon
parameters:
    level: 5
    paths:
        - src
    excludePaths:
        - src/autoload.php
    # baseline: phpstan-baseline.neon   # only if the first run is genuinely noisy
```

Start at the highest level that is green on today's untyped code (likely 5); this is **not** set to
9 up front. The level is **ratcheted upward toward 9 during Phase 3b** as property/parameter/return
types land — with ~470 untyped properties, level 9 today produces thousands of errors. No committed
baseline. The commented baseline line stays only as an escape hatch, never as a substitute for
fixing real issues.

### 6.3 `.github/workflows/ci.yml` (target shape)

```yaml
name: CI
on:
  push:
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ['8.3', '8.4', '8.5']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: curl, json, fileinfo
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: composer validate --strict
      - run: vendor/bin/phpstan analyse --no-progress
      - run: vendor/bin/phpunit --exclude-group integration
      - if: matrix.php == '8.3'
        run: vendor/bin/pint --test   # enable only from Phase 5 (Phases 0–4 are red by construction)
```

(If the fork lives on Gitea, translate to Gitea Actions — the steps are identical.)

### 6.4 `phpunit.xml.dist` (target additions)

- Define a default test suite that excludes the `integration` group.
- Keep coverage output under `build/` (already gitignored).
- Add `FANCOURIER_LIVE_TOKEN` as an opt-in env var gating `@group integration`.
- Migrate the schema for PHPUnit 10+ (`backupStaticAttributes`, `<filter><whitelist>`, removed TAP
  logger). Coverage cannot be collected in CI while `coverage: none`.

### 6.5 Test doubles for `Client` (no production seam)

No production interface is introduced. `AbstractRequest::send()` depends on more than the HTTP
verbs (`headers_add()`, `get_error()`, `set_put_request()`, `set_delete_request()`), so a minimal
transport interface cannot be type-satisfied, and it would carry snake_case names that Phase 3b
renames a phase later.

Tests instead use a **test-only fake `Client`** — `Client` is non-final with non-final public
methods, so an anonymous subclass or a small test double can override `get`/`post`/`post_ma`/
`post_json` — or a test subclass of the request that assigns `$this->client` directly.

`Auth` constructs `Client` internally, so its token/error path stays untestable without refactoring
and is covered only by the live/integration group for now.

---

## 7. Defect inventory

All findings verified during reconnaissance. "Test" indicates the Phase-1 test type that should
guard the fix: **U** = pure unit fixture test, **T** = fake-`Client` test.

| # | Location(s) | Problem | Fix | Test |
|---|---|---|---|---|
| 1 | `Request/GetCitiesExternal.php:108-111`, `GetCourierOrders.php:93-96`, `GetShippingSlip.php:84-87`, `GetBankTransfers.php:84-87`, `GetStreets.php:108-111` | `getPerPage()` returns `$this->page` instead of `$this->perPage` | Return the correct property | U |
| 2 | `Request/PrintAwb.php:188-191` | `getSize()` returns `$this->lang` | Return `$this->size` | U |
| 3 | `Request/CreateCourierOrder.php:283-286` | `getPickupDate()` returns `$this->notes` | Return the pickup-date field | U |
| 4 | `Request/CreateCourierOrder.php:113-116` | `getAwb($awbNo)` declares a required param it never uses | Remove the unused param **only if BC-safe**; otherwise keep and document | U |
| 5 | `Objects/AwbIntern.php:1156-1163` | `getIsValueUnderThreshold()` inverts its guard → throws when the value *is* set | Correct the guard | U |
| 6 | `Objects/AwbIntern.php:174-180` (+ `:79` `NUE_company` no default) | Non-EU `pack()` unconditionally writes `''` into `countryCode`/`vatId` from `NUE_*` fields instead of using stored values | Use the stored `NUE_countryCode`/`NUE_vatId`; default `NUE_company` | U |
| 7 | `Objects/CourierOrder.php:85-98` | `getHeight()/getLength()/getWidth(): float` default to `[]` → `TypeError` when dimensions absent | Default to `0.0`/nullable per intended semantics | U |
| 8 | `Response/GetBranches.php:50-53` | `get($id): array` returns `false` on miss → `TypeError` | Return `[]` or make the return nullable | U |
| 9 | `Response/GetAwbConfirmations.php:37-45` | `getRAWbytes(): string` can return `null`; `getLength(): string` returns `strlen()` (int) | Correct return types/values | U |
| 10 | `Objects/Pudo.php:76-79` | `getAddress(): array` fallback is `''` → `TypeError` | Fallback to `[]` (consistent with siblings) | U |
| 11 | `Client.php:231-234` | `post_json()` treats an empty successful response body as a failure (returns `false`). `curl_close()` is a no-op since PHP 8.0, so "close then `curl_error()`" is not a reproducible bug | Decide the intended behaviour for an empty success body | T |
| 12 | `Request/AbstractRequest.php:87-118` | `$responseString` is undefined when `$method` is none of the handled values (no `else`) | Add a default branch that raises `DomainException` | T |
| 13 | `Request/PrintAwb.php:98-103` | `setHtml($active)` ignores `$active` and always disables | Honour the argument | U |
| 14 | `examples/getShippingSlip.php:39-58` | Loop bound `getCurrentPage() <= getTotalPages()` is off-by-one; `setPage()` called twice (`:48-49`, `:54-55`) | Fix bound and remove the duplicate call | manual |
| 15 | `Objects/AwbExtern.php:67`, comment `:106` | `$currency` has a getter but is never packed (the pack line is commented out) | Decide whether currency must be sent; wire it or remove the getter | U |
| 16 | `Objects/AwbIntern.php:85-89`, `Objects/AwbExtern.php:77-81` | `isValid()` is a commented-out stub, yet advertised in `examples/create_awb*.php:79` | Implement, or remove from examples and mark unsupported | U |
| 25 | `Fancourier.php:39-41,359` | Hardcoded test credentials (`Fancourier::TEST_CLIENT_ID`/`TEST_USERNAME`/`TEST_PASSWORD`) and public `testInstance()` ship in the production path | Decide removal for 2.0 (breaking) | manual |
| 26 | `Request/AbstractRequest.php:85` | `Auth::getToken()` returns `false` on failure, but `send()` sends `'Bearer '.false` → an unauthenticated request with an opaque error | Add an explicit auth-failure guard | T |
| 27 | `Auth.php` (token path) | Token-expiry handling is not specified (API_GAP §6.4) | Decide behaviour when the API reports an expired token | manual |

Also flagged, lower confidence (confirm during Phase 3):

- `Request/GetCosts.php:24` — `$reimbursementPaymentType` is a commented-out property, not a real one.
- `Objects/AwbTracker.php:12` — `$paymentDate` is assigned (`:32`) but has no getter.

---

## 8. Cleanup inventory

### 8.1 Commented-out code / stubs (safe to remove — not public API)

- `src/Fancourier/Fancourier.php:139-142` — commented `requestCourier()` referencing a nonexistent
  `RequestCourier` class, plus a `// todo implement` at `:141` (the only TODO in the repo).
- `tests/FancourierTest.php:127-140` — commented bulk-tracking test using nonexistent `TrackAwbBulk`
  and `getBody()`; also commented assertions at `:112`, `:124`. **Confirm intent** (might document a
  missing feature) before deleting.
- `src/Fancourier/Client.php:81, 85, 138, 172-177, 179, 213, 215` — commented debug `echo`/`curl_setopt`.
- `src/Fancourier/Response/GetBankTransfers.php:83-96` — commented `getCity()` method.
- Commented payload keys: `Objects/AwbExtern.php:105-106, 135`; `Objects/AwbIntern.php:138, 168`;
  `Request/CreateCourierOrder.php:104`.
- `src/Fancourier/Request/GetCosts.php:24` — commented property.
- `src/Fancourier/Client.php:334-337` — empty `__destruct()` stub (dead).

Sample-data comments (`CreateAwb.php:27-63`, `CreateAwbExternal.php:27-37`, and the large trailing
sample blocks in `AwbTracker.php:122-216`, `BankTransfer.php:117-185`, `Country.php:55-113`,
`CourierOrder.php:133-202`, `ShippingSlip.php:186-272`, `AwbIntern.php`/`AwbExtern.php` payloads)
are documentation, not dead code — decide separately whether to move them to `docs/`.

### 8.2 Confirmed dead internal helpers / properties

Private/protected and package-internal only — safe to remove. **Public "unused" methods are NOT
listed here** because §1 preserves them.

- `src/Fancourier/Client.php:8` — `$error_no` is only consumed by the unused `get_error_no()`.
- `src/Fancourier/Objects/AwbIntern.php:77-78` — `NUE_countryCode`/`NUE_vatId` are overwritten in
  `pack()` (see defect #6); resolve alongside the fix.
- `tests/FancourierTest.php:6` — unused import `use Fancourier\Objects\AWBIntern;` (test uses the
  FQN `\Fancourier\Objects\AwbIntern` instead).

### 8.2b Public methods declared but unused in-repo — **KEEP (BC)**, listed only for awareness

`Auth::getClientUsername():36`, `Auth::getClientPassword():37`, `Objects/Pudo.php:101`
`getHighDemand()`, `:106` `getPaymentMethods()`, `Objects/Street.php:55` `hasZipCode()`,
`Objects/AwbIntern.php:769/877/886/1178/1196/1214` getters, `Request/PrintAwb.php:88/98/108/127/146/165`
getters, `Request/CreateCourierOrder.php:265` `getOrderType()`, `Client.php:265` `get_error_no()`,
`:295` `headers_reset()`, facade `setTimeout():77`, `getServices():147`, `getCountries():226`,
`getTokenExpiresAt():346`.

If the team confirms there are **no** external consumers of a specific method, it can be deprecated
and removed in a future major — but that is a deliberate, documented decision, not a cleanup.

### 8.3 Typos — public identifiers (preserve; optionally add aliases)

- `Objects/AwbTracker.php:64` — `getOpodAwbNumber()` vs property `oPODAwbNumber` (inconsistent acronym
  casing). Called from `examples/trackAwb.php`.
- `Objects/AwbIntern.php:587/596` and `Objects/AwbExtern.php:595/604` — `getUitCode()/setUitCode()`
  (acronym casing). Examples call `SetUitCode`.

Recommendation: keep the existing names; optionally add correctly-cased aliases. Do not rename.

### 8.4 Typos — comments, docblocks, examples, README (safe to fix)

- `Response/GetCourierOrders.php:81` — "consistentcy" → "consistency".
- `examples/create_awb.php:128`, `examples/create_awb_fanbox.php:113` — `seDropOffLocation` →
  `setDropOffLocation`.
- `examples/create_awb.php:103`, `examples/create_awb_extern.php:130` — `SetUitCode` → `setUitCode`.
- Method-call case in examples: `getServices.php:9` `GetServices`, `getCountries.php:9` `GetCountries`,
  `printAwb.php:25` `PrintAwb`.
- `tests/FancourierTest.php:6` and `README.md:139,161,204` — `AWBIntern` → `AwbIntern`.
- `README.md:25` — "Aditional" → "Additional"; `README.md:269` — "calls there PUDO" → "calls them PUDO".
- Wrong `@return` docblocks referencing nonexistent classes:
  - `GetRates` (nonexistent): `Request/GetStreets.php:60,78,97,115`, `Request/GetCourierOrders.php:82,100`,
    `Request/GetCitiesExternal.php:78,97,115`.
  - `GetBankTransfers`: `Request/GetShippingSlip.php:55,73,91`.
  - `GetCities`: `Request/GetCountiesExternal.php:41`, `Request/GetPudo.php:67`.
  - `TrackAwb`: `Request/GetCourierOrderEvents.php:48`, `Request/TrackCourierOrder.php:75`.
  - `Response\Generic` for `trackAwb`: `Fancourier.php:216` (actual is `Response\TrackAwb`).
- Examples/README calling nonexistent methods:
  - `examples/create_awb_extern.php:9` documents `->setCoD()` (real method: `setReimbursement`).
  - `examples/getShippingSlip.php:69` documents `->getInfo()` (no such method on `ShippingSlip`).
  - `examples/printAwb.php:36`, `README.md:311,334,356` call `$response->getAllErrors()` on a
    `PrintAwb` response, which has no such method → fatal on the error path.
- `docs/Classes overview.md:36` — `Objects\GetAwbConfirmations` (nonexistent); `:37` — `GetBankTransfer`
  (missing "s").

---

## 9. Duplication inventory (handled in Phase 3b)

Identical beyond class name:

- `Response/GetCosts.php` ≡ `Response/GetCostsExternal.php` (`setData()` + 8 getters) — diff clean.
- `Response/DeleteAwb.php` ≡ `Response/DeleteCourierOrder.php`.
- `Request/CreateAwb.php` ≈ `Request/CreateAwbExternal.php` (only `$gateway`, `use`, `addAwb()` type
  hint, and docblock differ).
- `pack() { return []; }`: `Request/GetCounties.php:18`, `GetCountries.php:18`, `GetServices.php:18`.

Structural duplication:

- Pagination request block duplicated verbatim (including the `getPerPage()` bug) in
  `Request/GetCitiesExternal.php`, `GetCourierOrders.php`, `GetBankTransfers.php`,
  `GetShippingSlip.php`, `GetStreets.php`.
- Pagination response block duplicated in the matching `Response/*` files.
- List add/set/reset pattern duplicated in `Request/CreateAwb.php`, `CreateAwbExternal.php`,
  `GetAwbConfirmations.php`, `TrackAwb.php`, `TrackCourierOrder.php`.
- Language handling duplicated in `TrackAwb.php`, `TrackCourierOrder.php`, `GetAwbEvents.php`,
  `GetCourierOrderEvents.php`.
- Near-duplicate data holders: `Objects/AwbIntern.php` (1294 lines) vs `Objects/AwbExtern.php`
  (1188 lines); `Request/CreateCourierOrder.php` setters mirror them.
- Near-duplicate example: `examples/getPudo.php` vs `examples/getPudoDetails.php` (only `setType` vs
  `setId`, `getAll` vs `get`).

---

## 10. Fork, licensing, attribution

- The project is MIT (`LICENSE`); retain the original copyright notice and add the fork
  copyright. MIT permits forking and relicensing terms only with attribution preserved.
- Keep the namespace `Fancourier\` (`README.md:379` mentions the package as open source under MIT).
- Composer package name should change to a distinct vendor to avoid collision with the upstream
  `shusaura85/fancourier-api` when both are installed in one project.
- Keep `origin` as upstream for selective cherry-picks; add the fork remote as `fork`.
- Consider whether to rename the repository itself (currently `fancourier-api-v2`) in the fork.

---

## 11. Risks & mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Live-only tests give no refactor safety net | High — silent regressions | Phase 1 hermetic suite is a hard prerequisite for Phases 3–5 |
| `pint` non-cosmetic rules (`protected_to_private`, `ordered_class_elements`) | Medium — visibility/order changes | Apply in Phase 5 as an isolated commit; manually review the diff against §1 |
| `minimum-stability: dev` + no lockfile | Medium — non-reproducible CI | Commit a `composer.lock` for the fork |
| Repo-wide `strict_types` surfaces many latent TypeErrors at once | Medium — large red CI | Declare it repo-wide in Phase 3b (via Rector) only after Phase 3 fixes and green PHPStan; the consumer-facing break is the declared parameter/return types, not `strict_types` itself |
| Breaking the public API breaks consumers | High | Single `2.0.0` + `MIGRATION.md` + Rector codemod; each consumer verifies with its own test suite before upgrading |
| Consumers not enumerated; the 2.0 break list is unvalidated | High — blind breaks | The fork has no downstream consumers: substituted by a fixture canary (`canary/consumer-v1`) proved against the `rector.php` output in CI. Break list is frozen. |
| An AI applying a prose migration guide mis-maps symbols | Medium | Keep mappings deterministic and codemod-backed; `MIGRATION.md` uses structured tables, not free prose |
| PHP 8.1 already EOL / 8.3 will age | Low | 8.3 chosen; revisit before 8.3 EOL (Dec 2026) |
| The one genuine 8.1 API (`CURLStringFile`) | Low | Valid on 8.3; no guard needed |
| Hardcoded live AWBs in tests belong to the shared account | Low | Move live tests behind `@group integration`; do not run repeatedly |
| `examples/_init.php` CWD-relative paths | Low | Fix or document in Phase 6 |

---

## 12. Open questions / decisions needed

1. **Composer vendor name** — `<your-org>/fancourier-api`? (needed in Phase 0.)
2. **CI host** — GitHub Actions assumed; confirm if the fork lives on Gitea and translate §6.3.
3. **`AGENTS.md`** — keep in the repo or move to local-only config?
4. **Retire `src/autoload.php`?** — only if non-Composer/manual usage is dropped; otherwise keep and
   fix `examples/_init.php`.
5. **`AwbExtern::$currency`** — should currency be sent in the payload? (defect #15)
6. **`isValid()`** — implement (defect #16) or formally drop it from examples as unsupported?
7. **`declare(strict_types=1)`** — **decided:** declared repo-wide in Phase 3b via Rector
   (`DeclareStrictTypesRector`) after the Phase 3 correctness fixes and green PHPStan, **not**
   per-directory. The consumer-facing break is the declared parameter/return types, not
   `strict_types` itself.
8. **PHPUnit ^11 vs ^12** for the 8.3-only dev floor.
9. **Commit `composer.lock`** for the fork? (recommended: yes.)
10. **Downstream consumers** — **RESOLVED:** there are none in this fork. Substituted by a fixture
    canary (`canary/consumer-v1`) migrated through `rector.php` in CI; the break list is frozen.
11. **Rector set scope** — **RESOLVED:** the shipped `rector.php` is rename-only for consumers;
    `rector-internal.php` (strict types) is repo-only and not shipped.
12. **`Client` public API target names** — **RESOLVED:** confirmed as the camelCase names listed in
    `MIGRATION.md` §3.1.

**Resolved since drafting (2026-10):**

- **Toolchain:** PHPStan ^2 + Pint + GitHub Actions — locked. **mago** evaluated and deliberately
  placed on hold (revisit at 2.0). See §1. Rationale: greenfield repo with no inherited baseline,
  so there is no migration cost either way; PHPStan wins on analyzer maturity for the type-heavy
  modernization, and mago's analyzer is explicitly not a parity replacement pre-2.0.
- **PHP floor:** re-affirmed as `^8.3` for this pass, with a floor bump **explicitly scheduled**
  for the ~Dec 2026 security-support end (see §1). Not bumped to 8.4 here.
- **`strict_types` rollout:** decided — declared repo-wide in Phase 3b via Rector after the Phase 3
  fixes; the consumer-facing break is the declared types (see §12.7).

**Phase 3b outcome — deviations from the seed doc (2026-10):**

- **Canary consumer substituted by a fixture canary.** This fork has no downstream consumers to
  enumerate, so the Phase 0/3b canary exit criterion is satisfied by a fixture consumer
  (`canary/consumer-v1`) run through `rector.php` in CI. No real canary service exists.
- **PHPStan ratcheted to level 8**, not the "likely level 5" the seed expected. Level 9 currently
  reports ≈474 errors and is deferred as a bounded follow-up; the level-8 run is green with no
  committed baseline (`phpstan.neon` keeps the commented escape hatch only).
- **"Internal fork" wording superseded** — the project is described as a public fork of
  `shusaura85/fancourier-api` (`fe9763a`), and `.travis.yml` was retired for GitHub Actions
  (`9baa0da`).
- **PHPUnit ^12** was chosen for the 8.3-only dev floor (resolves open question 8).
- **Rector scope decided** (resolves open question 11): the shipped `rector.php` is rename-only for
  consumers; `rector-internal.php` carries `DeclareStrictTypesRector` and is repo-only, not shipped.
- **`Client` camelCase target names confirmed** (resolves open question 12) — see `MIGRATION.md` §3.1.
- **Removed, not renamed:** `Client::get_error_no()` / `headers_reset()` were dead and deleted in
  2.0 (the seed listed them under renames); acronym casing (`getUitCode`→`getUITCode`,
  `getOpodAwbNumber`→`getOPODAwbNumber`) was applied in Phase 3 as BC-safe cosmetic cleanup.

---

## Appendix A — Phase 1 test targets (pure surface)

Requests with `pack()` to cover: `CreateAwb`, `CreateAwbExternal`, `PrintAwb`, `DeleteAwb`,
`GetCosts`, `GetCostsExternal`, `GetServices`, `GetServiceOptions`, `GetCounties`, `GetCities`,
`GetStreets`, `GetPudo`, `GetShippingSlip`, `GetAwbEvents`, `TrackAwb`, `GetCountries`,
`GetCountiesExternal`, `GetCitiesExternal`, `CreateCourierOrder`, `DeleteCourierOrder`,
`GetCourierOrders`, `GetCourierOrderEvents`, `TrackCourierOrder`, `GetBankTransfers`,
`GetBranches`, `GetAwbConfirmations`.

Responses with `setData()` + getters to cover: the matching `Response/*` classes, with fixtures
for `GetCosts`, `GetCities`, `GetStreets`, `GetCounties`, `GetCountries`, `GetBranches`, `GetPudo`,
`TrackAwb`, `TrackCourierOrder`, `GetAwbEvents`, `GetAwbConfirmations`, `GetBankTransfers`,
`GetShippingSlip`, `GetCourierOrders`, `GetCourierOrderEvents`, `GetServices`, `GetServiceOptions`,
`CreateAwb`, `CreateAwbExternal`, `CreateCourierOrder`, `DeleteAwb`, `DeleteCourierOrder`,
`PrintAwb`.

Objects to cover via `pack()`/getters: `AwbIntern`, `AwbExtern`, `AwbTracker`, `CourierOrder`,
`CourierOrderTracker`, `Pudo`, `Street`, `City`, `CityExternal`, `County`, `CountyExternal`,
`Country`, `Service`, `ServiceOption`, `BankTransfer`, `Branch`, `ShippingSlip`,
`AwbEvent`, `CourierOrderEvent`.

---

## Appendix B — Reconnaissance sources

Findings in §7–§9 come from two read-only audits performed against commit `204ec7b`:
a dead-code/typo audit and a PHP-version/modernisation audit, both reconciling the full
`src/`, `examples/`, `tests/`, `README.md`, `docs/`, `composer.json`, and `pint.json`.
Line numbers are valid at that commit and must be re-confirmed after any earlier phase shifts them.

Additional sources: the official API spec `RO_FANCourier_API_130825.pdf` (v2.0, Septembrie 2025,
61 pages) and a code-side API-surface inventory. Their reconciliation lives in
**`API_GAP_ANALYSIS.md`**.

---

## Appendix C — API specification alignment

The official spec was extracted and diffed against the code (see `API_GAP_ANALYSIS.md`).

**Headline result: all 28 documented endpoints are implemented** (26 Request classes + `Auth`
login). No missing endpoints and no undocumented endpoints in the facade. The gaps are at the
field/enum level and in error handling.

New defects surfaced by the spec comparison — add these to **Phase 3** (all BC-safe fixes):

| # | Location | Problem | Fix |
|---|---|---|---|
| 17 | `Objects/Branch.php` | Reads `address.zipCode`, but `/reports/branches` returns `zipcode` (lowercase `c`) → postal code always `null` | Parse both keys; confirm against live response |
| 18 | `Objects/AwbExtern.php:68` | Default `payment` is `'sender'`; spec/other code use `'expeditor'` | Default to `AbstractRequest::TYPE_SENDER` |
| 19 | `Request/GetCostsExternal.php:23` | Default `deliveryMode` is `'Rutier'`; spec is `rutier` (file itself uses lowercase at `:89`) | Normalise to `'rutier'` |
| 20 | `Response/CreateCourierOrder.php` | No typed getters despite spec returning `data.id` | Add `getId()` (additive) |
| 21 | `Objects/AwbTracker.php:12` | `paymentDate` parsed but has no getter | Add `getPaymentDate()` (additive) |
| 22 | `Response/Generic.php` + all `Response::setData()` | `isOk()` keys off error fields; API-level `status:"fail"` bodies may not set them → `isOk()` can report success on failure | Set `errorMessage` on `status !== 'success'`; add error-fixture test matrix |
| 23 | `Objects/AwbExtern.php:106` | `$currency` has setter/getter but is never emitted (commented) | Decide: send or remove (BC-safe) |
| 24 | `AbstractRequest.php` constants | No `Altul` payment value; no constants for documented option/order-type/service/event code lists | Add additive constants |

Undocumented code surface to keep but flag (not in the spec): NUE tax fields on `/intern-awb`
(`isValueUnderThreshold`, `countryCode`, `vatId`, `company`), `info.awbNumber` on `/order`,
`info.currency` on `/intern-awb`, `transactionType`, `info.status`, `info.awbs`.

Spec gaps to note (not code defects): no error schema/HTTP status table, no
`/reports/counties` or `/reports/countries` response schema, no county/country lists, no
sandbox host, and internal inconsistencies (`parcel`/`envelope` vs `packages.*`,
`contentType` vs `documentType`, `zipCode` vs `zipcode`, `POST` vs "GET" on `/extern-awb`).

Live confirmation required for defects #17, and the undocumented fields: run against the test
account and capture fixtures (which double as the Phase-1 repository fixtures).
