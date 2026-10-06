# FAN Courier API v2 - examples

Runnable examples for every endpoint, and the de-facto API documentation for
this package. Each script is self-contained and uses the shared bootstrap in
`_init.php`.

## Running

From this directory:

```bash
export FANCOURIER_TEST_CLIENT_ID=...
export FANCOURIER_TEST_USERNAME=...
export FANCOURIER_TEST_PASSWORD=...
php getCosts.php
```

`_init.php` loads the library (Composer autoloader when present, bundled
autoloader otherwise), builds a `Fancourier\Fancourier` instance in `$fan`, and
caches the 24h bearer token in `examples_token.txt`. Credentials are never
hardcoded. The examples are safe to run from any working directory.

## The common pattern

Every request-based example looks like this: bootstrap, build the request, set
its inputs, send it, check `isOk()`, then read the response or show the error.

```php
require __DIR__ . '/_init.php';

$request = new Fancourier\Request\GetCosts();
$request->setParcels(1)->setWeight(1)->setCounty('Arad')->setCity('Aciuta');

$response = $fan->getCosts($request);

if ($response->isOk()) {
    var_dump($response->getData());
} else {
    var_dump($response->getErrorMessage());
}
```

`getData()` is available on every response. Responses for endpoints that return
structured data also expose typed getters (e.g. `getId()`, `getAll()`); the
exact list is documented at the bottom of each example.

## Endpoints

### Price and services

| Example | Endpoint | Purpose |
| --- | --- | --- |
| `getCosts.php` | `GET reports/awb/internal-tariff` | Internal shipment price |
| `getCostsExternal.php` | `GET reports/awb/external-tariff` | International shipment price |
| `getServices.php` | `GET reports/services` | Services available to the account |
| `getServiceOptions.php` | `GET reports/service-options` | Options available for a service |

### Locations

| Example | Endpoint | Purpose |
| --- | --- | --- |
| `getCounties.php` | `GET reports/counties` | Romanian counties |
| `getCities.php` | `GET reports/localities` | Localities of a county |
| `getStreets.php` | `GET reports/streets` | Streets of a city (paginated) |
| `getBranches.php` | `GET reports/branches` | FAN Courier branches |
| `getPudo.php` | `GET reports/pickup-points` | FANbox/paypoint/office PUDO points |
| `getCountries.php` | `GET reports/countries` | Countries and their delivery modes |
| `getCountiesExternal.php` | `GET reports/external-counties` | Counties of a foreign country |
| `getCitiesExternal.php` | `GET reports/external-localities` | Localities abroad (paginated) |

### AWB lifecycle

| Example | Endpoint | Purpose |
| --- | --- | --- |
| `create_awb.php` | `POST intern-awb` | Create internal AWBs |
| `create_awb_extern.php` | `POST extern-awb` | Create export AWBs |
| `create_awb_fanbox.php` | `POST intern-awb` | Create a FANBox locker AWB |
| `printAwb.php` | `GET awb/label` | Render labels (PDF, ZPL or HTML) |
| `deleteAwb.php` | `DELETE awb` | Delete an AWB |
| `getShippingSlip.php` | `GET reports/awb` | Shipping slips for a day (paginated) |
| `getAwbConfirmations.php` | `GET reports/get-awb-confirmations` | Download proof-of-delivery ZIP |

### AWB tracking

| Example | Endpoint | Purpose |
| --- | --- | --- |
| `trackAwb.php` | `GET reports/awb/tracking` | Track AWBs |
| `getAwbEvents.php` | `GET reports/awb-events` | AWB event code list |

### Courier orders

| Example | Endpoint | Purpose |
| --- | --- | --- |
| `createCourierOrder.php` | `POST order` | Schedule a pickup |
| `deleteCourierOrder.php` | `DELETE order` | Cancel a pickup order |
| `getCourierOrders.php` | `GET reports/orders` | Orders placed for a day (paginated) |
| `getCourierOrderEvents.php` | `GET reports/order-events` | Order event code list |
| `trackCourierOrder.php` | `GET reports/orders/tracking` | Track pickup orders |

### Payments

| Example | Endpoint | Purpose |
| --- | --- | --- |
| `create_awb_bank_account.php` | `POST awb-bank-account` | Insert AWB bank account (IBAN) records |
| `getBankTransfers.php` | `GET reports/bank-transfers` | Bank transfers for a day (paginated) |
