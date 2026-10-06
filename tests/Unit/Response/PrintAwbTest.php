<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\PrintAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrintAwbTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_stores_a_non_json_binary_body(): void
    {
        $raw = '%PDF-1.4 fake shipping slip';
        $response = (new PrintAwb())->setData($raw);

        $this->assertTrue($response->isOk());
        $this->assertSame($raw, $response->getData());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new PrintAwb())->setData($this->fixture('printAwb.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame('fail', $response->getErrorCode());
        $this->assertSame('Cannot print AWB', $response->getErrorMessage());
    }

    #[Test]
    public function it_treats_an_unexpected_json_status_as_an_error(): void
    {
        $fixture = $this->fixture('printAwb.missing-keys');
        $response = (new PrintAwb())->setData($fixture);

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame($fixture, $response->getErrorMessage());
    }
}
