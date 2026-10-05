<?php

namespace Fancourier\Response;

use Fancourier\Objects\City;

class GetCities extends Generic implements ResponseInterface
{
	/** @var array<int|string, City>|null */
	protected ?array $result = null;
	
    #[\Override]
    public function setData(mixed $datastr): static
    {
		$response_json = json_decode($datastr, true);
		
		if (json_last_error() === JSON_ERROR_NONE)
			{
			$this->result = [];
			
			if (isset($response_json['status']) && ($response_json['status'] == 'success'))
				{
				parent::setData($response_json['data']);
				
				foreach ($response_json['data'] as $rd)
					{
					$this->result[ $rd['id'] ] = new City($rd['id'], $rd['name'], $rd['county'], $rd['agency'], $rd['exteriorKm']);
					}
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
	 * @return array<int|string, City>
	 */
	public function getAll(): array
		{
		return $this->result ?? [];
		}
	
	/**
	 * @param string $cityname
	 */
	public function getCity(string $cityname): City|false
		{
		$return = false;
		foreach ($this->result as $cid=>$cv)
			{
			if ( strtolower($cv->getName()) == strtolower(trim($cityname)) )
				{
				$return = $this->result[ $cid ];
				break;
				}
			}
		
		return $return;
		}
}
