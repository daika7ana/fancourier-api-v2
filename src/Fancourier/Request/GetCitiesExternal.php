<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCitiesExternal as GetCitiesExternalResponse;

class GetCitiesExternal extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/external-localities';
	protected string $method = 'GET';

    private string $country = '';
    private string $county = '';
    private int $page = 0;
    private int $perPage = 100;

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCitiesExternalResponse();
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [];
		if ($this->country != '')
			{
			$arr['country'] = $this->country;
			}
		
		if ($this->county != '')
			{
			$arr['county'] = $this->county;
			}
		
		if ($this->page > 0)
			{
			$arr['page'] = $this->page;
			}
		
		if ($this->perPage > 0)
			{
			$arr['perPage'] = $this->perPage;
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

    /**
     * @return string
     */
    public function getCounty(): string
    {
        return $this->county;
    }

    /**
     * @param string $county
     * @return $this
     */
    public function setCounty(string $county): static
    {
        $this->county = $county;
        return $this;
    }


    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page): static
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     * @return $this
     */
    public function setPerPage(int $perPage): static
    {
		if ($perPage > 100)
			{	// FAN Courier API limits this to maximum 100 even if the documentation specifies 1000
			$perPage = 100;
			}
        $this->perPage = $perPage;
        return $this;
    }


}
