<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class CourierOrder
{
	protected string $id = '';
	protected string $number = '';
	/** @var array<string, mixed> */
	protected array $status = [];
	protected string $date = '';
	protected string $hour = '';
	/** @var array<string, mixed> */
	protected array $packages = [];
	protected float $weight = 0.0;
	/** @var array<string, mixed> */
	protected array $dimensions = [];
	protected string $pickupDate = '';
	/** @var array<string, mixed> */
	protected array $pickupHours = [];
	protected string $observation = '';
	protected string $type = '';
	/** @var array<int, mixed> */
	protected array $awbs = [];
	/** @var array<string, mixed> */
	protected array $sender = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		$this->id = (string) ($data['info']['id'] ?? '');
		$this->number = (string) ($data['info']['number'] ?? '');
		// status is normally an assoc array but can arrive as a scalar message.
		$status = $data['info']['status'] ?? null;
		$this->status = is_array($status) ? $status : [];
		$this->date = (string) ($data['info']['date'] ?? '');
		$this->hour = (string) ($data['info']['hour'] ?? '');
		$packages = $data['info']['packages'] ?? null;
		$this->packages = is_array($packages) ? $packages : [];
		$weight = $data['info']['weight'] ?? null;
		$this->weight = is_numeric($weight) ? (float) $weight : 0.0;
		$dimensions = $data['info']['dimensions'] ?? null;
		$this->dimensions = is_array($dimensions) ? $dimensions : [];
		$this->pickupDate = (string) ($data['info']['pickupDate'] ?? '');
		$pickupHours = $data['info']['pickupHours'] ?? null;
		$this->pickupHours = is_array($pickupHours) ? $pickupHours : [];
		$this->observation = (string) ($data['info']['observation'] ?? '');
		$this->type = (string) ($data['info']['type'] ?? '');
		$awbs = $data['info']['awbs'] ?? null;
		$this->awbs = is_array($awbs) ? $awbs : [];
		$sender = $data['sender'] ?? null;
		$this->sender = is_array($sender) ? $sender : [];
		}

	public function getId(): string
		{
		return $this->id;
		}

	public function getNumber(): string
		{
		return $this->number;
		}

	/** @return array<string, mixed> */
	public function getStatus(): array
		{
		return $this->status;
		}

	public function getDate(): string
		{
		return $this->date;
		}

	public function getHour(): string
		{
		return $this->hour;
		}

	public function getEnvelopes(): int
		{
		return (int) ($this->packages['envelope'] ?? 0);
		}

	public function getParcels(): int
		{
		return (int) ($this->packages['parcel'] ?? 0);
		}

	public function getWeight(): float
		{
		return $this->weight;
		}

	/** @return array<string, mixed> */
	public function getDimensions(): array
		{
		return $this->dimensions;
		}

	public function getHeight(): float
		{
		return (float)($this->dimensions['height'] ?? 0.0);
		}

	public function getLength(): float
		{
		return (float)($this->dimensions['length'] ?? 0.0);
		}

	public function getWidth(): float
		{
		return (float)($this->dimensions['width'] ?? 0.0);
		}

	public function getPickupDate(): string
		{
		return $this->pickupDate;
		}

	/** @return array<string, mixed> */
	public function getPickupHours(): array
		{
		return $this->pickupHours;
		}

	public function getNotes(): string
		{
		return $this->observation;
		}

	public function getType(): string
		{
		return $this->type;
		}

	/** @return array<int, mixed> */
	public function getAwbs(): array
		{
		return $this->awbs;
		}


	/** @return array<string, mixed> */
	public function getSender(): array
		{
		return $this->sender;
		}

}

/*
            [0] => Array
                (
                    [info] => Array
                        (
                            [id] => 18601914
                            [number] => SI329300184
                            [status] => Array
                                (
                                    [id] => 0
                                    [name] => In asteptare
                                )

                            [date] => 2023-11-25
                            [hour] => 09:00
                            [packages] => Array
                                (
                                    [envelope] => 0
                                    [parcel] => 3
                                )

                            [weight] => 3
                            [dimensions] => Array
                                (
                                    [height] => 0.1
                                    [length] => 0.1
                                    [width] => 0.1
                                )

                            [pickupDate] => 2023-11-25
                            [pickupHours] => Array
                                (
                                    [firstHour] => 10:00
                                    [secondHour] => 13:00
                                )

                            [observation] => 
                            [type] => Standard
                            [awbs] => Array
                                (
                                )

                        )

                    [sender] => Array
                        (
                            [name] => FAN COURIER - cont test
                            [contactPerson] => Ionut Vasiliu
                            [email] => subscriptions@ivfuture.ro
                            [phone] => 0772269427
                            [address] => Array
                                (
                                    [localityId] => 11
                                    [locality] => Bucuresti
                                    [countyId] => 
                                    [county] => Bucuresti
                                    [agency] => Bucuresti
                                    [street] => Fabrica de Glucoza (sosea)
                                    [streetNo] => 11C
                                    [zipCode] => 
                                    [building] => 
                                    [entrance] => 
                                    [floor] => 
                                    [apartment] => 
                                    [country] => Romania
                                )

                        )

                )
*/
