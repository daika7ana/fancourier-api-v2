<?php

namespace Fancourier\Response;

use Fancourier\Objects\CourierOrderTracker;

class TrackCourierOrder extends Generic implements ResponseInterface
{
	protected $result;
	
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
					$this->result[ $rd['orderId'] ] = new CourierOrderTracker($rd);
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
	
	public function getAll(): array
		{
		return $this->result ?? [];
		}
	
		
	public function getOrder($orderId) //: CourierOrderTracker|false
		{
		return $this->result[ $orderId ] ?? false;
		}
}
