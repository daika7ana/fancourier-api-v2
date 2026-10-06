# Endpoint reference

Every endpoint exposes one public method on `Fancourier\Fancourier` that takes a request
object and returns a typed response object. This reference lists all 26 endpoints
grouped by area, with the exact gateway, HTTP method, request setters and response getters
read from the shipped source.

All request classes extend `Fancourier\Request\AbstractRequest` and implement
`Fancourier\Request\RequestInterface`; all response classes extend
`Fancourier\Response\Generic` (or `PaginatedResponse`) and implement
`Fancourier\Response\ResponseInterface`. Common methods (see [errors](errors.md)) are not
repeated in every table.

## Call shape

```php
$request  = new Fancourier\Request\GetCosts();
// ...configure the request...
$response = $fan->getCosts($request);
```

Three endpoints take **no request argument** because they accept no parameters:
`getServices()`, `getCounties()` and `getCountries()`.

## Endpoint summary

| Facade method | Request | HTTP / gateway | Response |
|---|---|---|---|
| `createAwb(CreateAwb)` | `Request\CreateAwb` | `POST intern-awb` | `Response\CreateAwb` |
| `createAwbExternal(CreateAwbExternal)` | `Request\CreateAwbExternal` | `POST extern-awb` | `Response\CreateAwbExternal` |
| `printAwb(PrintAwb)` | `Request\PrintAwb` | `GET awb/label` | `Response\PrintAwb` |
| `deleteAwb(DeleteAwb)` | `Request\DeleteAwb` | `DELETE awb` | `Response\DeleteAwb` |
| `getCosts(GetCosts)` | `Request\GetCosts` | `GET reports/awb/internal-tariff` | `Response\GetCosts` |
| `getCostsExternal(GetCostsExternal)` | `Request\GetCostsExternal` | `GET reports/awb/external-tariff` | `Response\GetCostsExternal` |
| `getServices()` | `Request\GetServices` | `GET reports/services` | `Response\GetServices` |
| `getServiceOptions(GetServiceOptions)` | `Request\GetServiceOptions` | `GET reports/service-options` | `Response\GetServiceOptions` |
| `getCounties()` | `Request\GetCounties` | `GET reports/counties` | `Response\GetCounties` |
| `getCities(GetCities)` | `Request\GetCities` | `GET reports/localities` | `Response\GetCities` |
| `getStreets(GetStreets)` | `Request\GetStreets` | `GET reports/streets` | `Response\GetStreets` |
| `getCountries()` | `Request\GetCountries` | `GET reports/countries` | `Response\GetCountries` |
| `getCountiesExternal(GetCountiesExternal)` | `Request\GetCountiesExternal` | `GET reports/external-counties` | `Response\GetCountiesExternal` |
| `getCitiesExternal(GetCitiesExternal)` | `Request\GetCitiesExternal` | `GET reports/external-localities` | `Response\GetCitiesExternal` |
| `getPudo(GetPudo)` | `Request\GetPudo` | `GET reports/pickup-points` | `Response\GetPudo` |
| `createCourierOrder(CreateCourierOrder)` | `Request\CreateCourierOrder` | `POST order` | `Response\CreateCourierOrder` |
| `deleteCourierOrder(DeleteCourierOrder)` | `Request\DeleteCourierOrder` | `DELETE order` | `Response\DeleteCourierOrder` |
| `getCourierOrders(GetCourierOrders)` | `Request\GetCourierOrders` | `GET reports/orders` | `Response\GetCourierOrders` |
| `getCourierOrderEvents(GetCourierOrderEvents)` | `Request\GetCourierOrderEvents` | `GET reports/order-events` | `Response\GetCourierOrderEvents` |
| `trackCourierOrder(TrackCourierOrder)` | `Request\TrackCourierOrder` | `GET reports/orders/tracking` | `Response\TrackCourierOrder` |
| `getShippingSlip(GetShippingSlip)` | `Request\GetShippingSlip` | `GET reports/awb` | `Response\GetShippingSlip` |
| `getAwbEvents(GetAwbEvents)` | `Request\GetAwbEvents` | `GET reports/awb-events` | `Response\GetAwbEvents` |
| `trackAwb(TrackAwb)` | `Request\TrackAwb` | `GET reports/awb/tracking` | `Response\TrackAwb` (declared `Response\Generic`) |
| `getAwbConfirmations(GetAwbConfirmations)` | `Request\GetAwbConfirmations` | `GET reports/get-awb-confirmations` | `Response\GetAwbConfirmations` |
| `getBankTransfers(GetBankTransfers)` | `Request\GetBankTransfers` | `GET reports/bank-transfers` | `Response\GetBankTransfers` |
| `getBranches(GetBranches)` | `Request\GetBranches` | `GET reports/branches` | `Response\GetBranches` |

