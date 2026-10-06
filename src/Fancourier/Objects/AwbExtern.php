<?php

declare(strict_types=1);

namespace Fancourier\Objects;

use Fancourier\Enums\DeliveryMode;
use Fancourier\Enums\DocumentType;
use Fancourier\Enums\PaymentType;
use Fancourier\Request\AbstractRequest;

class AwbExtern
{
    // response fields only
    protected ?string $awb = null;
    /** @var array<int, mixed>|null */
    protected ?array $errors = null;
    protected bool $hasErrors = false;

    protected string $service = 'Export';				// "Export" sau "Export-Cont Colector"
    protected string $deliveryMode = 'rutier';			// "rutier" sau "aerian"
    protected string $documentType = 'document';		// "document" sau "non document"

    protected string $bank = '';	// optional						// info.bank
    protected string $iban = '';	// optional						// info.bankAccount
    protected int $envelopes = 0;	// optionalif parcels set	// info.packages.envelopes
    protected int $parcels = 0;	// optional if envelopes set	// info.packages.parcel
    // weight/dimensions/declaredValue are stored exactly as supplied: the default is
    // int 0 (pack() must emit 0, not 0.0) while a float supplied by the caller stays float.
    protected int|float $weight = 0;									// info.weight
    protected int|float $height = 0; // cm							// info.dimensions.length
    protected int|float $length = 0; // cm							// info.dimensions.height
    protected int|float $width = 0; // cm								// info.dimensions.width
    protected int|float $declaredValue = 0;								// info.declaredValue
    protected string $notes = '';		// observation					// info.observation
    protected string $contents = '';									// info.content

    protected string $costCenter = '';	// optional					// info.costCenter
    /** @var array<int, string> */
    protected array $options = [];	// optional					// info.options
    protected string $uitCode = '';	// optional					// info uitCode

    protected string $senderName = '';
    protected string $senderContactPerson = '';
    protected string $senderPhone = '';
    protected string $senderAltPhone = '';
    protected string $senderEmail = '';

    protected string $senderCounty = ''; // county							// info.sender.address.county
    protected string $senderCity = ''; // locality							// info.sender.address.locality
    protected string $senderStreet = '';										// info.sender.address.street
    protected string $senderNumber = '';									// info.sender.address.streetNo
    protected string $senderPostalCode = '';								// info.sender.address.zipcode
    protected string $senderBuilding = '';								// info.sender.address.building
    protected string $senderEntrance = '';								// info.sender.address.entrance
    protected string $senderFloor = '';									// info.sender.address.floor
    protected string $senderApartment = '';								// info.sender.address.apartment

    protected string $name = '';										// info.recipient.name
    protected string $contactPerson = '';								// info.recipient.contactPerson
    protected string $phone = '';										// info.recipient.phone
    protected string $altPhone = '';									// info.recipient.secondaryPhone
    protected string $email = '';									// info.recipient.email
    protected string $country = ''; // country							// info.recipient.address.country
    protected string $county = ''; // county							// info.recipient.address.county
    protected string $city = ''; // locality							// info.recipient.address.locality
    protected string $street = '';										// info.recipient.address.street
    protected string $number = '';									// info.recipient.address.streetNo
    protected string $postalCode = '';								// info.recipient.address.zipcode

    protected string $building = '';								// info.recipient.address.building
    protected string $entrance = '';								// info.recipient.address.entrance
    protected string $floor = '';									// info.recipient.address.floor
    protected string $apartment = '';								// info.recipient.address.apartment


    protected float|int|string $CoD = '';	// cash on delivery, optional			// info.cod
    protected string $currency = 'RON';								// info.currency (apare doar in borderou in documentatie, nu stiu daca afecteaza crearea de awb)
    protected string $paymentType = AbstractRequest::TYPE_SENDER;	// info.payment
    protected string $refund = '';	// refund payment			// info.refund
    protected string $returnPayment = ''; //refund	// info.returnPayment


    public function __construct() {}

