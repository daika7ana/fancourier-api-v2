# Enums and code lists

2.0 ships each code list **additively**: as string-backed enum classes in
`Fancourier\Enums\`, and (in parallel) as `public const` values on
`Fancourier\Request\AbstractRequest`. Adopting the enums is **optional** — plain string
arguments keep working. Where a setter accepts `string|Enum`, either form is valid.

```php
use Fancourier\Enums\PaymentType;
use Fancourier\Request\CreateAwb;

$awb->setPaymentType(PaymentType::Expeditor);       // enum
$awb->setPaymentType(CreateAwb::TYPE_SENDER);       // constant (also an inherited const)
$awb->setPaymentType('expeditor');                  // raw string
```

All enums are backed by `string` and live in the `Fancourier\Enums` namespace.

## Enums

### PaymentType

| Case | Value | Used by |
|---|---|---|
| `PaymentType::Expeditor` | `expeditor` | `AwbIntern::setPaymentType()` / `setReturnPayment()`, `AwbExtern::setPaymentType()` / `setReturnPayment()` |
| `PaymentType::Destinatar` | `destinatar` | same |
| `PaymentType::Altul` | `Altul` | Defined, but `GetCosts::setPaymentType()` rejects it (see note below). |

> `GetCosts::setPaymentType()` validates against `destinatar` / `expeditor` only and throws
> `\InvalidArgumentException` otherwise, even though the enum defines `Altul`. The AWB
> objects do not validate the value.

### DeliveryMode

| Case | Value | Used by |
|---|---|---|
| `DeliveryMode::Rutier` | `rutier` | `AwbExtern::setDeliveryMode()`, `GetCostsExternal::setDeliveryMode()` |
| `DeliveryMode::Aerian` | `aerian` | same |

### DocumentType

| Case | Value | Used by |
|---|---|---|
| `DocumentType::Document` | `document` | `AwbExtern::setDocumentType()` |
| `DocumentType::NonDocument` | `non document` | same |

> `GetCostsExternal::setDocumentType()` takes a plain `string` (not the enum) but accepts
> the same two values.

### OrderType

| Case | Value | Used by |
|---|---|---|
| `OrderType::Standard` | `Standard` | `CreateCourierOrder::setOrderType()` |
| `OrderType::ExpressLoco1h` | `Express Loco 1h` | same |
| `OrderType::ExpressLoco2h` | `Express Loco 2h` | same |
| `OrderType::ExpressLoco4h` | `Express Loco 4h` | same |
| `OrderType::ExpressLoco6h` | `Express Loco 6h` | same |

### PudoType

| Case | Value | Used by |
|---|---|---|
| `PudoType::Fanbox` | `fanbox` | `GetPudo::setType()` |
| `PudoType::Paypoint` | `paypoint` | same |
| `PudoType::Office` | `office` | same |

### Language

| Case | Value | Used by |
|---|---|---|
| `Language::Ro` | `ro` | `PrintAwb::setLang()`, `LanguageTrait::setLanguage()` |
| `Language::En` | `en` | same |

`LanguageTrait` is used by `GetAwbEvents`, `GetCourierOrderEvents` and
`TrackAwb`/`TrackCourierOrder`; unsupported values are ignored.

### LabelFormat

| Case | Value | Used by |
|---|---|---|
| `LabelFormat::A4` | `A4` | `PrintAwb::setSize()` |
| `LabelFormat::A5` | `A5` | same |
| `LabelFormat::A6` | `A6` | same (`A6` is for ePOD only) |

## Code-list constants on AbstractRequest

`Fancourier\Request\AbstractRequest` defines the following constants. Because every
request class extends `AbstractRequest`, they are reachable through any request class
(e.g. `Fancourier\Request\CreateAwb::TYPE_SENDER`) as well as directly.

### Party types

| Constant | Value |
|---|---|
| `AbstractRequest::TYPE_RECIPIENT` | `destinatar` |
| `AbstractRequest::TYPE_SENDER` | `expeditor` |
| `AbstractRequest::TYPE_OTHER` | `Altul` |

### PUDO types

| Constant | Value |
|---|---|
| `AbstractRequest::PUDO_FANBOX` | `fanbox` |
| `AbstractRequest::PUDO_PAYPOINT` | `paypoint` |
| `AbstractRequest::PUDO_OFFICE` | `office` |

### AWB service options

| Constant | Value |
|---|---|
| `OPTION_OPEN_ON_DELIVERY` | `A` |
| `OPTION_OPOD` | `B` |
| `OPTION_DROP_OFF_OFFICE` | `C` |
| `OPTION_PICKUP_OFFICE` | `D` |
| `OPTION_DROP_OFF_PAYPOINT` | `E` |
| `OPTION_DELIVERY_PAYPOINT` | `F` |
| `OPTION_SMS_BUSINESS` | `M` |
| `OPTION_PICKUP_PREALERT` | `O` |
| `OPTION_PREALERT` | `P` |
| `OPTION_SATURDAY_DELIVERY` | `S` |
| `OPTION_PICKUP_LOCKER` | `V` |
| `OPTION_DROP_OFF_LOCKER` | `W` |
| `OPTION_EPOD` | `X` |
| `OPTION_MPOS` | `Y` |

Prefix all with `AbstractRequest::`.

### Courier order types

| Constant | Value |
|---|---|
| `ORDER_TYPE_STANDARD` | `Standard` |
| `ORDER_TYPE_EXPRESS_LOCO_1H` | `Express Loco 1h` |
| `ORDER_TYPE_EXPRESS_LOCO_2H` | `Express Loco 2h` |
| `ORDER_TYPE_EXPRESS_LOCO_4H` | `Express Loco 4h` |
| `ORDER_TYPE_EXPRESS_LOCO_6H` | `Express Loco 6h` |

### Service types

| Constant | Value |
|---|---|
| `SERVICE_STANDARD` | `Standard` |
| `SERVICE_REDCODE` | `RedCode` |
| `SERVICE_CASH_ON_DELIVERY` | `Cont Colector` |
| `SERVICE_EXPRESS_LOCO_2H` | `Express Loco 2H` |
| `SERVICE_EXPRESS_LOCO_4H` | `Express Loco 4H` |
| `SERVICE_EXPRESS_LOCO_6H` | `Express Loco 6H` |
| `SERVICE_EXPORT` | `Export` |
| `SERVICE_REDCODE_CASH_ON_DELIVERY` | `Red code-Cont Colector` |
| `SERVICE_EXPRESS_LOCO_2H_CASH_ON_DELIVERY` | `Express Loco 2H-Cont Colector` |
| `SERVICE_EXPRESS_LOCO_4H_CASH_ON_DELIVERY` | `Express Loco 4H-Cont Colector` |
| `SERVICE_EXPRESS_LOCO_6H_CASH_ON_DELIVERY` | `Express Loco 6H-Cont Colector` |
| `SERVICE_EXPRESS_LOCO_1H` | `Express Loco 1H` |
| `SERVICE_EXPRESS_LOCO_1H_CASH_ON_DELIVERY` | `Express Loco 1H-Cont Colector` |
| `SERVICE_EXPORT_CASH_ON_DELIVERY` | `Export-Cont Colector` |
| `SERVICE_COLLECT_POINT` | `CollectPoint` |
| `SERVICE_COLLECT_POINT_CASH_ON_DELIVERY` | `CollectPoint Cont Colector` |
| `SERVICE_WHITE_GOODS` | `Produse Albe` |
| `SERVICE_WHITE_GOODS_CASH_ON_DELIVERY` | `Produse Albe-Cont Colector` |
| `SERVICE_FREIGHT` | `Transport Marfa` |
| `SERVICE_FREIGHT_CASH_ON_DELIVERY` | `Transport Marfa-Cont Colector` |
| `SERVICE_FREIGHT_WHITE_GOODS` | `Transport Marfa Produse Albe` |
| `SERVICE_FREIGHT_WHITE_GOODS_CASH_ON_DELIVERY` | `Transport Marfa Produse Albe-Cont Colector` |
| `SERVICE_FANBOX` | `FANbox` |
| `SERVICE_FANBOX_CASH_ON_DELIVERY` | `FANbox Cont Colector` |

### AWB event codes

| Constant | Value | Constant | Value |
|---|---|---|---|
| `AWB_EVENT_C0` | `C0` | `AWB_EVENT_S11` | `S11` |
| `AWB_EVENT_C1` | `C1` | `AWB_EVENT_S12` | `S12` |
| `AWB_EVENT_H0` | `H0` | `AWB_EVENT_S14` | `S14` |
| `AWB_EVENT_H1` | `H1` | `AWB_EVENT_S15` | `S15` |
| `AWB_EVENT_H2` | `H2` | `AWB_EVENT_S16` | `S16` |
| `AWB_EVENT_H3` | `H3` | `AWB_EVENT_S19` | `S19` |
| `AWB_EVENT_H4` | `H4` | `AWB_EVENT_S20` | `S20` |
| `AWB_EVENT_H10` | `H10` | `AWB_EVENT_S21` | `S21` |
| `AWB_EVENT_H11` | `H11` | `AWB_EVENT_S22` | `S22` |
| `AWB_EVENT_H12` | `H12` | `AWB_EVENT_S24` | `S24` |
| `AWB_EVENT_H13` | `H13` | `AWB_EVENT_S25` | `S25` |
| `AWB_EVENT_H15` | `H15` | `AWB_EVENT_S27` | `S27` |
| `AWB_EVENT_H17` | `H17` | `AWB_EVENT_S28` | `S28` |
| `AWB_EVENT_S1` | `S1` | `AWB_EVENT_S30` | `S30` |
| `AWB_EVENT_S2` | `S2` | `AWB_EVENT_S33` | `S33` |
| `AWB_EVENT_S3` | `S3` | `AWB_EVENT_S35` | `S35` |
| `AWB_EVENT_S4` | `S4` | `AWB_EVENT_S37` | `S37` |
| `AWB_EVENT_S5` | `S5` | `AWB_EVENT_S38` | `S38` |
| `AWB_EVENT_S6` | `S6` | `AWB_EVENT_S42` | `S42` |
| `AWB_EVENT_S7` | `S7` | `AWB_EVENT_S43` | `S43` |
| `AWB_EVENT_S8` | `S8` | `AWB_EVENT_S46` | `S46` |
| `AWB_EVENT_S9` | `S9` | `AWB_EVENT_S47` | `S47` |
| `AWB_EVENT_S10` | `S10` | `AWB_EVENT_S49` | `S49` |
| `AWB_EVENT_S50` | `S50` | | |

The library stores only the codes; the human-readable labels come from the API (see
`Response\GetAwbEvents`, which maps each id to an `AwbEvent` name). The official API spec
lists the codes without a machine-readable label set, so no labels are documented here.

### Courier order event codes

These are `int` constants (not strings).

| Constant | Value |
|---|---|
| `ORDER_EVENT_PENDING` | `0` |
| `ORDER_EVENT_PLACED` | `1` |
| `ORDER_EVENT_PICKED_UP` | `2` |
| `ORDER_EVENT_NOT_PICKED_UP` | `3` |
| `ORDER_EVENT_CANCELLED` | `4` |
| `ORDER_EVENT_POSTPONED` | `5` |
| `ORDER_EVENT_SENDER_NOT_FOUND` | `8` |
| `ORDER_EVENT_PICKED_UP_BORDEROU` | `12` |
| `ORDER_EVENT_CANCELLATION_IN_PROGRESS` | `99` |
