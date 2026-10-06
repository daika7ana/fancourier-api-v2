<?php

declare(strict_types=1);

namespace Fancourier\Objects;

class Street
{
	protected string $id = '';
	protected string $streetName = '';
	protected string $type = '';
	protected string $county = '';
	protected string $city = '';
	// extra details
	/** @var array<string, array<string, mixed>> */
	protected array $details = [];

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		$this->id			= (string) intval($data['id'] ?? 0);
		$this->streetName	= (string) ($data['street'] ?? '');
		$this->type			= (string) ($data['type'] ?? '');
		$this->county		= (string) ($data['county'] ?? '');
		$this->city			= (string) ($data['locality'] ?? '');

		$this->details = [];
		$details = $data['details'] ?? null;
		if (is_array($details))
			{
			foreach ($details as $pos=>$detail)
				{
				if (!is_array($detail))
					{
					continue;
					}
				$this->details[ (string) ($detail['zipCode'] ?? '') ] = $detail;
				}
			}
		}
	
	public function getId(): string
		{
		return $this->id;
		}
	
	public function getName(): string
		{
		return $this->streetName;
		}
		
	public function getType(): string
		{
		return $this->type;
		}
	
	public function getCounty(): string
		{
		return $this->county;
		}
	
	public function getCity(): string
		{
		return $this->city;
		}
	
	public function hasZipCode(string $zipCode): bool
		{
		return isset($this->details[$zipCode]);
		}

	/**
	 * @return array<string, mixed>|false
	 */
	public function getDetails(string $zipCode): array|false
		{
		return $this->details[$zipCode] ?? false;
		}
	
	
	/* return array with data similar to fan courier api response (details keys are set to the zipcode instead of numeric) */
	/** @return array<string, mixed> */
	public function getArray(): array
		{
		$arr = [
			'id'		=> (int) $this->id,
			"street"	=> $this->streetName,
			"type"		=> $this->type,
			"locality"	=> $this->city,
			"county"	=> $this->county,
			"details"	=> $this->details
			];
		
		return $arr;
		}
	
}
