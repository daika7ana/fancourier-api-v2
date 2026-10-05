<?php

namespace Fancourier\Response;


class CreateCourierOrder extends Generic implements ResponseInterface
{
	protected $result;
	
    #[\Override]
    public function setData($datastr)
    {
		try {
			$response_json = json_decode($datastr, true);
			}
		catch (\TypeError $e)
			{ }
		
		if (json_last_error() === JSON_ERROR_NONE)
			{
			$this->result = [];
			
			if (isset($response_json['status']) && ($response_json['status'] == 'success'))
				{
				parent::setData($response_json['data']['id'] ?? null);
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
	
	public function getId(): string|int|null
		{
		$id = $this->getData();

		return is_string($id) || is_int($id) ? $id : null;
		}

}
