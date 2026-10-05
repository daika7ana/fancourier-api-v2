<?php

namespace Fancourier\Request;

use Fancourier\Response\GetStreets as GetStreetsResponse;

class GetStreets extends AbstractRequest implements RequestInterface
{
    use PaginationTrait;

    protected string $gateway = 'reports/streets';
	protected string $method = 'GET';

    private string $county = '';
    private string $city = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetStreetsResponse();
        $this->perPage = 1000;
    }

    // Overrides PaginationTrait::maxPerPage(); #[\Override] cannot see trait methods.
    protected function maxPerPage(): int
    {
        return 1000;
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
		
		return $this->withPagination($arr);
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


}
