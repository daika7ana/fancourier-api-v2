<?php

namespace Fancourier\Response;

use Fancourier\Objects\Branch;

class GetBranches extends Generic implements ResponseInterface
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
				parent::setData($response_json);
								
				foreach ($response_json['data'] as $rd)
					{
					$this->result[ $rd['id'] ] = new Branch($rd);
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
	
	public function get($id): ?Branch
		{
		return $this->result[$id] ?? null;
		}
}
