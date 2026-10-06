<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Response\CreateAwbBankAccount as CreateAwbBankAccountResponse;

class CreateAwbBankAccount extends AbstractRequest implements RequestInterface
{
    protected string $gateway = 'awb-bank-account';
    protected string $method = 'POST';

    /** @var list<array{awb: string, iban: string}> */
    protected array $records = [];

    public function __construct()
    {
        parent::__construct();
        $this->response = new CreateAwbBankAccountResponse();
    }

    /** @return list<array{awb: string, iban: string}> */
    #[\Override]
    public function pack(): array
    {
        return $this->records;
    }

    public function addRecord(string $awb, string $iban): static
    {
        $this->records[] = ['awb' => $awb, 'iban' => $iban];

        return $this;
    }

    /** @return list<array{awb: string, iban: string}> */
    public function getRecords(): array
    {
        return $this->records;
    }
}
