<?php

namespace Fancourier\Objects;

class City
{
	protected string $id;
	protected string $name;
	protected string $county;
	protected string $agency;
	protected float $extKm;

	public function __construct(int|string $id, string $name, string $county, string $agency, int|float|string|null $extKm)
		{
		$this->id = (string) $id;
		$this->name = $name;
		$this->county = $county;
		$this->agency = $agency;
		// exteriorKm may be null or a numeric string in live payloads.
		$this->extKm = is_numeric($extKm) ? (float) $extKm : 0.0;
		}
	
	public function getId(): string
		{
		return $this->id;
		}
	
	public function getName(): string
		{
		return $this->name;
		}
	
	public function getCounty(): string
		{
		return $this->county;
		}
	
	public function getAgency(): string
		{
		return $this->agency;
		}
	
	public function getExtKm(): float
		{
		return $this->extKm;
		}
}