The base URL is `Fancourier::API_URL` = `https://api.fancourier.ro/`. The
`trackAwb()` facade method is typed as `Response\Generic` but returns the
`Response\TrackAwb` object the request was constructed with.

## FAN Courier docs ↔ PHP classes

This is the mapping between the sections of the official FAN Courier documentation and the
equivalent PHP classes. All PHP names are relative to the `Fancourier\` namespace.

| FAN Courier docs | PHP Request/Response | PHP Object | Notes |
|---|---|---|---|
| Autentificare | `\Auth` | — | Handled automatically; no need to call it directly. |
| **Generare AWB** | | | |
| Tipuri servicii | `[Request/Response]\GetServices` | `Objects\Service` | Call `Fancourier::getServices()` (no request object). |
| Optiuni servicii | `[Request/Response]\GetServiceOptions` | `Objects\ServiceOption` | — |
| Printare AWB | `[Request/Response]\PrintAwb` | — | `getData()` returns the HTML, PDF or ZPL payload. |
| Stergere AWB | `[Request/Response]\DeleteAwb` | — | — |
| **AWB Intern** | | | |
| Creare AWB Intern | `[Request/Response]\CreateAwb` | `Objects\AwbIntern` | Single and bulk creation: add `AwbIntern` objects to one request. |
| Judete | `[Request/Response]\GetCounties` | `Objects\County` | Call `Fancourier::getCounties()` (no request object). |
| Localitati | `[Request/Response]\GetCities` | `Objects\City` | — |
| Strazi | `[Request/Response]\GetStreets` | `Objects\Street` | — |
| Puncte PUDO | `[Request/Response]\GetPudo` | `Objects\Pudo` | For FANBox; returns available lockers. |
| Tarif AWB Intern | `[Request/Response]\GetCosts` | — | — |
| **AWB Extern** | | | |
| Creare AWB Extern | `[Request/Response]\CreateAwbExternal` | `Objects\AwbExtern` | Single and bulk creation. |
| Tari | `[Request/Response]\GetCountries` | `Objects\Country` | Call `Fancourier::getCountries()` (no request object). |
| Judete Externe | `[Request/Response]\GetCountiesExternal` | `Objects\CountyExternal` | The official docs list Moldova, Grecia and Bulgaria. |
| Localitati Externe | `[Request/Response]\GetCitiesExternal` | `Objects\CityExternal` | The official docs list Moldova, Grecia and Bulgaria. |
| Tarif AWB Export | `[Request/Response]\GetCostsExternal` | — | — |
| **Comanda Curier** | | | |
| Plasare Comanda Curier | `[Request/Response]\CreateCourierOrder` | — | — |
| Stergere Comanda Curier | `[Request/Response]\DeleteCourierOrder` | — | — |
| **Raportare AWB** | | | |
| Borderou | `[Request/Response]\GetShippingSlip` | `Objects\ShippingSlip` | — |
| Event-uri AWB | `[Request/Response]\GetAwbEvents` | `Objects\AwbEvent` | — |
| AWB Tracking | `[Request/Response]\TrackAwb` | `Objects\AwbTracker` | Tracking for one or more AWBs. |
| AWB Confirmations | `[Request/Response]\GetAwbConfirmations` | — | Delivery confirmations for one or more AWBs. |
| Viramente Bancare | `[Request/Response]\GetBankTransfers` | `Objects\BankTransfer` | — |
| **Raportare Comenzi** | | | |
| Raport Comenzi | `[Request/Response]\GetCourierOrders` | `Objects\CourierOrder` | — |
| Event-uri Comenzi Curier | `[Request/Response]\GetCourierOrderEvents` | `Objects\CourierOrderEvent` | — |
| Tracking Comenzi Curier | `[Request/Response]\TrackCourierOrder` | `Objects\CourierOrderTracker` | — |
| **Detalii Cont** | | | |
| Sucursale | `[Request/Response]\GetBranches` | `Objects\Branch` | — |

---

## AWB

### createAwb

```php
Fancourier::createAwb(Fancourier\Request\CreateAwb $request): Fancourier\Response\CreateAwb
```

- Gateway: `POST intern-awb`
- Object: one or more [`AwbIntern`](objects.md#awbintern) data holders.

| Request method | Description |
|---|---|
| `addAwb(AwbIntern $awb): static` | Append an AWB to the shipment list (bulk creation). |
| `resetAwbs(): static` | Clear the AWB list. |
| `setPlatformId(int\|string $platformId): static` | Optional `platformId` from FAN Courier. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int, AwbIntern>` — the submitted objects, each updated with the API result. |
| `getData()` | The decoded `response` array. |

