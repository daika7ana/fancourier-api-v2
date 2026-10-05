<?php

namespace Fancourier\Request;

use Fancourier\Response\CreateAwb as CreateAwbResponse;
use Fancourier\Response\Generic;

use Fancourier\Objects\AwbIntern;

/**
 * Class CreateAwb
 * @package Fancourier\Request
 */
class CreateAwb extends AbstractRequest implements RequestInterface
{
	protected string $gateway = 'intern-awb';
	protected string $method = 'POST';

	protected int|string|null $platformId = null;

	/** @var array<AwbIntern> */
	protected array $awbList = [];

    /** @var CreateAwbResponse */
    protected Generic $response;

    public function __construct()
    {
        parent::__construct();
        $this->response = new CreateAwbResponse();
    }


    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
		$this->response->setAwbList($this->awbList);


		$arr = [
				"clientId" => $this->auth->getClientId(), //obligatoriu
				"shipments" => [] // shipments
			];

		foreach ($this->awbList as $awb)
			{
			$arr['shipments'][] = $awb->pack();
			}

		if (isset($this->platformId))
			{
			$arr['platformId'] = $this->platformId;
			}

		return $arr;

    }


	/**
	* Add a new AWB object to the request
	*/
	public function addAwb(AwbIntern $awb): static
	{
		$this->awbList[] = $awb;
		return $this;
	}


	/**
	* Clear the list of AWB's assigned to this request
	*/
	public function resetAwbs(): static
	{
		$this->awbList = [];
		return $this;
	}


	/**
	* Use this only if you have a platformId number from Fan Courier
	*/
	public function setPlatformId(int|string $platformId): static
	{
		$this->platformId = $platformId;
		return $this;
	}
}
