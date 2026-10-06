<?php

declare(strict_types=1);

namespace Fancourier\Response;

use Fancourier\Objects\CourierOrderEvent;

class GetCourierOrderEvents extends Generic implements ResponseInterface
{
	/** @var array<int|string, CourierOrderEvent>|null */
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
					$this->result[ $rd['id'] ] = new CourierOrderEvent($rd['id'], $rd['name']);
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
	 * @return array<int|string, CourierOrderEvent>
	 */
	public function getAll(): array
		{
		return $this->result ?? [];
		}
	
		
	/**
	 * @param int|string $courierEventId
	 */
	public function getEvent(int|string $courierEventId): CourierOrderEvent|false
		{
		return $this->result[ $courierEventId ] ?? false;
		}
}