Each returned `AwbIntern` carries `hasErrors()`, `getErrors()`, `getAwb()` and
`getDetails()`. See [objects](objects.md#awbintern).

### createAwbExternal

```php
Fancourier::createAwbExternal(Fancourier\Request\CreateAwbExternal $request): Fancourier\Response\CreateAwbExternal
```

- Gateway: `POST extern-awb`
- Object: one or more [`AwbExtern`](objects.md#awbextern) data holders.

Request and response methods mirror `createAwb` (`addAwb`, `resetAwbs`, `setPlatformId`,
`getAll`), but `getAll()` returns `array<int, AwbExtern>`.

> An empty successful body is treated as an error rather than silently returning an empty
> list (2.0 fix B24).

### printAwb

```php
Fancourier::printAwb(Fancourier\Request\PrintAwb $request): Fancourier\Response\PrintAwb
```

- Gateway: `GET awb/label`

| Request method | Description |
|---|---|
| `setAwb(string $awb): static` | Add an AWB (alias of `addAwb`). |
| `addAwb(string $awb): static` | Append an AWB. |
| `getAwb(): array<string>` | All added AWB numbers. |
| `setPdf(bool $active = true): static` | PDF mode (default). Disables ZPL when enabled. |
| `getPdf(): bool` | Whether PDF mode is active. |
| `setZpl(bool $active = false): static` | ZPL mode. Disables PDF when enabled. |
| `getZpl(): bool` | Whether ZPL mode is active. |
| `setHtml(bool $active = true): static` | Explicit HTML mode (neither PDF nor ZPL). `setHtml(false)` falls back to PDF. |
| `getHtml(): bool` | True when neither PDF nor ZPL is active. |
| `setDpi(int $dpi = -1): static` | Dots per inch; applies to ZPL only. Sent when `> 0`. |
| `getDpi(): int` | Configured DPI. |
| `setLang(string\|Language $lang): static` | `ro` or `en`; anything else falls back to `ro`. |
| `getLang(): string` | Configured language. |
| `setSize(string\|LabelFormat $pageSize = ''): static` | `''`, `A4`, `A5`, `A6` (`A6` is for ePOD only). Anything else becomes `''`. |
| `getSize(): string` | Configured format. |

Response: `getData()` returns the raw body. A non-JSON body is treated as **success**
(PDF bytes, ZPL text or HTML). A JSON body with `status` of `fail`/`error` is treated as a
failure.

### deleteAwb

```php
Fancourier::deleteAwb(Fancourier\Request\DeleteAwb $request): Fancourier\Response\DeleteAwb
```

- Gateway: `DELETE awb`

| Request method | Description |
|---|---|
| `setAwb(string $awb): static` | AWB number to delete. |
| `getAwb(): ?string` | Configured AWB number. |

Response: `getData()` returns `bool` — `true` when the API reports `status:"success"`.

---

## Costs

### getCosts

```php
Fancourier::getCosts(Fancourier\Request\GetCosts $request): Fancourier\Response\GetCosts
```

- Gateway: `GET reports/awb/internal-tariff`

| Request method | Default | Description |
|---|---|---|
| `setPaymentType(string\|PaymentType $paymentType): static` | `destinatar` | Only `destinatar` / `expeditor` are accepted; other values throw `\InvalidArgumentException`. |
| `setCity(string $city)` / `getCity()` | `null` | Recipient locality. |
| `setCounty(string $county)` / `getCounty()` | `null` | Recipient county. |
| `setSenderCity(string $city)` / `getSenderCity()` | `null` | Sender locality (optional). |
| `setSenderCounty(string $county)` / `getSenderCounty()` | `null` | Sender county (optional). |
| `setEnvelopes(int $envelopes)` / `getEnvelopes()` | `0` | Envelope count. |
| `setParcels(int $parcels)` / `getParcels()` | `0` | Parcel count. |
| `setWeight(int\|float $weight)` / `getWeight()` | `null` | Weight in kg. |
| `setLength()` / `setWidth()` / `setHeight()` | `0` | Dimensions in cm; sent only when all three are `> 0`. |
| `setDeclaredValue(int\|float $v)` / `getDeclaredValue()` | `null` | Declared value. |
| `setService(string $service)` / `getService()` | `Standard` | Service name. |
| `setOptions(string $options): static` | `[]` | Replace options; the string is split into single characters. |
| `addOption(string $option): static` | — | Add one option letter (uppercased). |
| `resetOptions(): static` | — | Clear all options. |

| Response method | Returns |
|---|---|
| `getAllErrors()` | `array` — the `errors` field, if the API returned one. |
| `getKmCost()` | `float` (API `extraKmCost`). |
| `getWeightCost()` | `float`. |
| `getInsuranceCost()` | `float`. |
| `getOptionsCost()` | `float`. |
| `getFuelCost()` | `float`. |
| `getCost()` | `float` (API `costNoVAT`). |
| `getCostVat()` | `float` (API `vat`). |
| `getCostTotal()` | `float` (API `total`). |

Money fields are normalized to `float` at parse time.

### getCostsExternal

```php
Fancourier::getCostsExternal(Fancourier\Request\GetCostsExternal $request): Fancourier\Response\GetCostsExternal
```

- Gateway: `GET reports/awb/external-tariff`

| Request method | Default | Description |
|---|---|---|
| `setDeliveryMode(string\|DeliveryMode $m): static` | `rutier` | Only `rutier` / `aerian`; other values are ignored. |
| `setDocumentType(string $documentType): static` | `document` | Only `document` / `non document`; other values are ignored. |
| `setCountry(string $country)` / `getCountry()` | `null` | Recipient country. |
| `setSenderCity()` / `setSenderCounty()` | `null` | Optional sender. |
| `setEnvelopes()` / `setParcels()` | `0` | Package counts. |
| `setWeight()` / `setLength()` / `setWidth()` / `setHeight()` | `null` / `0` | Weight and dimensions. |
| `setService(string $service)` / `getService()` | `Export` | Service name. |

The response is `Response\GetCostsExternal extends Response\GetCosts`, so it exposes the
same getters. `setDeliveryMode()` accepts the `DeliveryMode` enum; `setDocumentType()`
takes a plain string.

---

## Reference lists

### getServices

```php
Fancourier::getServices(): Fancourier\Response\GetServices
```

- Gateway: `GET reports/services`
- No request argument.

| Response method | Returns |
|---|---|
| `getAll()` | `array<string, Service>` keyed by service name. |
| `hasService(string $name): bool` | Whether a service exists. |
| `getService(string $name): Service\|false` | One service by name. |

### getServiceOptions

```php
Fancourier::getServiceOptions(Fancourier\Request\GetServiceOptions $request): Fancourier\Response\GetServiceOptions
```

- Gateway: `GET reports/service-options`

| Request method | Default |
|---|---|
| `setService(string $service)` / `getService()` | `Standard` |

| Response method | Returns |
|---|---|
| `getAll()` | `array<string, ServiceOption>` keyed by option code. |
| `hasOption(string $code): bool` | Whether an option exists. |
| `getOption(string $code): ServiceOption\|false` | One option by code. |

### getCounties

```php
Fancourier::getCounties(): Fancourier\Response\GetCounties
```

- Gateway: `GET reports/counties`
- No request argument.

| Response method | Returns |
|---|---|
| `getAll()` | `array<string, County>` keyed by county name. |
| `getCounty(string $name): County\|false` | One county by name. |

### getCities

```php
Fancourier::getCities(Fancourier\Request\GetCities $request): Fancourier\Response\GetCities
```

- Gateway: `GET reports/localities`

| Request method | Description |
|---|---|
| `setCounty(string $county)` / `getCounty()` | Filter by county (optional; omitted when empty). |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, City>` keyed by id. |
| `getCity(string $cityname): City\|false` | Case-insensitive lookup by name. |

### getStreets

```php
Fancourier::getStreets(Fancourier\Request\GetStreets $request): Fancourier\Response\GetStreets
```

- Gateway: `GET reports/streets`

| Request method | Description |
|---|---|
| `setCounty(string $county)` / `getCounty()` | Filter by county. |
| `setCity(string $city)` / `getCity()` | Filter by locality (sent as `locality`). |
| `setPage(int $page)` / `getPage()` | Pagination page. |
| `setPerPage(int $perPage)` / `getPerPage()` | Page size; default `1000`, capped at the documented ceiling of `1000`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, Street>` keyed by id. |
| `getTotal()` | Total entries. |
| `getPerPage()` | Page size reported by the API. |
| `getCurrentPage()` | Current page. |
| `getTotalPages()` | Computed page count. |

### getCountries

```php
Fancourier::getCountries(): Fancourier\Response\GetCountries
```

- Gateway: `GET reports/countries`
- No request argument.

| Response method | Returns |
|---|---|
| `getAll()` | `array<string, Country>` keyed by country name. |
| `getCountry(string $name): Country\|false` | One country by name. |

### getCountiesExternal

```php
Fancourier::getCountiesExternal(Fancourier\Request\GetCountiesExternal $request): Fancourier\Response\GetCountiesExternal
```

- Gateway: `GET reports/external-counties`

| Request method | Description |
|---|---|
| `setCountry(string $country)` / `getCountry()` | Filter by country (optional). |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, CountyExternal>` keyed by id. |

### getCitiesExternal

```php
Fancourier::getCitiesExternal(Fancourier\Request\GetCitiesExternal $request): Fancourier\Response\GetCitiesExternal
```

- Gateway: `GET reports/external-localities`

| Request method | Description |
|---|---|
| `setCountry(string $country)` / `getCountry()` | Filter by country. |
| `setCounty(string $county)` / `getCounty()` | Filter by county. |
| `setPage()` / `getPage()` | Pagination page. |
| `setPerPage()` / `getPerPage()` | Page size; default `100`, capped at `100`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, CityExternal>` keyed by id. |
| `getTotal()` / `getPerPage()` / `getCurrentPage()` / `getTotalPages()` | Pagination values. |

### getPudo

```php
Fancourier::getPudo(Fancourier\Request\GetPudo $request): Fancourier\Response\GetPudo
```

- Gateway: `GET reports/pickup-points`

| Request method | Default | Description |
|---|---|---|
| `setType(string\|PudoType $pudoType): static` | `fanbox` | Pickup-point type. |
| `getType(): string` | — | Configured type. |
| `setId(string $pudoId): static` | — | Fetch one PUDO by id; when set, `type` is ignored. |
| `getId(): string\|false` | — | Configured id, or `false` when unset. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, Pudo>` keyed by id. |
| `get(int\|string\|null $pudoId = null): Pudo\|false` | One PUDO by id; with no id and exactly one result, returns that result. |

---

## Courier orders

### createCourierOrder

```php
Fancourier::createCourierOrder(Fancourier\Request\CreateCourierOrder $request): Fancourier\Response\CreateCourierOrder
```

- Gateway: `POST order`

| Request method | Default | Notes |
|---|---|---|
| `setAwb(string $awbNo)` / `getAwb()` | `''` | AWB number, when the order accompanies an AWB. |
| `setParcels(int $parcels)` / `getParcels()` | `0` | Parcel count. |
| `setEnvelopes(int $envelopes)` / `getEnvelopes()` | `0` | Envelope count. |
| `setWeight(int\|float $weight)` / `getWeight()` | `1` | Weight in kg. |
| `setSizes(int\|float $length, int\|float $height, int\|float $width): static` | — | All three must be `> 0` or the setter throws. |
| `setLength()` / `setHeight()` / `setWidth()` | `0` | Individual dimensions. |
| `setOrderType(string\|OrderType $orderType)` / `getOrderType()` | `Standard` | Order type. |
| `setPickupDate(string $date)` / `getPickupDate()` | `''` | Pickup date (`YYYY-mm-dd`). |
| `setPickupHours(int\|string $first, int\|string $last)` / `getPickupHours()` | `['min' => '', 'max' => '']` | Pickup window. |
| `setNotes(string $notes)` / `getNotes()` | `''` | Observations. |
| `setRecipientName(string $name)` / `getRecipientName()` | `null` | Recipient (required for non-Standard order types). |
| `setContactPerson()` / `getContactPerson()` | `''` | Recipient contact. |
| `setPhone()` / `getPhone()` | `''` | Recipient phone. |
| `setAltPhone()` / `getAltPhone()` | `''` | Secondary phone. |
| `setEmail()` / `getEmail()` | `''` | Recipient email. |
| `setCounty()` / `getCounty()` | `''` | Recipient county. |
| `setCity()` / `getCity()` | `''` | Recipient locality. |
| `setStreet()` / `getStreet()` | `''` | Recipient street. |
| `setNumber()` / `getNumber()` | `''` | Recipient street number. |
| `setPostalCode()` / `getPostalCode()` | `''` | Recipient postal code. |
| `setBuilding()` / `setEntrance()` / `setFloor()` / `setApartment()` | `''` | Address extras. |

> The recipient block is only included in the payload when `orderType` is **not**
> `Standard` (case-insensitive). Standard orders omit it.

| Response method | Returns |
|---|---|
| `getId()` | `string\|int\|null` — the created order id (`data.id`). |

### deleteCourierOrder

```php
Fancourier::deleteCourierOrder(Fancourier\Request\DeleteCourierOrder $request): Fancourier\Response\DeleteCourierOrder
```

- Gateway: `DELETE order`

| Request method | Description |
|---|---|
| `setOrder(string $orderId): static` | Order id to delete. |
| `getOrder(): ?string` | Configured order id. |

Response: `Response\DeleteCourierOrder extends Response\DeleteAwb`; `getData()` returns a
`bool` (`true` on `status:"success"`).

### getCourierOrders

```php
Fancourier::getCourierOrders(Fancourier\Request\GetCourierOrders $request): Fancourier\Response\GetCourierOrders
```

- Gateway: `GET reports/orders`

| Request method | Default | Description |
|---|---|---|
| `setDate(string $date)` / `getDate()` | today, `d-m-Y` | Accepts `dd-mm-YYYY` (native) or `YYYY-mm-dd`, which is converted internally. |
| `setPage()` / `getPage()` | `0` | Pagination page. |
| `setPerPage()` / `getPerPage()` | `10` | Page size, capped at `100`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, CourierOrder>` keyed by order id. |
| `get(int\|string $orderId): CourierOrder\|false` | One order by id. |
| `getTotal()` | Total **pages** (this endpoint reports pages, not entries). |
| `getTotalPages()` | Alias of `getTotal()`. |
| `getPerPage()` | Page size. |
| `getCurrentPage()` | Current page. |

### getCourierOrderEvents

```php
Fancourier::getCourierOrderEvents(Fancourier\Request\GetCourierOrderEvents $request): Fancourier\Response\GetCourierOrderEvents
```

- Gateway: `GET reports/order-events`

| Request method | Default | Description |
|---|---|---|
| `setLanguage(string\|Language $language)` / `getLanguage()` | `''` | `ro` or `en`; unsupported values are ignored and omitted from the payload. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, CourierOrderEvent>` keyed by event id. |
| `getEvent(int\|string $courierEventId): CourierOrderEvent\|false` | One event by id. |

### trackCourierOrder

```php
Fancourier::trackCourierOrder(Fancourier\Request\TrackCourierOrder $request): Fancourier\Response\TrackCourierOrder
```

- Gateway: `GET reports/orders/tracking`

| Request method | Description |
|---|---|
| `addOrder(string $order): static` | Append an order id to track. |
| `setOrder(string $order): static` | Alias of `addOrder`. |
| `resetOrders(): static` | Clear the order list. |
| `setLanguage(string\|Language $language)` / `getLanguage()` | Optional `ro`/`en`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, CourierOrderTracker>` keyed by order id. |
| `getOrder(int\|string $orderId): CourierOrderTracker\|false` | One tracker by order id. |

---

## Reporting

### getShippingSlip

```php
Fancourier::getShippingSlip(Fancourier\Request\GetShippingSlip $request): Fancourier\Response\GetShippingSlip
```

- Gateway: `GET reports/awb`

| Request method | Default | Description |
|---|---|---|
| `setDate(string $date)` / `getDate()` | today, `Y-m-d` | Report date. |
| `setPage()` / `getPage()` | `0` | Pagination page. |
| `setPerPage()` / `getPerPage()` | `100` | Page size, capped at `100`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int, ShippingSlip>` (indexed list). |
| `get(int $position): ShippingSlip\|false` | One slip by position. |
| `getTotal()` / `getPerPage()` / `getCurrentPage()` / `getTotalPages()` | Pagination values. |

### getAwbEvents

```php
Fancourier::getAwbEvents(Fancourier\Request\GetAwbEvents $request): Fancourier\Response\GetAwbEvents
```

- Gateway: `GET reports/awb-events`

| Request method | Description |
|---|---|
| `setLanguage(string\|Language $language)` / `getLanguage()` | Optional `ro`/`en`; unsupported values are ignored. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, AwbEvent>` keyed by event id. |
| `getEvent(int\|string $eventId): AwbEvent\|false` | One event by id. |

### trackAwb

```php
Fancourier::trackAwb(Fancourier\Request\TrackAwb $request): Fancourier\Response\Generic
```

- Gateway: `GET reports/awb/tracking`
- Declared return type is `Response\Generic`; the concrete object is `Response\TrackAwb`.

| Request method | Description |
|---|---|
| `addAwb(string $awb): static` | Append an AWB to track. |
| `setAwb(string $awb): static` | Alias of `addAwb`. |
| `resetAwbs(): static` | Clear the AWB list. |
| `setLanguage(string\|Language $language)` / `getLanguage()` | Optional `ro`/`en`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, AwbTracker>` keyed by AWB number. |
| `getAwb(int\|string $awbNo): AwbTracker\|false` | One tracker by AWB number. |

### getAwbConfirmations

```php
Fancourier::getAwbConfirmations(Fancourier\Request\GetAwbConfirmations $request): Fancourier\Response\GetAwbConfirmations
```

- Gateway: `GET reports/get-awb-confirmations`

| Request method | Description |
|---|---|
| `addAwb(string $awb): static` | Append an AWB. |
| `setAwb(string $awb): static` | Alias of `addAwb`. |
| `resetAwbs(): static` | Clear the AWB list. |

The API answers with a ZIP archive when confirmations are available (body begins with the
`PK` magic bytes); otherwise it answers with JSON.

| Response method | Returns |
|---|---|
| `getRAWbytes()` | `string\|null` — the ZIP payload as raw bytes. |
| `getLength()` | `int` — size of the ZIP payload in bytes. |
| `saveToFile(string $filename)` | `int\|false` — bytes written, or `false` on write failure. |
| `getData()` | The raw payload when it is a ZIP. |

Test AWBs that have not been delivered return an error (there is nothing to confirm).

### getBankTransfers

```php
Fancourier::getBankTransfers(Fancourier\Request\GetBankTransfers $request): Fancourier\Response\GetBankTransfers
```

- Gateway: `GET reports/bank-transfers`

| Request method | Default | Description |
|---|---|---|
| `setDate(string $usedate)` / `getDate()` | today, `Y-m-d` | Transfer date. |
| `setPage()` / `getPage()` | `0` | Pagination page. |
| `setPerPage()` / `getPerPage()` | `100` | Page size, capped at `100`. |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int, BankTransfer>` (indexed list). |
| `get(int $position = 0): BankTransfer\|false` | One transfer by position. |
| `getTotal()` / `getPerPage()` / `getCurrentPage()` / `getTotalPages()` | Pagination values. |

### getBranches

```php
Fancourier::getBranches(Fancourier\Request\GetBranches $request): Fancourier\Response\GetBranches
```

- Gateway: `GET reports/branches`

| Request method | Description |
|---|---|
| `setCounty(string $county)` / `getCounty()` | Filter by county (optional). |
| `setCity(string $city)` / `getCity()` | Filter by locality, sent as `locality` (optional). |

| Response method | Returns |
|---|---|
| `getAll()` | `array<int\|string, Branch>` keyed by branch id. |
| `get(int\|string $id): ?Branch` | One branch by id; **`null`** when missing. |
