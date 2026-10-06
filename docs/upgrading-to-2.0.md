# Upgrading to 2.0

2.0 is a **breaking major** release. The authoritative, complete migration guide — every
rename, removal and behaviour change, the Rector codemod, and a per-consumer checklist —
lives in [`../MIGRATION.md`](../MIGRATION.md). Start there.

## What changed at a glance

- Package renamed to `daika7ana/fancourier-api`; PHP floor `^8.3`; now requires
  `ext-fileinfo`; namespace `Fancourier\` is unchanged.
- The public API is fully typed, and `declare(strict_types=1)` is on repo-wide.
- `Fancourier\Client` methods were renamed snake_case → camelCase
  (`set_verify()` → `setVerify()`, `post_json()` → `postJson()`, …) and are codemoddable
  via `rector.php`.
- Dead methods were removed: `Client::get_error_no()`, `Client::headers_reset()`,
  `Auth::getClientUsername()`, `Auth::getClientPassword()`, and `Fancourier::testInstance()`
  with the `Fancourier::TEST_*` constants.
- Code lists ship as backed enums in `Fancourier\Enums\`; adopting the enums is optional
  (string call sites keep working, and setters accept `string|Enum`).

## Where to go next

- [`../MIGRATION.md`](../MIGRATION.md) — full break list, symbol mappings, behaviour fixes,
  the Rector command, and the migration checklist.
- [`../CHANGELOG.md`](../CHANGELOG.md) — the human-readable `[2.0.0]` entry.
- [Getting started](getting-started.md) — the 2.0 usage flow.
