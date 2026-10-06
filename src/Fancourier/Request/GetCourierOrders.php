<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetCourierOrders as GetCourierOrdersResponse;

class GetCourierOrders extends AbstractRequest implements RequestInterface
{
    use PaginationTrait;

    protected string $gateway = 'reports/orders';
    protected string $method = 'GET';

    private string $date = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCourierOrdersResponse();

        $this->date = date("d-m-Y");
        $this->perPage = 10;
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
            'clientId' => $this->auth()->getClientId(),
            'date' => $this->date,
        ];

        return $this->withPagination($arr);
    }


    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date Date as string in the dd-mm-YYYY format
     * 					  Accepts YYYY-mm-dd format as well and will be converted internally to the dd-mm-YYYY format
     * @return static
     */
    public function setDate(string $date): static
    {
        $parts = explode("-", $date);
        if (strlen($parts[0]) == 4) {
            // we have Y-m-d but CourierOrder expects date as d-m-Y  -_-
            $date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }
        $this->date = $date;

        return $this;
    }


}
