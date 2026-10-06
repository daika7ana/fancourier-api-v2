# FAN Courier API v2 — PHP client

[![CI](https://github.com/daika7ana/fancourier-api-v2/actions/workflows/ci.yml/badge.svg)](https://github.com/daika7ana/fancourier-api-v2/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/php-%5E8.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](https://phpstan.org/)
[![License](https://img.shields.io/badge/license-MIT-blue)](./LICENSE)

> Ship with FAN Courier without hand-rolling cURL.

A sharp, fully-typed PHP 8.3 client for the **FAN Courier API v2.0**. Every documented
endpoint, real response objects instead of loose arrays, string-backed enums instead of
magic strings, and a hermetic test suite you can actually trust.

```php
$fan = new Fancourier\Fancourier($clientId, $username, $password);

$request = (new Fancourier\Request\GetCosts())
    ->setParcels(1)
    ->setWeight(2)
    ->setCounty('Arad')
    ->setCity('Aciuta');

$cost = $fan->getCosts($request);
echo $cost->isOk() ? $cost->getCostTotal() : $cost->getErrorMessage();
```

## Why this client

- **Complete coverage** — all documented v2.0 endpoints, mapped 1:1 to the official spec.
- **Typed end to end** — typed requests, responses and data objects; `declare(strict_types=1)`
  repo-wide; **PHPStan level 8** with no baseline.
- **Enums, not magic strings** — every code list ships as a string-backed enum in
  `Fancourier\Enums\`. Adoption is optional: plain strings keep working.
- **Errors you can branch on** — every response exposes `isOk()`, `getErrorCode()` and
  `getErrorMessage()`, so a failed call never masquerades as success.
- **Batteries included** — 341 hermetic unit tests, an opt-in live suite, and a runnable
  example for every endpoint in [`examples/`](./examples/README.md).
- **A real upgrade path** — the 1.x → 2.0 renames are codemoddable via Rector, with the full
  break list in [`MIGRATION.md`](./MIGRATION.md).
- **Production ready** — opt-in retry/backoff (`RetryPolicy`), PSR-3 logging and a PSR-16
  token cache; secrets are never logged and non-idempotent `POST`s are not retried by default.

## Requirements

- **PHP ≥ 8.3**
- `ext-curl`, `ext-json`, `ext-fileinfo`

## Installation

```bash
composer require daika7ana/fancourier-api
```

That's it — the library is framework-agnostic and PSR-4 autoloaded under the `Fancourier\`
namespace.

## Authentication

The bearer token lives for 24 hours. Construct the client once, then let it fetch and cache a
token lazily; pass a previously cached token if you have one.

```php
use Fancourier\Fancourier;

$fan = new Fancourier(
    getenv('FANCOURIER_TEST_CLIENT_ID'),
    getenv('FANCOURIER_TEST_USERNAME'),
    getenv('FANCOURIER_TEST_PASSWORD'),
    // 4th argument: a cached token, or '' to fetch one on first use
);

$token = $fan->getToken();        // cached token, or fetched on demand
$token = $fan->getToken(true);    // force a refresh
```

Expired and rejected tokens are handled for you: a request made against a stale token is
refreshed and retried **once**.

## Common tasks

### Estimate a shipping cost

```php
$request = (new Fancourier\Request\GetCosts())
    ->setParcels(1)
    ->setWeight(1)
    ->setCounty('Arad')
    ->setCity('Aciuta')
    ->setDeclaredValue(125);

$response = $fan->getCosts($request);

if ($response->isOk()) {
    echo $response->getCostTotal();   // typed getters, or $response->getData() for raw JSON
}
```

### Create an AWB

```php
$awb = (new Fancourier\Objects\AwbIntern())
    ->setService('Cont Colector')
    ->setPaymentType(Fancourier\Enums\PaymentType::Expeditor) // or the string 'expeditor'
    ->setParcels(1)
    ->setWeight(1)                                            // kg
    ->setReimbursement(199.99)                                // cash on delivery
    ->setDeclaredValue(1000)
    ->setSizes(10, 5, 1)                                      // cm
    ->setNotes('testing notes')
    ->setContents('SKU-1, SKU-2')
    ->setRecipientName('John Ivy')
    ->setPhone('0723000000')
    ->setCounty('Arad')
    ->setCity('Aciuta')
    ->setStreet('Str Lunga')
    ->setNumber(1)
    ->addOption('S')
    ->addOption('X');

$request = (new Fancourier\Request\CreateAwb())->addAwb($awb);
$response = $fan->createAwb($request);

if ($response->isOk()) {
    foreach ($response->getAll() as $created) {
        echo $created->hasErrors() ? print_r($created->getErrors(), true) : $created->getAwb();
    }
}
```

Need a batch? There is no separate bulk request anymore — keep calling `addAwb()` and the
same request ships them all.

### Track an AWB

```php
$request = (new Fancourier\Request\TrackAwb())
    ->addAwb('2150900120084')
    ->addAwb('2150900120085');

$response = $fan->trackAwb($request);
if ($response->isOk()) {
    foreach ($response->getAll() as $tracker) {   // AwbTracker objects
        echo $tracker->getAwbNumber() . ' — ' . $tracker->getStatus()['name'];
    }
}
```

### Print a label

```php
$request = (new Fancourier\Request\PrintAwb())
    ->setPdf(true)                 // PDF (default); setZpl(true) for Zebra, setPdf(false) for HTML
    ->setSize(Fancourier\Enums\LabelFormat::A4)   // A4 / A5 / A6 (A6 is ePOD-only)
    ->setAwb('2150900120086');

$response = $fan->printAwb($request);
if ($response->isOk()) {
    file_put_contents('awb.pdf', $response->getData());
}
```

PDF and ZPL are mutually exclusive: `setPdf()` disables ZPL and vice versa.

### Find a FANbox / PayPoint

FAN Courier calls these **PUDO** (Pick Up Drop Off). Fetch the network, then reference the
chosen `PUDO_ID` when creating the AWB.

```php
$request = (new Fancourier\Request\GetPudo())
    ->setType(Fancourier\Enums\PudoType::Fanbox);   // fanbox / paypoint / office

$response = $fan->getPudo($request);
if ($response->isOk()) {
    print_r($response->getAll());   // Pudo objects
}
```

### Delete an AWB

```php
$response = $fan->deleteAwb((new Fancourier\Request\DeleteAwb())->setAwb('2150900120086'));
```

## Error handling

Every response — success or failure — answers the same three questions:

```php
if ($response->isOk()) {
    $data = $response->getData();          // raw API payload
} else {
    $code    = $response->getErrorCode();  // API or transport error code
    $message = $response->getErrorMessage();
}
```

API-level `status: "fail"` bodies, HTTP-level failures, and per-AWB `errors` are all surfaced
consistently. See [`docs/errors.md`](./docs/errors.md) for the full behaviour.

## Enums and code lists

Payment types, delivery modes, document types, order types, PUDO types, languages and label
formats are all string-backed enums:

```php
use Fancourier\Enums\PaymentType;

$awb->setPaymentType(PaymentType::Expeditor);   // enum
$awb->setPaymentType('expeditor');              // equivalent string
```

Setters accept `string|Enum` throughout, so migration is opt-in. The complete list lives in
[`docs/enums.md`](./docs/enums.md).

## Endpoint reference

| Facade method | API endpoint | Example |
|---|---|---|
| `createAwb()` | `POST /intern-awb` | [`create_awb.php`](./examples/create_awb.php) |
| `createAwbExternal()` | `POST /extern-awb` | [`create_awb_extern.php`](./examples/create_awb_extern.php) |
| `printAwb()` | `GET /awb/label` | [`printAwb.php`](./examples/printAwb.php) |
| `deleteAwb()` | `DELETE /awb` | [`deleteAwb.php`](./examples/deleteAwb.php) |
| `getShippingSlip()` | `GET /reports/awb` | [`getShippingSlip.php`](./examples/getShippingSlip.php) |
| `getAwbConfirmations()` | `GET /reports/get-awb-confirmations` | [`getAwbConfirmations.php`](./examples/getAwbConfirmations.php) |
| `trackAwb()` | `GET /reports/awb/tracking` | [`trackAwb.php`](./examples/trackAwb.php) |
| `getAwbEvents()` | `GET /reports/awb-events` | [`getAwbEvents.php`](./examples/getAwbEvents.php) |
| `getCosts()` | `GET /reports/awb/internal-tariff` | [`getCosts.php`](./examples/getCosts.php) |
| `getCostsExternal()` | `GET /reports/awb/external-tariff` | [`getCostsExternal.php`](./examples/getCostsExternal.php) |
| `createCourierOrder()` | `POST /order` | [`createCourierOrder.php`](./examples/createCourierOrder.php) |
| `deleteCourierOrder()` | `DELETE /order` | [`deleteCourierOrder.php`](./examples/deleteCourierOrder.php) |
| `getCourierOrders()` | `GET /reports/orders` | [`getCourierOrders.php`](./examples/getCourierOrders.php) |
| `getCourierOrderEvents()` | `GET /reports/order-events` | [`getCourierOrderEvents.php`](./examples/getCourierOrderEvents.php) |
| `trackCourierOrder()` | `GET /reports/orders/tracking` | [`trackCourierOrder.php`](./examples/trackCourierOrder.php) |
| `getServices()` | `GET /reports/services` | [`getServices.php`](./examples/getServices.php) |
| `getServiceOptions()` | `GET /reports/service-options` | [`getServiceOptions.php`](./examples/getServiceOptions.php) |
| `getCounties()` | `GET /reports/counties` | [`getCounties.php`](./examples/getCounties.php) |
| `getCities()` | `GET /reports/localities` | [`getCities.php`](./examples/getCities.php) |
| `getStreets()` | `GET /reports/streets` | [`getStreets.php`](./examples/getStreets.php) |
| `getCountries()` | `GET /reports/countries` | [`getCountries.php`](./examples/getCountries.php) |
| `getCountiesExternal()` | `GET /reports/external-counties` | [`getCountiesExternal.php`](./examples/getCountiesExternal.php) |
| `getCitiesExternal()` | `GET /reports/external-localities` | [`getCitiesExternal.php`](./examples/getCitiesExternal.php) |
| `getBranches()` | `GET /reports/branches` | [`getBranches.php`](./examples/getBranches.php) |
| `getPudo()` | `GET /reports/pickup-points` | [`getPudo.php`](./examples/getPudo.php) |
| `getBankTransfers()` | `GET /reports/bank-transfers` | [`getBankTransfers.php`](./examples/getBankTransfers.php) |
| `createAwbBankAccount()` | `POST /awb-bank-account` | [`create_awb_bank_account.php`](./examples/create_awb_bank_account.php) |

Authentication uses `POST /login` (handled by the client).

## Upgrading from 1.x

2.0 is a deliberate breaking major: native types, `Client` camelCase renames, dead methods
removed, enums added. The full mapping is in [`MIGRATION.md`](./MIGRATION.md), and the
mechanical renames are automated by the bundled Rector set:

```bash
composer require --dev rector/rector
vendor/bin/rector process src tests --dry-run   # review
vendor/bin/rector process src tests             # apply
```

## Documentation

The [`docs/`](./docs/README.md) directory is the complete reference:
[endpoints](./docs/endpoints.md), [objects](./docs/objects.md), [enums](./docs/enums.md),
[errors](./docs/errors.md) and [getting started](./docs/getting-started.md). Runnable,
self-documenting examples for every endpoint live in [`examples/`](./examples/README.md).

## Development

```bash
composer install
vendor/bin/phpunit --exclude-group integration   # hermetic unit suite (no network)
vendor/bin/phpstan analyse                       # static analysis, level 8
vendor/bin/pint --test                           # code style
```

The live integration suite hits `https://api.fancourier.ro/` and creates **real** AWBs. It is
opt-in and excluded by default; set `FANCOURIER_LIVE_TOKEN` plus `FANCOURIER_TEST_CLIENT_ID`,
`FANCOURIER_TEST_USERNAME` and `FANCOURIER_TEST_PASSWORD` to run it:

```bash
vendor/bin/phpunit --group integration
```

## License

MIT — see [`LICENSE`](./LICENSE). This project is a community fork of
[`shusaura85/fancourier-api`](https://github.com/shusaura85/fancourier-api); credits for the
original author and contributors are recorded in the LICENSE file.
