<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\GetBankTransfers as GetBankTransfersResponse;

class GetBankTransfers extends AbstractRequest implements RequestInterface
{
    use PaginationTrait;

    protected string $gateway = 'reports/bank-transfers';
	protected string $method = 'GET';

    private string $date = '';

    public function __construct()
    {
        parent::__construct();
        $this->response = new GetBankTransfersResponse();
		
		$this->date = date("Y-m-d");
        $this->perPage = 100;
    }

    /** @return array<string, mixed> */
    #[\Override]
    public function pack(): array
    {
        $arr = [
				'clientId' => $this->auth->getClientId(),
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
     * @param string $usedate
     * @return static
     */
    public function setDate(string $usedate): static
    {
        $this->date = $usedate;
        return $this;
    }

}
