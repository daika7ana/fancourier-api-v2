<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\CreateAwbBankAccount;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateAwbBankAccountTest extends TestCase
{
    #[Test]
    public function it_parses_the_native_success_body(): void
    {
        $response = (new CreateAwbBankAccount())->setData(
            '{"message": "Records inserted successfully", "inserted": 10}',
        );

        $this->assertTrue($response->isOk());
        $this->assertSame(10, $response->getInserted());
        $this->assertSame('Records inserted successfully', $response->getMessage());
        $this->assertSame(10, $response->getData());
    }

    #[Test]
    public function it_reports_a_malformed_body_as_an_error(): void
    {
        $response = (new CreateAwbBankAccount())->setData('not json');

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('not json', $response->getErrorMessage());
        $this->assertNull($response->getInserted());
        $this->assertNull($response->getMessage());
    }

    #[Test]
    public function it_reports_a_body_without_inserted_as_an_error(): void
    {
        $response = (new CreateAwbBankAccount())->setData('{"message": "Bad IBAN"}');

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Bad IBAN', $response->getErrorMessage());
        $this->assertNull($response->getInserted());
    }
}
