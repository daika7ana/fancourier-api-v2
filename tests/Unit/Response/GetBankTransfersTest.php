<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\BankTransfer;
use Fancourier\Response\GetBankTransfers;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetBankTransfersTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetBankTransfers())->setData($this->fixture('getBankTransfers.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(2, $response->getAll());
        $this->assertInstanceOf(BankTransfer::class, $response->get(0));
        $this->assertSame(670.33, $response->get(0)->getAmountCollected());
        $this->assertSame(2, $response->getTotal());
        $this->assertSame(10, $response->getPerPage());
        $this->assertSame(1, $response->getCurrentPage());
        $this->assertSame(1, $response->getTotalPages());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetBankTransfers())->setData($this->fixture('getBankTransfers.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No transfers', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetBankTransfers())->setData($this->fixture('getBankTransfers.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(BankTransfer::class, $response->get(0));
        $this->assertSame('', $response->get(0)->getAwbNumber());
        $this->assertSame(0.0, $response->get(0)->getAmountCollected());
        $this->assertSame('', $response->get(0)->getContent());
        $this->assertSame(1, $response->getTotal());
    }
}
