# FAN Courier API v2.0 — Documentation

Documentation for the `daika7ana/fancourier-api` package v2.0 — a typed PHP client library
for the FAN Courier API v2.0 (JSON). This documentation set is grounded in the shipped
`src/Fancourier/` code; where the official FAN Courier specification is silent the text
says so explicitly instead of guessing.

> New here? Start with [Getting started](getting-started.md), then browse the
> [endpoint reference](endpoints.md).

## Documentation map

| Document | Contents |
|---|---|
| [Getting started](getting-started.md) | Requirements, installation, authentication/token lifecycle, client configuration, and the request/response execution flow. |
| [Endpoint reference](endpoints.md) | Every facade method with its request class, gateway, HTTP method, input setters, response class, response getters, and the FAN Courier docs ↔ PHP class map. |
| [Objects reference](objects.md) | The `Fancourier\Objects\*` data holders (`AwbIntern`, `AwbExtern`, `Pudo`, …) and their getters/setters. |
| [Enums and code lists](enums.md) | The string-backed enums in `Fancourier\Enums\` and the parallel code-list constants on `AbstractRequest`. |
| [Error handling](errors.md) | `isOk()`, `getErrorCode()` / `getErrorMessage()`, `status:"fail"` bodies, empty-body failures, and auth failures. |
| [Upgrading to 2.0](upgrading-to-2.0.md) | Pointer to the full 1.x → 2.0 migration guide and the high-level break list. |

Related material outside this folder:

- [`../README.md`](../README.md) — package-level readme and worked usage snippets.
- [`../MIGRATION.md`](../MIGRATION.md) — the authoritative 1.x → 2.0 migration guide.
- [`../examples/`](../examples) — runnable scripts for every endpoint (`_init.php` bootstraps them).
- [`../CHANGELOG.md`](../CHANGELOG.md) — release history.

## At a glance

```php
require __DIR__ . '/vendor/autoload.php';

use Fancourier\Fancourier;
use Fancourier\Request\GetCosts;

$fan = new Fancourier(
    getenv('FANCOURIER_CLIENT_ID'),
    getenv('FANCOURIER_USERNAME'),
    getenv('FANCOURIER_PASSWORD'),
    '', // bearer token: empty = fetch automatically on first request
);

$request = (new GetCosts())
    ->setParcels(1)
    ->setWeight(1)
    ->setCounty('Arad')
    ->setCity('Aciuta')
    ->setDeclaredValue(125);

$response = $fan->getCosts($request);

if ($response->isOk()) {
    echo $response->getCostTotal();
} else {
    echo $response->getErrorMessage();
}
```

## Conventions used in these docs

- Namespace root is `Fancourier\`; request classes live in `Fancourier\Request\`,
  response classes in `Fancourier\Response\`, data holders in `Fancourier\Objects\`,
  enums in `Fancourier\Enums\`.
- Method names are reproduced exactly as they appear in the 2.0 source, including
  acronym casing (`getUITCode`, `getOPODAwbNumber`, `getRAWbytes`).
- All classes declare `strict_types`, and the public API is fully typed. Setters return
  `static` for fluent chaining.
- "Gateway" is the path appended to `Fancourier::API_URL` (`https://api.fancourier.ro/`).