    /** @return array<string, mixed> */
    public function pack(): array
    {

        $arr = [
            "info" => [
                "deliveryMode" => $this->deliveryMode,
                "service" => $this->service, // "Export" sau "Export-Cont Colector". Note that the API returns "The selected info.service is invalid" for "Export-Cont Colector". Use "Export" instead. If using CoD, it will be changed by fan courier automatically
                "contentType" => $this->documentType,
                "bank" => $this->bank, //optional
                "bankAccount" => $this->iban, //optional
                "packages" => [
                    "parcel" => $this->parcels,
                    "envelope" => $this->envelopes,
                ],
                "dimensions" => [
                    "length" => $this->length,
                    "height" => $this->height,
                    "width" => $this->width,
                ],
                "weight" => $this->weight, //obligatoriu
                "declaredValue" => $this->declaredValue, //optional

                "cod" => $this->CoD,	// optional - daca se doreste trimiterea cu ramburs
                // ponytail: default is 'RON', so "when set" is always true; emitted unconditionally to match AwbIntern. Add a nullable/unset state if the API ever needs currency suppressed.
                "currency" => $this->currency, // optional
                "payment" => $this->paymentType,
                "refund" => $this->refund,
                "returnPayment" => $this->returnPayment,

                "observation" => $this->notes, //obligatoriu
                "content" => $this->contents, //optional

                "costCenter" => $this->costCenter, //optional
                "options" => $this->options,
                "uitCode" => $this->uitCode,
            ],
            "sender" => [
                "name" => $this->senderName,
                "contactPerson" => $this->senderContactPerson, // obligatoriu
                "phone" => $this->senderPhone,
                "secondaryPhone" => $this->senderAltPhone, // optional
                "email" => $this->senderEmail,
                "address" => [ //obligatoriu
                    "county" => $this->senderCounty, // {{url}}/counties
                    "locality" => $this->senderCity, // {{url}}/localities
                    "street" => $this->senderStreet, // {{url}}/streets
                    "streetNo" => strval($this->senderNumber),
                    "zipCode" => $this->senderPostalCode,

                    "building" => $this->senderBuilding,
                    "entrance" => $this->senderEntrance,
                    "floor" => $this->senderFloor,
                    "apartment" => $this->senderApartment,
                ],
            ],
            "recipient" => [ //obligatoriu
                "name" => $this->name,
                "contactPerson" => $this->contactPerson, // obligatoriu
                "phone" => $this->phone,
                "secondaryPhone" => $this->altPhone, // optional
                "email" => $this->email,
                "address" => [ //obligatoriu
                    "country" => $this->country,
                    "region" => $this->county,
                    "locality" => $this->city,
                    "street" => $this->street,
                    "streetNo" => $this->number,
                    "zipCode" => $this->postalCode,

                    "building" => $this->building,
                    "entrance" => $this->entrance,
                    "floor" => $this->floor,
                    "apartment" => $this->apartment,
                ],
            ],
        ];

        return $arr;
    }


    public function getService(): string
    {
        return $this->service;
    }

