<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\DeleteAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DeleteAwbTest extends TestCase
{
    #[Test]
    public function it_reports_success_as_true(): void
    {
        $response = (new DeleteAwb())->setData($this->fixture('deleteAwb.success'));

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->getData());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new DeleteAwb())->setData($this->fixture('deleteAwb.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('AWB not found', $response->getErrorMessage());
        $this->assertFalse($response->getData());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new DeleteAwb())->setData($this->fixture('deleteAwb.missing-keys'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Missing status', $response->getErrorMessage());
        $this->assertFalse($response->getData());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
