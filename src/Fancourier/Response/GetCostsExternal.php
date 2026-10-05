<?php

namespace Fancourier\Response;

class GetCostsExternal extends Generic implements ResponseInterface
{
	/** @var array<string, mixed>|null Decoded `data`; money keys normalized below, `errors` kept as-is. */
	protected ?array $result = null;
	
    #[\Override]
    public function setData(mixed $datastr): static
    {
		$response_json = json_decode($datastr, true);
		
		if (json_last_error() === JSON_ERROR_NONE)
			{
			$data = $response_json['data'] ?? null;
			$this->result = is_array($data) ? $data : [];
			
			// The API returns money as numeric strings; normalize at the boundary.
			foreach (['extraKmCost', 'weightCost', 'insuranceCost', 'optionsCost', 'fuelCost', 'costNoVAT', 'vat', 'total'] as $field)
				{
				$this->result[$field] = is_numeric($this->result[$field] ?? null) ? (float) $this->result[$field] : 0.0;
				}
			
			if (isset($response_json['status']) && ($response_json['status'] == 'success'))
				{
				parent::setData($response_json['data']);
				}
			else
				{
				$this->setErrorFromBody($response_json);
				}
			}
		else
			{
			$this->setErrorFromBody($datastr);
			}


        return $this;
    }
	
	/**
	 * @return array<int|string, mixed>
	 */
	public function getAllErrors(): array
		{
		return $this->result['errors'] ?? [];
		}
	
	public function getKmCost(): float
		{
		return $this->result['extraKmCost'] ?? 0;
		}
		
	public function getWeightCost(): float
		{
		return $this->result['weightCost'] ?? 0;
		}
		
	public function getInsuranceCost(): float
		{
		return $this->result['insuranceCost'] ?? 0;
		}
		
	public function getOptionsCost(): float
		{
		return $this->result['optionsCost'] ?? 0;
		}
		
	public function getFuelCost(): float
		{
		return $this->result['fuelCost'] ?? 0;
		}
		
	public function getCost(): float
		{
		return $this->result['costNoVAT'] ?? 0;
		}
		
	public function getCostVat(): float
		{
		return $this->result['vat'] ?? 0;
		}
		
	public function getCostTotal(): float
		{
		return $this->result['total'] ?? 0;
		}
}
