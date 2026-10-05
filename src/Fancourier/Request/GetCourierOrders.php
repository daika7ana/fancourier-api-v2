<?php

namespace Fancourier\Request;

use Fancourier\Response\GetCourierOrders as GetCourierOrdersResponse;

class GetCourierOrders extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/orders';
	protected string $method = 'GET';

    private string $date = '';
    private int $page = 0;
    private int $perPage = 10;

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetCourierOrdersResponse();
		
		$this->date = date("d-m-Y");
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
				'clientId' => $this->auth->getClientId(),
				'date' => $this->date,
				];
		
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
		if (strlen($parts[0]) == 4)
			{
			// we have Y-m-d but CourierOrder expects date as d-m-Y  -_-
			$date = $parts[2].'-'.$parts[1].'-'.$parts[0];
			}
        $this->date = $date;
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
