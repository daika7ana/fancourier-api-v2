<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class ShippingSlip
{
	protected string $awbNumber = '';

	protected string $service = '';
	protected string $serviceId = '';

	protected string $weight = '';
	protected float $height = 0.0;
	protected float $width = 0.0;
	protected float $length = 0.0;

	protected string $payment = '';
	protected string $returnPayment = '';
	protected float $cod = 0.0;
	protected float $declaredValue = 0.0;

	protected string $notes = '';
	protected string $contents = '';

    protected int $envelopes = 0;
    protected int $parcels = 0;

    protected string $dateTime = '';
    protected float $cost = 0.0;
    protected string $costCenter = '';
    protected string $refund = '';
    protected string $currency = '';

	/** @var array<string, mixed> */
	protected array $recipient = [];
	/** @var array<string, mixed> */
	protected array $sender = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		$this->awbNumber	= (string) ($data['info']['awbNumber'] ?? '');

		$this->service		= (string) ($data['info']['service'] ?? '');
		$this->serviceId	= (string) ($data['info']['serviceId'] ?? '');

		// weight is exposed as a string by the getter; keep the numeric default "0".
		$this->weight		= (string) ($data['info']['weight'] ?? 0);
		$this->height		= self::toFloat($data['info']['dimensions']['height'] ?? null);
		$this->width		= self::toFloat($data['info']['dimensions']['width'] ?? null);
		$this->length		= self::toFloat($data['info']['dimensions']['length'] ?? null);

		$this->payment		= (string) ($data['info']['payment'] ?? '');
		$this->returnPayment	= (string) ($data['info']['returnPayment'] ?? '');
		$this->cod			= self::toFloat($data['info']['cod'] ?? null);
		$this->declaredValue	= self::toFloat($data['info']['declaredValue'] ?? null);
		$this->notes		= (string) ($data['info']['observations'] ?? '');
		$this->contents		= (string) ($data['info']['content'] ?? '');

		$this->envelopes	= (int) ($data['info']['packages']['envelope'] ?? 0);
		$this->parcels		= (int) ($data['info']['packages']['parcel'] ?? 0);

		$this->dateTime		= (string) ($data['info']['date'] ?? '');

		$this->cost			= self::toFloat($data['info']['cost'] ?? null);
		$this->costCenter	= (string) ($data['info']['costCenter'] ?? '');

		$this->refund		= (string) ($data['info']['refund'] ?? '');

		$this->currency		= (string) ($data['info']['currency'] ?? '');

		$recipient = $data['recipient'] ?? null;
 		$this->recipient	= is_array($recipient) ? $recipient : [];
		$sender = $data['sender'] ?? null;
 		$this->sender		= is_array($sender) ? $sender : [];
		}

	/**
	 * Normalize a JSON scalar that must become a float.
	 */
	private static function toFloat(mixed $value): float
		{
		return is_numeric($value) ? (float) $value : 0.0;
		}

	public function getAwbNumber(): string
		{
		return $this->awbNumber;
		}

	public function getService(): string
		{
		return $this->service;
		}

	public function getServiceId(): string
		{
		return $this->serviceId;
		}

	public function getWeight(): string
		{
		return $this->weight;
		}

	public function getHeight(): float
		{
		return $this->height;
		}

	public function getWidth(): float
		{
		return $this->width;
		}

	public function getLength(): float
		{
		return $this->length;
		}

	public function getPayment(): string
		{
		return $this->payment;
		}

	public function getReturnPayment(): string
		{
		return $this->returnPayment;
		}

	public function getReimbursement(): float
		{
		return $this->cod;
		}

	public function getDeclaredValue(): float
		{
		return $this->declaredValue;
		}

	public function getNotes(): string
		{
		return $this->notes;
		}

	public function getContents(): string
		{
		return $this->contents;
		}

	public function getEnvelopes(): int
		{
		return $this->envelopes;
		}

	public function getParcels(): int
		{
		return $this->parcels;
		}

	public function getDateTime(): string
		{
		return $this->dateTime;
		}

	public function getCost(): float
		{
		return $this->cost;
		}

	public function getCostCenter(): string
		{
		return $this->costCenter;
		}

	public function getRefund(): string
		{
		return $this->refund;
		}

	public function getCurrency(): string
		{
		return $this->currency;
		}

	/** @return array<string, mixed> */
	public function getRecipient(): array
		{
		return $this->recipient;
		}

	/** @return array<string, mixed> */
	public function getSender(): array
		{
		return $this->sender;
		}


}


/*
            [info] => Array
                (
                    [awbNumber] => 6324356450139
                    [serviceId] => 4
                    [weight] => 1
                    [dimensions] => Array
                        (
                            [height] => 10
                            [width] => 10
                            [length] => 10
                        )

                    [payment] => sender
                    [cod] => 75.29
                    [returnPayment] => expeditor
                    [declaredValue] => 59.88
                    [observations] => POS
                    [content] => Order #465
                    [date] => 2023-11-20 18:53:07
                    [service] => Cont Colector
                    [cost] => 14.4
                    [packages] => Array
                        (
                            [envelope] => 0
                            [parcel] => 1
                        )

                    [costCenter] => 
                    [refund] => 
                    [currency] => LEI
                )

            [recipient] => Array
                (
                    [name] => COM S.R.L.
                    [contactPerson] => COM S.R.L.
                    [phone] => +40 720 000 000
                    [secondaryPhone] => 
                    [email] => email@example.com
                    [address] => Array
                        (
                            [localityId] => 233
                            [locality] => Fetesti
                            [countyId] => 24
                            [county] => Ialomita
                            [agency] => Ialomita
                            [street] => Str. Grausor, bl. 0, sc. 0, et. 0, ap. 0
                            [streetNo] => 
                            [zipCode] => 000000
                            [building] => 
                            [entrance] => 
                            [floor] => 
                            [apartment] => 
                            [country] => Romania
                        )

                )

            [sender] => Array
                (
                    [name] => NETWORK SRL
                    [contactPerson] => 
                    [phone] => 0720000000
                    [secondaryPhone] => 
                    [email] => email@example.com
                    [address] => Array
                        (
                            [localityId] => 11
                            [locality] => Bucuresti
                            [countyId] => 10
                            [county] => Bucuresti
                            [agency] => Bucuresti
                            [street] => Ridicare din sediul FAN Otopeni (Sediu)
                            [streetNo] => 
                            [zipCode] => 000000
                            [building] => 
                            [entrance] => 
                            [floor] => 
                            [apartment] => 
                            [country] => Romania
                        )

                )

        )
*/
