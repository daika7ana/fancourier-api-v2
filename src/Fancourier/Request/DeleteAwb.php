<?php

namespace Fancourier\Request;

use Fancourier\Response\DeleteAwb as DeleteAwbResponse;

class DeleteAwb extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'awb';
	protected string $method = 'DELETE';

    private ?string $awb = null;

    public function __construct()
    {
        parent::__construct();
        $this->response = new DeleteAwbResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
			'clientId'	=> $this->auth->getClientId(),
			'awb'		=> $this->awb
			];
		
		return $arr;
    }

    /**
     * @return string|null
     */
    public function getAwb(): ?string
    {
        return $this->awb;
    }

    /**
     * @param string $awb
     * @return static
     */
    public function setAwb(string $awb): static
    {
        $this->awb = $awb;
        return $this;
    }
}
