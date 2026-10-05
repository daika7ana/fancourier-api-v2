<?php

namespace Fancourier\Objects;

class BankTransfer
{
	protected string $awbNumber = '';
	protected string $awbDate = '';
	protected string $returnAwbNumber = '';
	protected string $reimbursementAwbNumber = '';
	protected float $amountCollected = 0.0;
	protected string $content = '';
	protected string $transferDate = '';
	protected string $transactionType = '';
	protected string $transactionDate = '';

	protected string $recipientName = '';
	protected string $recipientContactPerson = '';
	protected string $recipientCity = '';

	protected string $senderName = '';
	protected string $senderContactPerson = '';

	/**
	 * @param array<string, mixed> $data
	 */
	public function __construct(array $data)
		{
		$this->awbNumber				= (string) ($data['info']['awbNumber'] ?? '');
 		$this->awbDate					= (string) ($data['info']['awbDate'] ?? '');
 		$this->returnAwbNumber			= (string) ($data['info']['returnAwbNumber'] ?? '');
 		$this->reimbursementAwbNumber	= (string) ($data['info']['reimbursementAwbNumber'] ?? '');
 		// amountCollected arrives as a numeric string or a float.
 		$amountCollected = $data['info']['amountCollected'] ?? null;
 		$this->amountCollected			= is_numeric($amountCollected) ? (float) $amountCollected : 0.0;
 		$this->content					= (string) ($data['info']['content'] ?? '');
 		$this->transferDate				= (string) ($data['info']['transferDate'] ?? '');
 		$this->transactionType			= (string) ($data['info']['transactionType'] ?? '');
 		$this->transactionDate			= (string) ($data['info']['transactionDate'] ?? '');

		$this->recipientName			= (string) ($data['recipient']['name'] ?? '');
		$this->recipientContactPerson	= (string) ($data['recipient']['contactPerson'] ?? '');
		$this->recipientCity			= (string) ($data['recipient']['address']['locality'] ?? '');

		$this->senderName				= (string) ($data['sender']['name'] ?? '');
		$this->senderContactPerson		= (string) ($data['sender']['contactPerson'] ?? '');
		}

	public function getAwbNumber(): string
		{
		return $this->awbNumber;
		}

	public function getAwbDate(): string
		{
		return $this->awbDate;
		}

	public function getReturnAwbNumber(): string
		{
		return $this->returnAwbNumber;
		}

	public function getReimbursementAwbNumber(): string
		{
		return $this->reimbursementAwbNumber;
		}

	public function getAmountCollected(): float
		{
		return $this->amountCollected;
		}

	public function getContent(): string
		{
		return $this->content;
		}

	public function getTransferDate(): string
		{
		return $this->transferDate;
		}

	public function getTransactionType(): string
		{
		return $this->transactionType;
		}

	public function getTransactionDate(): string
		{
		return $this->transactionDate;
		}

	public function getRecipientName(): string
		{
		return $this->recipientName;
		}

	public function getRecipientContactPerson(): string
		{
		return $this->recipientContactPerson;
		}

	public function getRecipientCity(): string
		{
		return $this->recipientCity;
		}

	public function getSenderName(): string
		{
		return $this->senderName;
		}

	public function getSenderContactPerson(): string
		{
		return $this->senderContactPerson;
		}

}


/*
    [0] => Array
        (
            [info] => Array
                (
                    [awbNumber] => 6000000000001
                    [awbDate] => 16.11.2023
                    [amountCollected] => 670.33
                    [content] => order #6783
                    [transferDate] => 20.11.2023
                    [returnAwbNumber] => 
                    [reimbursementAwbNumber] => 
                    [transactionType] => cash
                    [transactionDate] => 17.11.2023
                )

            [recipient] => Array
                (
                    [name] => M. INTREPRINDERE FAMILIALA
                    [contactPerson] => M. - Adjud
                    [address] => Array
                        (
                            [locality] => Adjud
                        )

                )

            [sender] => Array
                (
                    [name] => NETWORK SRL
                    [contactPerson] => 
                )

        )

    [1] => Array
        (
            [info] => Array
                (
                    [awbNumber] => 6000000000002
                    [awbDate] => 16.11.2023
                    [amountCollected] => 375.03
                    [content] => order #6785
                    [transferDate] => 20.11.2023
                    [returnAwbNumber] => 
                    [reimbursementAwbNumber] => 
                    [transactionType] => cash
                    [transactionDate] => 17.11.2023
                )

            [recipient] => Array
                (
                    [name] => COM S.R.L.
                    [contactPerson] => COM S.R.L.
                    [address] => Array
                        (
                            [locality] => Arad
                        )

                )

            [sender] => Array
                (
                    [name] => NETWORK SRL
                    [contactPerson] => 
                )

        )
*/