<?php

namespace Fancourier\Request;

use Fancourier\Response\GetStreets as GetStreetsResponse;

class GetStreets extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/streets';
	protected string $method = 'GET';

    private string $county = '';
    private string $city = '';
    private int $page = 0;
    private int $perPage = 1000;

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetStreetsResponse();
    }

    /** @return array<string, mixed> */
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
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
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
		if ($perPage > 1000)
			{
			$perPage = 1000;
			}
        $this->perPage = $perPage;
        return $this;
    }


}
