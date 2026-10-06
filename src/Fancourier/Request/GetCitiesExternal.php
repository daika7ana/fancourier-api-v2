<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetCitiesExternal as GetCitiesExternalResponse;

class GetCitiesExternal extends AbstractRequest implements RequestInterface
{
    use PaginationTrait;

    protected string $gateway = 'reports/external-localities';
    protected string $method = 'GET';

    private string $country = '';
    private string $county = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCitiesExternalResponse();
        $this->perPage = 100;
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [];
        if ($this->country != '') {
            $arr['country'] = $this->country;
        }

        if ($this->county != '') {
            $arr['county'] = $this->county;
        }

        return $this->withPagination($arr);
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


}
