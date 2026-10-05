<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCountries as GetCountriesResponse;

class GetCountries extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/countries';
	protected string $method = 'GET';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCountriesResponse();
    }

    #[\Override]
    public function pack(): array
    {
		return [];
    }

}
