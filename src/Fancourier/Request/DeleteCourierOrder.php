<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\DeleteCourierOrder as DeleteCourierOrderResponse;

class DeleteCourierOrder extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'order';
	protected string $method = 'DELETE';

    private ?string $orderId = null;

    public function __construct()
    {
        parent::__construct();
        $this->response = new DeleteCourierOrderResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
			'clientId'	=> $this->auth->getClientId(),
			'id'		=> $this->orderId
			];
		
		return $arr;
    }

    /**
     * @return string|null
     */
    public function getOrder(): ?string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return static
     */
    public function setOrder(string $orderId): static
    {
        $this->orderId = $orderId;
        return $this;
    }
}
