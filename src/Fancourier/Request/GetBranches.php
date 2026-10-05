<?php

namespace Fancourier\Request;

use Fancourier\Response\GetBranches as GetBranchesResponse;

class GetBranches extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/branches';
	protected string $method = 'GET';

    private string $county = '';
    private string $city = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetBranchesResponse();
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
		
		if ($this->city != '')
			{
			$arr['locality'] = $this->city;
			}		

		return $arr;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return static
     */
    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
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
