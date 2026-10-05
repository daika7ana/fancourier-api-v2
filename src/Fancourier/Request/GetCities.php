<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCities as GetCitiesResponse;

class GetCities extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/localities';
	protected string $method = 'GET';
	
    protected string $county = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCitiesResponse();
    }

    /** @return array<string, string> */
    #[\Override]
    public function pack(): array
    {
		$arr = [];
		if ($this->county != '')
			{
			$arr['county'] = $this->county;
			}
		
		return $arr;
    }

    /**
     * @return string
     */
    public function getCounty(): string
    {
        return $this->county;
    }

    /**
     * @param string $county
     * @return static
     */
    public function setCounty(string $county): static
    {
        $this->county = $county;
        return $this;
    }

}
