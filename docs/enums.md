# Enums and code lists

2.0 ships each code list as string-backed enum classes in `Fancourier\Enums\`.
Adopting the enums is **optional** — plain string arguments keep working. Where a setter
accepts `string|Enum`, either form is valid.

```php
use Fancourier\Enums\PaymentType;

$awb->setPaymentType(PaymentType::Expeditor);       // enum
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

