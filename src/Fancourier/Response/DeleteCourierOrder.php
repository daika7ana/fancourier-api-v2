<?php

namespace Fancourier\Response;

class DeleteCourierOrder extends Generic implements ResponseInterface
{
    #[\Override]
    public function setData(mixed $datastr): static
    {
		$response_json = json_decode($datastr, true);
		
		parent::setData(false);
		
		if (json_last_error() === JSON_ERROR_NONE)
			{
			if (isset($response_json['status']) && ($response_json['status'] == 'success'))
				{
				parent::setData(true);
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
}
