<?php

namespace Fancourier\Request;

use Fancourier\Response\TrackCourierOrder as TrackCourierOrderResponse;

/**
 * Class TrackCourierOrder
 * @package Fancourier\Request
 * @SuppressWarnings(PHPMD)
 */
class TrackCourierOrder extends AbstractRequest implements RequestInterface
{
	use LanguageTrait;

	protected string $gateway = 'reports/orders/tracking';
	protected string $method = 'GET';
	
	/** @var array<string> */
	protected array $orderList = [];

    public function __construct()
    {
        parent::__construct();
        $this->response = new TrackCourierOrderResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
		$arr = [
				"clientId" => $this->auth->getClientId(), //obligatoriu 
				"orderId" => [] // shipments
				
			];
		
		foreach ($this->orderList as $order)
			{
			$arr['orderId'][] = $order;
			}
		
		if ($this->language != '')
			{
			$arr['language'] = $this->language;
			}
		
		return $arr;
	
    }
	
	public function addOrder(string $order): static
	{
		$this->orderList[] = $order;
		return $this;
	}
	
	public function setOrder(string $order): static
	{
		return $this->addOrder($order);
	}
	
	public function resetOrders(): static
	{
		$this->orderList = [];
		return $this;
	}


}