    public function setService(string $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getDeliveryMode(): string
    {
        return $this->deliveryMode;
    }

    /**
     * @param string|DeliveryMode $deliveryMode
     * @return $this
     */
    public function setDeliveryMode(string|DeliveryMode $deliveryMode): static
    {
        $deliveryMode = $deliveryMode instanceof DeliveryMode ? $deliveryMode->value : $deliveryMode;
        $deliveryMode = strtolower($deliveryMode);
        if (($deliveryMode == 'rutier') || ($deliveryMode == 'aerian')) {
            $this->deliveryMode = $deliveryMode;
        }

        return $this;
    }

    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * @param string|DocumentType $documentType
     * @return $this
     */
    public function setDocumentType(string|DocumentType $documentType): static
    {
        $documentType = $documentType instanceof DocumentType ? $documentType->value : $documentType;
        $documentType = strtolower($documentType);
        if (($documentType == 'document') || ($documentType == 'non document')) {
            $this->documentType = $documentType;
        }

        return $this;
    }

    public function getBank(): string
    {
        return $this->bank;
    }

    public function setBank(string $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getIban(): string
    {
        return $this->iban;
    }

    public function setIban(string $iban): static
    {
        $this->iban = $iban;

        return $this;
    }

    public function getEnvelopes(): int
    {
        return $this->envelopes;
    }

    public function setEnvelopes(int|string $envelopes): static
    {
        $this->envelopes = (int) $envelopes;

        return $this;
    }

    public function getParcels(): int
    {
        return $this->parcels;
    }

    public function setParcels(int|string $parcels): static
    {
        $this->parcels = (int) $parcels;

        return $this;
    }

    public function getWeight(): int|float
    {
        return $this->weight;
    }

    public function setWeight(int|float|string $weight): static
    {
        $this->weight = is_numeric($weight) ? $weight + 0 : 0;

        return $this;
    }

    /** @return array{length: int|float, height: int|float, width: int|float} */
    public function getSizes(): array
    {
        return [
            'length' => $this->length,
            'height' => $this->height,
            'width' => $this->width,
        ];
    }

    /**
     * @param int|float|string $length_cm
     * @param int|float|string $height_cm
     * @param int|float|string $width_cm
     */
    public function setSizes(int|float|string $length_cm, int|float|string $height_cm, int|float|string $width_cm): static
    {
        // keep int-vs-float exactly as supplied; numeric strings normalise
        $length = is_numeric($length_cm) ? $length_cm + 0 : 0;
        $height = is_numeric($height_cm) ? $height_cm + 0 : 0;
        $width = is_numeric($width_cm) ? $width_cm + 0 : 0;

        if (($length > 0) && ($height > 0) && ($width > 0)) {
            $this->length = $length;
            $this->height = $height;
            $this->width = $width;

            return $this;
        }

        throw new \Exception("You can't set sizes to 0 or lower");
    }

    public function getHeight(): int|float
    {
        return $this->height;
    }

    public function setHeight(int|float|string $height): static
    {
        $this->height = is_numeric($height) ? $height + 0 : 0;

        return $this;
    }

    public function getLength(): int|float
    {
        return $this->length;
    }

    public function setLength(int|float|string $length): static
    {
        $this->length = is_numeric($length) ? $length + 0 : 0;

        return $this;
    }

    public function getWidth(): int|float
    {
        return $this->width;
    }

    public function setWidth(int|float|string $width): static
    {
        $this->width = is_numeric($width) ? $width + 0 : 0;

        return $this;
    }

    public function getReimbursement(): float|int|string
    {
        return $this->CoD;
    }

    public function setReimbursement(float|int|string $cashondelivery): static
    {
        $this->CoD = $cashondelivery;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getDeclaredValue(): int|float
    {
        return $this->declaredValue;
    }

    public function setDeclaredValue(int|float|string $declaredValue): static
    {
        $this->declaredValue = is_numeric($declaredValue) ? $declaredValue + 0 : 0;

        return $this;
    }

    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string|PaymentType $paymentType): static
    {
        $this->paymentType = $paymentType instanceof PaymentType ? $paymentType->value : $paymentType;

        return $this;
    }

    public function getRefund(): string
    {
        return $this->refund;
    }

    public function setRefund(string $refund): static
    {
        $this->refund = $refund;

        return $this;
    }

    public function getReturnPayment(): string
    {
        return $this->returnPayment;
    }

    public function setReturnPayment(string|PaymentType $reimbursementPaymentType): static
    {
        $this->returnPayment = $reimbursementPaymentType instanceof PaymentType ? $reimbursementPaymentType->value : $reimbursementPaymentType;

        return $this;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function setContents(string $contents): static
    {
        $this->contents = $contents;

        return $this;
    }

    public function getCostCenter(): string
    {
        return $this->costCenter;
    }

    public function setCostCenter(string $costCenter): static
    {
        $this->costCenter = $costCenter;

        return $this;
    }

    /** @return array<int, string> */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $option
     * @return $this
     */
    public function addOption(string $option): static
    {
        if (strlen($option) == 1) {
            $this->options[] = strtoupper($option);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetOptions(): static
    {
        $this->options = [];

        return $this;
    }

    public function getUITCode(): string
    {
        return $this->uitCode;
    }

    public function setUITCode(string $uitCode): static
    {
        $this->uitCode = $uitCode;

        return $this;
    }

    /******
    SENDER
    *******/


    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function setSenderName(string $sender): static
    {
        $this->senderName = $sender;

        return $this;
    }

    public function getSenderContactPerson(): string
    {
        return $this->senderContactPerson;
    }

    public function setSenderContactPerson(string $contactPerson): static
    {
        $this->senderContactPerson = $contactPerson;

        return $this;
    }

    public function getSenderPhone(): string
    {
        return $this->senderPhone;
    }

    public function setSenderPhone(string $phone): static
    {
        $this->senderPhone = $phone;

        return $this;
    }


    public function getSenderAltPhone(): string
    {
        return $this->senderAltPhone;
    }

    public function setSenderAltPhone(string $phone): static
    {
        $this->senderAltPhone = $phone;

        return $this;
    }


    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $email): static
    {
        $this->senderEmail = $email;

        return $this;
    }

    public function getSenderCounty(): string
    {
        return $this->senderCounty;
    }

    public function setSenderCounty(string $county): static
    {
        $this->senderCounty = $county;

        return $this;
    }

    public function getSenderCity(): string
    {
        return $this->senderCity;
    }

    public function setSenderCity(string $city): static
    {
        $this->senderCity = $city;

        return $this;
    }

    public function getSenderStreet(): string
    {
        return $this->senderStreet;
    }

    public function setSenderStreet(string $street): static
    {
        $this->senderStreet = $street;

        return $this;
    }

    public function getSenderNumber(): string
    {
        return $this->senderNumber;
    }

    public function setSenderNumber(string $number): static
    {
        $this->senderNumber = $number;

        return $this;
    }

    public function getSenderPostalCode(): string
    {
        return $this->senderPostalCode;
    }

    public function setSenderPostalCode(string $postalCode): static
    {
        $this->senderPostalCode = $postalCode;

        return $this;
    }

    public function getSenderBuilding(): string
    {
        return $this->senderBuilding;
    }

    public function setSenderBuilding(string $building): static
    {
        $this->senderBuilding = $building;

        return $this;
    }

    public function getSenderEntrance(): string
    {
        return $this->senderEntrance;
    }

    public function setSenderEntrance(string $entrance): static
    {
        $this->senderEntrance = $entrance;

        return $this;
    }

    public function getSenderFloor(): string
    {
        return $this->senderFloor;
    }

    public function setSenderFloor(string $floor): static
    {
        $this->senderFloor = $floor;

        return $this;
    }

    public function getSenderApartment(): string
    {
        return $this->senderApartment;
    }

    public function setSenderApartment(string $apartment): static
    {
        $this->senderApartment = $apartment;

        return $this;
    }

    /***********
    RECIPIENT
    ************/
    public function getRecipientName(): string
    {
        return $this->name;
    }

    public function setRecipientName(string $recipient): static
    {
        $this->name = $recipient;

        return $this;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): static
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }


    public function getAltPhone(): string
    {
        return $this->altPhone;
    }

    public function setAltPhone(string $phone): static
    {
        $this->altPhone = $phone;

        return $this;
    }


    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCounty(): string
    {
        return $this->county;
    }

    public function setCounty(string $county): static
    {
        $this->county = $county;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getBuilding(): string
    {
        return $this->building;
    }

    public function setBuilding(string $building): static
    {
        $this->building = $building;

        return $this;
    }

    public function getEntrance(): string
    {
        return $this->entrance;
    }

    public function setEntrance(string $entrance): static
    {
        $this->entrance = $entrance;

        return $this;
    }

    public function getFloor(): string
    {
        return $this->floor;
    }

    public function setFloor(string $floor): static
    {
        $this->floor = $floor;

        return $this;
    }

    public function getApartment(): string
    {
        return $this->apartment;
    }

    public function setApartment(string $apartment): static
    {
        $this->apartment = $apartment;

        return $this;
    }


    // ********************************************
    // ************** FUNCTII PT REZULTATE ********
    // ********************************************

    /**
     * @param array<string, mixed> $data
     */
    public function setResult(array $data): void
    {
        $this->hasErrors = false;
        $this->errors = [];

        if (isset($data['errors']) && is_array($data['errors']) && (count($data['errors']) > 0)) {
            $this->hasErrors = true;
            $this->errors = $data['errors'];
        }

        $this->awb = (string) $data['awbNumber'];
    }


    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /** @return array<int, mixed> */
    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function getAwb(): ?string
    {
        return $this->awb;
    }

}
