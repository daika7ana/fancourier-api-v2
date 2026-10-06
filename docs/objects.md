# Objects reference

The `Fancourier\Objects\*` classes are the mutable data holders exchanged with the API.
Request classes accept them (for AWB creation) or their constructor parses API rows into
them (for responses). All setters return `static` for fluent chaining.

```php
use Fancourier\Objects\AwbIntern;

$awb = (new AwbIntern())
    ->setService('Cont Colector')
    ->setParcels(1)
    ->setWeight(1)
    ->setRecipientName('John Ivy')
    ->setCounty('Arad')
    ->setCity('Aciuta')
    ->setStreet('Str Lunga')
    ->setNumber('1')
    ->addOption('S');
```

Objects that are built from a response are constructed internally by the response class;
you normally read them through typed getters rather than constructing them yourself
(`AwbEvent`, `City`, `Branch`, `Pudo`, `ShippingSlip`, `BankTransfer`, …).

---

## AwbIntern

`Fancourier\Objects\AwbIntern` — an internal (Romanian) AWB, used by
[`createAwb`](endpoints.md#createawb). One object per shipment; pass several to one request
for bulk creation. `pack()` builds the `intern-awb` payload and is called by the request.

### Parties and contact

| Setter | Getter |
|---|---|
| `setRecipientName(string $recipient)` | `getRecipientName(): string` |
| `setContactPerson(string $contactPerson)` | `getContactPerson(): string` |
| `setPhone(string $phone)` | `getPhone(): string` |
| `setAltPhone(string $phone)` | `getAltPhone(): string` |
| `setEmail(string $email)` | `getEmail(): string` |
| `setSenderName(string $sender)` | `getSenderName(): string` |
| `setSenderContactPerson(string $contactPerson)` | `getSenderContactPerson(): string` |
| `setSenderPhone(string $phone)` | `getSenderPhone(): string` |
| `setSenderAltPhone(string $phone)` | `getSenderAltPhone(): string` |
| `setSenderEmail(string $email)` | `getSenderEmail(): string` |

### Recipient address

| Setter | Getter |
|---|---|
| `setCounty(string $county)` | `getCounty(): string` |
| `setCity(string $city)` | `getCity(): string` |
| `setStreet(string $street)` | `getStreet(): string` |
| `setNumber(string $number)` | `getNumber(): string` |
| `setPostalCode(string $postalCode)` | `getPostalCode(): string` |
| `setBuilding(string $building)` | `getBuilding(): string` |
| `setEntrance(string $entrance)` | `getEntrance(): string` |
| `setFloor(string $floor)` | `getFloor(): string` |
| `setApartment(string $apartment)` | `getApartment(): string` |
| `setPickupLocation(string $pudoId)` | `getPickupLocation(): string` — FANbox pickup point id. |
| `setDropOffLocation(string $pudoId)` | `getDropOffLocation(): string` — FANbox drop-off point id. |

### Sender address

| Setter | Getter |
|---|---|
| `setSenderCounty(string $county)` | `getSenderCounty(): string` |
| `setSenderCity(string $city)` | `getSenderCity(): string` |
| `setSenderStreet(string $street)` | `getSenderStreet(): string` |
| `setSenderNumber(string $number)` | `getSenderNumber(): string` |
| `setSenderPostalCode(string $postalCode)` | `getSenderPostalCode(): string` |
| `setSenderBuilding(string $building)` | `getSenderBuilding(): string` |
| `setSenderEntrance(string $entrance)` | `getSenderEntrance(): string` |
| `setSenderFloor(string $floor)` | `getSenderFloor(): string` |
| `setSenderApartment(string $apartment)` | `getSenderApartment(): string` |

### Shipment

| Setter | Getter | Notes |
|---|---|---|
| `setService(string $service)` | `getService(): string` | Default `Standard`. |
| `setEnvelopes(int\|string $envelopes)` | `getEnvelopes(): int` | Optional if parcels set. |
| `setParcels(int\|string $parcels)` | `getParcels(): int` | Optional if envelopes set. |
| `setWeight(int\|float\|string $weight)` | `getWeight(): int\|float` | kg. |
| `setReimbursement(float\|int\|string $cod)` | `getReimbursement(): float\|int\|string` | Cash on delivery. |
| `setCurrency(string $currency)` | `getCurrency(): string` | Default `RON`. |
| `setDeclaredValue(int\|float\|string $v)` | `getDeclaredValue(): int\|float` | — |
| `setPaymentType(string\|PaymentType $paymentType)` | `getPaymentType(): string` | Default `destinatar`. |
| `setRefund(string $refund)` | `getRefund(): string` | — |
| `setReturnPayment(string\|PaymentType $paymentType)` | `getReturnPayment(): string` | Default `expeditor`. |
| `setNotes(string $notes)` | `getNotes(): string` | Observation. |
| `setContents(string $contents)` | `getContents(): string` | Shipment content. |
| `setCostCenter(string $costCenter)` | `getCostCenter(): string` | — |
| `setUITCode(string $uitCode)` | `getUITCode(): string` | Acronym-cased method. |
| `setBank(string $bank)` | `getBank(): string` | — |
| `setIban(string $iban)` | `getIban(): string` | — |

### Sizes

`setSizes(int|float|string $length_cm, int|float|string $height_cm, int|float|string $width_cm): static`
sets all three and throws if any is `<= 0`. `getSizes(): array{length, height, width}`.
Individual accessors: `setHeight()` / `getHeight()`, `setLength()` / `getLength()`,
`setWidth()` / `getWidth()` (all `int|float`).

### Options

| Method | Description |
|---|---|
| `setOptions(string $options): static` | Replace all options; the string is split into characters. |
| `addOption(string $option): static` | Add one letter (uppercased). |
| `resetOptions(): static` | Clear all options. |
| `getOptions(): array<int, string>` | Configured option letters. |

### Non-EU parcels

| Setter | Getter |
|---|---|
| `setIsValueUnderThreshold(bool $value)` | `getIsValueUnderThreshold(): bool` |
| `setCountryCode(string $code)` | `getCountryCode(): string` |
| `setVatId(string $vatId)` | `getVatId(): string` |
| `setCompany(string $company)` | `getCompany(): string` |

These fields are only added to the payload once `setIsValueUnderThreshold()` has been
called (the internally stored value is nullable until then).

### Result state (response)

After `createAwb`, the request fills each object with the API result:

| Method | Returns |
|---|---|
| `hasErrors(): bool` | Whether the AWB failed. |
| `getErrors(): array` | Error details. |
| `getAwb(): ?string` | Assigned AWB number. |
| `getDetails(): ?array` | `tariff`, `vat`, `packages`, `letter`, `routingCode`, `office`, `pickUpPointId`, `visualCode`, `estimatedDeliveryTime`. |

---

## AwbExtern

`Fancourier\Objects\AwbExtern` — an export AWB, used by
[`createAwbExternal`](endpoints.md#createawbexternal). It shares most accessors with
`AwbIntern` but has its own defaults and export-specific fields.

### Export-specific

| Setter | Getter | Notes |
|---|---|---|
| `setService(string $service)` | `getService(): string` | Default `Export`. |
| `setDeliveryMode(string\|DeliveryMode $mode)` | `getDeliveryMode(): string` | Default `rutier`; only `rutier`/`aerian` accepted, others ignored. |
| `setDocumentType(string\|DocumentType $type)` | `getDocumentType(): string` | Default `document`; only `document`/`non document` accepted. |
| `setCountry(string $country)` | `getCountry(): string` | Recipient country (required for export). |

### Shared with AwbIntern

`AwbExtern` also provides: `setEnvelopes`/`getEnvelopes`, `setParcels`/`getParcels`,
`setWeight`/`getWeight`, `setSizes`/`getSizes` (plus `setHeight`/`setLength`/`setWidth`),
`setReimbursement`/`getReimbursement`, `setCurrency`/`getCurrency`,
`setDeclaredValue`/`getDeclaredValue`, `setPaymentType`/`getPaymentType` (default
`expeditor`), `setRefund`/`getRefund`, `setReturnPayment`/`getReturnPayment` (default `''`),
`setNotes`/`getNotes`, `setContents`/`getContents`, `setCostCenter`/`getCostCenter`,
`setUITCode`/`getUITCode`, `setBank`/`getBank`, `setIban`/`getIban`, the recipient
address/contact setters and the sender address/contact setters (same names as
`AwbIntern`, including `setCounty`, `setCity`, `setRecipientName`, `setContactPerson`,
`setPhone`, `setAltPhone`, `setEmail`, and the `setSender*` family).

Options: `getOptions()`, `addOption(string)`, `resetOptions()`. Unlike `AwbIntern`,
`AwbExtern` has **no** `setOptions()` and no non-EU (`setIsValueUnderThreshold` etc.)
methods.

### Result state (response)

`hasErrors(): bool`, `getErrors(): array`, `getAwb(): ?string`. `AwbExtern` has no
`getDetails()`.

---

## Pudo

`Fancourier\Objects\Pudo` — a Pick Up / Drop Off point (FANbox, PayPoint or office).
Built by [`getPudo`](endpoints.md#getpudo).

| Getter | Returns |
|---|---|
| `getId(): string` | PUDO id. |
| `getName(): string` | Name. |
| `getRoutingLocation(): string` | Routing location. |
| `getDescription(): string` | Description. |
| `getLatitude(): string` / `getLongitude(): string` | Coordinates (returned as strings). |
| `getAddress(): array` | Address block. |
| `getSchedule(): array` | Opening schedule. |
| `getDrawer(): array` | Drawer information. |
| `getPhones(): array` | Phone numbers. |
| `getEmail(): string` | Email. |
| `getHighDemand(): bool` | High-demand flag. |
| `getPaymentMethods(): array` | Accepted payment methods. |
| `getArray(): array` | The object as an API-shaped array. |

Use `Pudo::getId()` with `AwbIntern::setPickupLocation()` / `setDropOffLocation()`.

## Country

`Fancourier\Objects\Country` — an export destination country, built by
[`getCountries`](endpoints.md#getcountries).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Identification. |
| `getCode(): string` | ISO-style country code. |
| `hasAirShipping(): bool` | Delivery mode `2` is available. |
| `hasLandShipping(): bool` | Delivery mode `1` is available. |
| `getShipping(): array<int, string>` | Available delivery modes keyed by id. |

## City

`Fancourier\Objects\City` — a Romanian locality, built by
[`getCities`](endpoints.md#getcities).

| Getter | Returns |
|---|---|
| `getId(): string` | Locality id. |
| `getName(): string` | Locality name. |
| `getCounty(): string` | County name. |
| `getAgency(): string` | Agency. |
| `getExtKm(): float` | Extra-kilometre value (numeric strings normalized to float). |

## CityExternal

`Fancourier\Objects\CityExternal` — a locality in an export country, built by
[`getCitiesExternal`](endpoints.md#getcitiesexternal).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Identification. |
| `getCounty(): string` | County / region. |
| `getCountry(): string` | Country. |

## County

`Fancourier\Objects\County` — a Romanian county, built by
[`getCounties`](endpoints.md#getcounties).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Identification. |

## CountyExternal

`Fancourier\Objects\CountyExternal` — a county in an export country, built by
[`getCountiesExternal`](endpoints.md#getcountiesexternal).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Identification. |
| `getCode(): string` | County code. |
| `getCountry(): string` | Country. |

## Street

`Fancourier\Objects\Street` — a street, built by
[`getStreets`](endpoints.md#getstreets).

| Getter | Returns |
|---|---|
| `getId(): string` | Street id. |
| `getName(): string` | Street name (API key `street`). |
| `getType(): string` | Street type. |
| `getCounty(): string` / `getCity(): string` | Location. |
| `hasZipCode(string $zipCode): bool` | Whether details exist for a postal code. |
| `getDetails(string $zipCode): array\|false` | Detail row for a postal code (keyed by `zipCode` internally). |
| `getArray(): array` | The object as an API-shaped array. |

## Service

`Fancourier\Objects\Service` — a service type, built by
[`getServices`](endpoints.md#getservices).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` / `getDescription(): string` | Identification and description. |

## ServiceOption

`Fancourier\Objects\ServiceOption` — a service option, built by
[`getServiceOptions`](endpoints.md#getserviceoptions).

| Getter | Returns |
|---|---|
| `getCode(): string` / `getName(): string` | Option code and label. |

## AwbEvent

`Fancourier\Objects\AwbEvent` — an AWB event definition, built by
[`getAwbEvents`](endpoints.md#getawbevents).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Event id and label. |

The event ids are returned verbatim by the API.

## CourierOrderEvent

`Fancourier\Objects\CourierOrderEvent` — a courier order event definition, built by
[`getCourierOrderEvents`](endpoints.md#getcourierorderevents).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Event id and label. |

The event ids are returned verbatim by the API.

## AwbTracker

`Fancourier\Objects\AwbTracker` — tracking information for one AWB, built by
[`trackAwb`](endpoints.md#trackawb).

| Getter | Returns |
|---|---|
| `getAwbNumber(): string` | AWB number. |
| `getMessage(): string` | Status message. |
| `getContent(): string` | Content / order reference. |
| `getPaymentDate(): string` | Payment date. |
| `getReturnAwbNumber(): string` | Return AWB. |
| `getRedirectionAwbNumber(): string` | Redirection AWB. |
| `getReimbursementAwbNumber(): string` | Reimbursement AWB. |
| `getOPODAwbNumber(): string` | OPOD AWB (acronym-cased method). |
| `hasConfirmation(): bool` | Whether a named confirmation exists. |
| `getConfirmation(): array` | Confirmation block. |
| `getOTD(): string` | On-time-delivery duration. |
| `getEvents(): array` | Event rows (`id`, `name`, `location`, `date`). |
| `getStatus(): array` | Last event, or a synthesized status from `message`. |

## CourierOrderTracker

`Fancourier\Objects\CourierOrderTracker` — tracking information for one courier order,
built by [`trackCourierOrder`](endpoints.md#trackcourierorder).

| Getter | Returns |
|---|---|
| `getOrderId(): string` | Order id. |
| `getOrderNo(): string` | Order number. |
| `getMessage(): string` | Status message. |
| `getEvents(): array` | Event rows. |
| `getStatus(): array` | Last event, or a synthesized status from `message`. |

## ShippingSlip

`Fancourier\Objects\ShippingSlip` — a shipping-slip (borderou) row, built by
[`getShippingSlip`](endpoints.md#getshippingslip).

| Getter | Returns |
|---|---|
| `getAwbNumber(): string` | AWB number. |
| `getService(): string` / `getServiceId(): string` | Service. |
| `getWeight(): string` | Weight. |
| `getHeight(): float` / `getWidth(): float` / `getLength(): float` | Dimensions. |
| `getPayment(): string` / `getReturnPayment(): string` | Payment types. |
| `getReimbursement(): float` | Cash on delivery (`cod`). |
| `getDeclaredValue(): float` | Declared value. |
| `getNotes(): string` / `getContents(): string` | Observations and content. |
| `getEnvelopes(): int` / `getParcels(): int` | Package counts. |
| `getDateTime(): string` | Slip date/time. |
| `getCost(): float` / `getCostCenter(): string` / `getRefund(): string` / `getCurrency(): string` | Cost details. |
| `getRecipient(): array` / `getSender(): array` | Party blocks. |

## BankTransfer

`Fancourier\Objects\BankTransfer` — a cash-on-delivery bank transfer, built by
[`getBankTransfers`](endpoints.md#getbanktransfers).

| Getter | Returns |
|---|---|
| `getAwbNumber(): string` / `getAwbDate(): string` | AWB information. |
| `getReturnAwbNumber(): string` / `getReimbursementAwbNumber(): string` | Related AWBs. |
| `getAmountCollected(): float` | Amount collected. |
| `getContent(): string` | Content / order reference. |
| `getTransferDate(): string` / `getTransactionType(): string` / `getTransactionDate(): string` | Transfer details. |
| `getRecipientName(): string` / `getRecipientContactPerson(): string` / `getRecipientCity(): string` | Recipient. |
| `getSenderName(): string` / `getSenderContactPerson(): string` | Sender. |

## CourierOrder

`Fancourier\Objects\CourierOrder` — a courier order row, built by
[`getCourierOrders`](endpoints.md#getcourierorders).

| Getter | Returns |
|---|---|
| `getId(): string` / `getNumber(): string` | Identification. |
| `getStatus(): array` | Status block. |
| `getDate(): string` / `getHour(): string` | Creation date/time. |
| `getEnvelopes(): int` / `getParcels(): int` | Package counts. |
| `getWeight(): float` | Weight. |
| `getDimensions(): array` / `getHeight()` / `getLength()` / `getWidth()` | Dimensions (floats). |
| `getPickupDate(): string` / `getPickupHours(): array` | Pickup window. |
| `getNotes(): string` | Observations. |
| `getType(): string` | Order type. |
| `getAwbs(): array` | Associated AWBs. |
| `getSender(): array` | Sender block. |

## Branch

`Fancourier\Objects\Branch` — a branch (sucursala), built by
[`getBranches`](endpoints.md#getbranches).

| Getter | Returns |
|---|---|
| `getId(): string` / `getName(): string` | Identification. |
| `getBank(): string` / `getBankAccount(): string` | Banking details. |
| `getEmail(): string` / `getPhone(): string` / `getSecondaryPhone(): string` / `getContactPerson(): string` | Contact. |
| `getCounty(): string` / `getCity(): string` | Address. |
| `getCountyId(): string` / `getCityId(): string` | Address ids. |
| `getStreet(): string` / `getStreetNo(): string` / `getPostalCode(): string` | Street address. |
| `getBuilding(): string` / `getEntrance(): string` / `getFloor(): string` / `getApartment(): string` | Address extras. |

`getPostalCode()` reads the API's `zipcode` key (with a `zipCode` fallback for older
payloads).
