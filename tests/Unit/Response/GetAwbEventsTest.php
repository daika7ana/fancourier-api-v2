<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\AwbEvent;
use Fancourier\Response\GetAwbEvents;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetAwbEventsTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetAwbEvents())->setData($this->fixture('getAwbEvents.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(2, $response->getAll());
        $this->assertInstanceOf(AwbEvent::class, $response->getEvent('C0'));
        $this->assertSame('Expeditie ridicata', $response->getEvent('C0')->getName());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetAwbEvents())->setData($this->fixture('getAwbEvents.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Invalid AWB', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetAwbEvents())->setData($this->fixture('getAwbEvents.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->getEvent('C0'));
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
