<?php

namespace Fancourier\Response;

use \Fancourier\Objects\AwbExtern;

class CreateAwbExternal extends Generic implements ResponseInterface
{
	/** @var array<int, array<string, mixed>>|null */
	protected ?array $result = null;
	/** @var array<int, AwbExtern> */
	protected array $awbList = [];
	
    #[\Override]
    public function setData(mixed $datastr): static
    {
		$response_json = json_decode($datastr, true);
		
		if (json_last_error() === JSON_ERROR_NONE && is_array($response_json))
			{
			$this->result = [];
			
			// ponytail: this endpoint normally returns a bare list, so a
			// top-level status object is treated as an error. Revisit if the
			// API starts tagging a successful list with status:"success".
			if (isset($response_json['status']) && ($response_json['status'] !== 'success'))
				{
				$this->setErrorFromBody($response_json);
				}
			elseif (count($response_json) > 0)
				{
				parent::setData($response_json);
/*
Array
(
    [0] => Array
        (
            [awbNumber] => 2338300120330
            [errors] => 
        )

)
*/
				foreach ($response_json as $result)
					{
					$this->result[] = $result;
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
	 * @param array<int, AwbExtern> $awbList
	 */
	public function setAwbList(array $awbList): bool
		{
		// check list
		foreach ($awbList as $awb)
			{
			if (!($awb instanceof AwbExtern))
				{
				return false;
				}
			}
		
		$this->awbList = $awbList;
		return true;
		}
	
	/**
	 * @return array<int, AwbExtern>
	 */
	public function getAll(): array
		{
		//print_r($this->result);
		if (empty($this->result))
			{
			return [];
			}
		
		foreach ($this->result as $idx=>$result)
			{
			$this->awbList[ $idx ]->setResult($result);
			}
		
		return $this->awbList;
		}
	
}
