<?php

namespace Fancourier\Request;

use Fancourier\Response\GetBankTransfers as GetBankTransfersResponse;

class GetBankTransfers extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'reports/bank-transfers';
	protected string $method = 'GET';

    private string $date = '';
    private int $page = 0;
    private int $perPage = 100;

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetBankTransfersResponse();
		
		$this->date = date("Y-m-d");
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
     * @param string $usedate
     * @return static
     */
    public function setDate(string $usedate): static
    {
        $this->date = $usedate;
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
     * @return static
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
     * @return static
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
