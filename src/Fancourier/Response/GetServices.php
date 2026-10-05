<?php

namespace Fancourier\Response;

use Fancourier\Objects\Service;

class GetServices extends Generic implements ResponseInterface
{
	protected $result;
	
    #[\Override]
    public function setData($datastr)
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
					$this->result[ $rd['name'] ] = new Service($rd['id'], $rd['name'], $rd['description']);
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
	
	
	public function hasService($name): bool
		{
		return isset($this->result[ $name ]);
		}
		
	public function getService($name) //: Service|false
		{
		return $this->result[ $name ] ?? false;
		}
}
