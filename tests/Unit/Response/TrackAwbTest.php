<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\AwbTracker;
use Fancourier\Response\TrackAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TrackAwbTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new TrackAwb())->setData($this->fixture('trackAwb.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(AwbTracker::class, $response->getAwb(2347300120337));
        $this->assertSame('2347300120337', $response->getAwb(2347300120337)->getAwbNumber());
        $this->assertSame('24H', $response->getAwb(2347300120337)->getOTD());
        $this->assertCount(1, $response->getAwb(2347300120337)->getEvents());
        $this->assertFalse($response->getAwb(999999));
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new TrackAwb())->setData($this->fixture('trackAwb.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Invalid AWB', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new TrackAwb())->setData($this->fixture('trackAwb.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(AwbTracker::class, $response->getAwb(2347300120337));
        $this->assertSame('', $response->getAwb(2347300120337)->getMessage());
        $this->assertSame([], $response->getAwb(2347300120337)->getEvents());
        $this->assertFalse($response->getAwb(2347300120337)->hasConfirmation());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
