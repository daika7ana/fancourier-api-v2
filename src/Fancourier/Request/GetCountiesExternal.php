<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCountiesExternal as GetCountiesExternalResponse;

class GetCountiesExternal extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/external-counties';
	protected string $method = 'GET';
	
    protected string $country = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCountiesExternalResponse();
    }

    /** @return array<string, string> */
    #[\Override]
    public function pack(): array
    {
		$arr = [];
		if ($this->country != '')
			{
			$arr['country'] = $this->country;
			}
		
		return $arr;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return static
     */
    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

}
