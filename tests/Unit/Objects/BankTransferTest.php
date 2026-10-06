<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\BankTransfer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BankTransferTest extends TestCase
{
    private function data(): array
    {
        return [
            'info' => [
                'awbNumber' => '6000000000001',
                'awbDate' => '16.11.2023',
                'returnAwbNumber' => '',
                'reimbursementAwbNumber' => '',
                'amountCollected' => 670.33,
                'content' => 'order #6783',
                'transferDate' => '20.11.2023',
                'transactionType' => 'cash',
                'transactionDate' => '17.11.2023',
            ],
            'recipient' => [
                'name' => 'M. INTREPRINDERE FAMILIALA',
                'contactPerson' => 'M. - Adjud',
                'address' => ['locality' => 'Adjud'],
            ],
            'sender' => [
                'name' => 'NETWORK SRL',
                'contactPerson' => '',
            ],
        ];
    }

    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $transfer = new BankTransfer($this->data());

        $this->assertSame('6000000000001', $transfer->getAwbNumber());
        $this->assertSame('16.11.2023', $transfer->getAwbDate());
        $this->assertSame('', $transfer->getReturnAwbNumber());
        $this->assertSame('', $transfer->getReimbursementAwbNumber());
        $this->assertSame(670.33, $transfer->getAmountCollected());
        $this->assertSame('order #6783', $transfer->getContent());
        $this->assertSame('20.11.2023', $transfer->getTransferDate());
        $this->assertSame('cash', $transfer->getTransactionType());
        $this->assertSame('17.11.2023', $transfer->getTransactionDate());
        $this->assertSame('M. INTREPRINDERE FAMILIALA', $transfer->getRecipientName());
        $this->assertSame('M. - Adjud', $transfer->getRecipientContactPerson());
        $this->assertSame('Adjud', $transfer->getRecipientCity());
        $this->assertSame('NETWORK SRL', $transfer->getSenderName());
        $this->assertSame('', $transfer->getSenderContactPerson());
    }

    #[Test]
    public function it_defaults_when_optional_data_is_missing(): void
    {
        $transfer = new BankTransfer([]);

        $this->assertSame('', $transfer->getAwbNumber());
        $this->assertSame(0.0, $transfer->getAmountCollected());
        $this->assertSame('', $transfer->getContent());
        $this->assertSame('', $transfer->getRecipientCity());
    }
}
