<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class Branch
{
	protected string $id = '';
	protected string $name = '';
	protected string $bank = '';
	protected string $bankAccount = '';
	protected string $email = '';
	protected string $phone = '';
	protected string $altPhone = '';
	protected string $contactPerson = '';
	protected string $addr_county = '';
	protected string $addr_city = '';
	protected string $addr_countyId = '';
	protected string $addr_cityId = '';
	protected string $addr_street = '';
	protected string $addr_streetNo = '';
	protected string $addr_zipcode = '';
	protected string $addr_building = '';
	protected string $addr_entrance = '';
	protected string $addr_floor = '';
	protected string $addr_apartment = '';

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		// Ids can be ints and optional keys may be absent; the address block may
		// be missing entirely. Default + cast so every typed property initializes.
		$this->id = (string) ($data['id'] ?? '');
		$this->name = (string) ($data['name'] ?? '');
		$this->bank = (string) ($data['bank'] ?? '');
		$this->bankAccount = (string) ($data['bankAccount'] ?? '');
		$this->email = (string) ($data['email'] ?? '');
		$this->phone = (string) ($data['phone'] ?? '');
		$this->altPhone = (string) ($data['secondaryPhone'] ?? '');
		$this->contactPerson = (string) ($data['contactPerson'] ?? '');
		$this->addr_county = (string) ($data['address']['county'] ?? '');
		$this->addr_city = (string) ($data['address']['locality'] ?? '');
		$this->addr_countyId = (string) ($data['address']['countyId'] ?? '');
		$this->addr_cityId = (string) ($data['address']['localityId'] ?? '');
		$this->addr_street = (string) ($data['address']['street'] ?? '');
		$this->addr_streetNo = (string) ($data['address']['streetNo'] ?? '');
		// /reports/branches returns the key as lowercase "zipcode"; older payloads use "zipCode".
		$this->addr_zipcode = (string) ($data['address']['zipCode'] ?? $data['address']['zipcode'] ?? '');
		$this->addr_building = (string) ($data['address']['building'] ?? '');
		$this->addr_entrance = (string) ($data['address']['entrance'] ?? '');
		$this->addr_floor = (string) ($data['address']['floor'] ?? '');
		$this->addr_apartment = (string) ($data['address']['apartment'] ?? '');
		}
	
	public function getId(): string
		{
		return $this->id;
		}
	
	public function getName(): string
		{
		return $this->name;
		}
	
	public function getBank(): string
		{
		return $this->bank;
		}
	
	public function getBankAccount(): string
		{
		return $this->bankAccount;
		}
	
	public function getEmail(): string
		{
		return $this->email;
		}
	
	public function getPhone(): string
		{
		return $this->phone;
		}
	
	public function getSecondaryPhone(): string
		{
		return $this->altPhone;
		}
	
	public function getContactPerson(): string
		{
		return $this->contactPerson;
		}
	
	public function getCounty(): string
		{
		return $this->addr_county;
		}
	
	public function getCity(): string
		{
		return $this->addr_city;
		}
	
	public function getCountyId(): string
		{
		return $this->addr_countyId;
		}
	
	public function getCityId(): string
		{
		return $this->addr_cityId;
		}
	
	public function getStreet(): string
		{
		return $this->addr_street;
		}
	
	public function getStreetNo(): string
		{
		return $this->addr_streetNo;
		}
	
	public function getPostalCode(): string
		{
		return $this->addr_zipcode;
		}
	
	public function getBuilding(): string
		{
		return $this->addr_building;
		}
	
	public function getEntrance(): string
		{
		return $this->addr_entrance;
		}
	
	public function getFloor(): string
		{
		return $this->addr_floor;
		}
	
	public function getApartment(): string
		{
		return $this->addr_apartment;
		}
	
}
