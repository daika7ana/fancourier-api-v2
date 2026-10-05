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

	protected $platformId;

	protected $awbList = [];

    /** @var CreateAwbResponse */
    protected Generic $response;

    public function __construct()
    {
        parent::__construct();
        $this->response = new CreateAwbResponse();
    }


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
	public function addAwb(AwbIntern $awb)
	{
		$this->awbList[] = $awb;
		return $this;
	}


	/**
	* Clear the list of AWB's assigned to this request
	*/
	public function resetAwbs()
	{
		$this->awbList = [];
		return $this;
	}


	/**
	* Use this only if you have a platformId number from Fan Courier
	*/
	public function setPlatformId($platformId)
	{
		$this->platformId = $platformId;
		return $this;
	}
}
