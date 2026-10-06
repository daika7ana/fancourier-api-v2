<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\GetAwbConfirmations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetAwbConfirmationsTest extends TestCase
{
    #[Test]
    public function it_accepts_a_success_body_without_payload(): void
    {
        $response = (new GetAwbConfirmations())->setData($this->fixture('getAwbConfirmations.success'));

        $this->assertTrue($response->isOk());
        $this->assertNull($response->getData());
    }

    #[Test]
    public function it_keeps_a_zip_body_as_raw_data(): void
    {
        $raw = "PK\x03\x04fake-zip-bytes";
        $response = (new GetAwbConfirmations())->setData($raw);

        $this->assertTrue($response->isOk());
        $this->assertSame($raw, $response->getData());
        $this->assertSame($raw, $response->getRAWbytes());
        $this->assertSame(strlen($raw), $response->getLength());
    }

    /**
     * Defect #9 (UPGRADE_PLAN §7): getRAWbytes() is nullable and getLength()
     * returns an int.
     */
    #[Test]
    public function it_exposes_null_raw_bytes_when_no_zip_was_received(): void
    {
        $response = (new GetAwbConfirmations())->setData($this->fixture('getAwbConfirmations.success'));

        $this->assertTrue($response->isOk());
        $this->assertNull($response->getRAWbytes());
        $this->assertSame(0, $response->getLength());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetAwbConfirmations())->setData($this->fixture('getAwbConfirmations.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No confirmations', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetAwbConfirmations())->setData($this->fixture('getAwbConfirmations.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertNull($response->getData());
        $this->assertNull($response->getRAWbytes());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
