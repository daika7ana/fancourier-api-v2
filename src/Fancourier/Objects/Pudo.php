<?php

namespace Fancourier\Objects;

class Pudo
{
	protected string $id = '';
	protected string $name = '';
	protected string $routingLocation = '';
	protected string $description = '';
	// latitude/longitude arrive as JSON floats but the getters are typed string.
	protected string $latitude = '';
	protected string $longitude = '';

	/** @var array<string, mixed> */
	protected array $address = [];

	/** @var array<string, mixed> */
	protected array $schedule = [];
	/** @var array<string, mixed> */
	protected array $drawer = [];

	/** @var array<int, mixed> */
	protected array $phones = [];
	protected string $email = '';
	protected bool $highDemand = false;
	/** @var array<int, mixed> */
	protected array $paymentMethods = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		$this->id				= (string) ($data['id'] ?? '');
		$this->name				= (string) ($data['name'] ?? '');
		$this->routingLocation	= (string) ($data['routingLocation'] ?? '');
		$this->description		= (string) ($data['description'] ?? '');

		$address = $data['address'] ?? null;
		$this->address			= is_array($address) ? $address : [];

		$this->latitude			= (string) ($data['latitude'] ?? '');
		$this->longitude		= (string) ($data['longitude'] ?? '');

		$schedule = $data['schedule'] ?? null;
		$this->schedule			= is_array($schedule) ? $schedule : [];
		$drawer = $data['drawer'] ?? null;
		$this->drawer			= is_array($drawer) ? $drawer : [];

		$phones = $data['phones'] ?? null;
		$this->phones			= is_array($phones) ? $phones : [];

		$this->email			= (string) ($data['email'] ?? '');
		$this->highDemand		= (bool) ($data['highDemand'] ?? false);
		$paymentMethods = $data['paymentMethods'] ?? null;
		$this->paymentMethods	= is_array($paymentMethods) ? $paymentMethods : [];
		}

	public function getId(): string
		{
		return $this->id;
		}

	public function getName(): string
		{
		return $this->name;
		}

	public function getRoutingLocation(): string
		{
		return $this->routingLocation;
		}

	public function getDescription(): string
		{
		return $this->description;
		}

	public function getLatitude(): string
		{
		return $this->latitude;
		}

	public function getLongitude(): string
		{
		return $this->longitude;
		}

	/** @return array<string, mixed> */
	public function getAddress(): array
		{
		return $this->address;
		}

	/** @return array<string, mixed> */
	public function getSchedule(): array
		{
		return $this->schedule;
		}

	/** @return array<string, mixed> */
	public function getDrawer(): array
		{
		return $this->drawer;
		}

	/** @return array<int, mixed> */
	public function getPhones(): array
		{
		return $this->phones;
		}

	public function getEmail(): string
		{
		return $this->email;
		}

	public function getHighDemand(): bool
		{
		return $this->highDemand;
		}

	/** @return array<int, mixed> */
	public function getPaymentMethods(): array
		{
		return $this->paymentMethods;
		}


	/* return array with data similar to fan courier api response */
	/** @return array<string, mixed> */
	public function getArray(): array
		{
		$arr = [
			"id"				=> $this->id,
			"name"				=> $this->name,
			"routingLocation"	=> $this->routingLocation,
			"description"		=> $this->description,
			"latitude"			=> $this->latitude,
			"longitude"			=> $this->longitude,
			"address"			=> $this->address,
			"schedule"			=> $this->schedule,
			"drawer"			=> $this->drawer,
			"phones"			=> $this->phones,
			"email"				=> $this->email,
			"highDemand"		=> $this->highDemand,
			"paymentMethods"	=> $this->paymentMethods,
			];

		return $arr;
		}

}
