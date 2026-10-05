<?php

namespace Fancourier\Request;

use Fancourier\Response\GetAwbConfirmations as GetAwbConfirmationsResponse;

/**
 * Class GetAwbConfirmations
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class GetAwbConfirmations extends AbstractRequest implements RequestInterface
{
	protected string $gateway = 'reports/get-awb-confirmations';
	protected string $method = 'GET';
	
	/** @var array<string> */
	protected array $awbList = [];

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetAwbConfirmationsResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
		$arr = [
				"clientId" => $this->auth->getClientId(), //obligatoriu 
				"awb" => [] // shipments
				
			];
		
		foreach ($this->awbList as $awb)
			{
			$arr['awb'][] = $awb;
			}
		
		return $arr;
	
    }
	
	public function addAwb(string $awb): static
	{
		$this->awbList[] = $awb;
		return $this;
	}
	
	public function setAwb(string $awb): static
	{
		return $this->addAwb($awb);
	}
	
	
	public function resetAwbs(): static
	{
		$this->awbList = [];
		return $this;
	}

}
