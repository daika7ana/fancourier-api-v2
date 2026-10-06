<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\CourierOrderEvent;
use Fancourier\Response\GetCourierOrderEvents;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCourierOrderEventsTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCourierOrderEvents())->setData($this->fixture('getCourierOrderEvents.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(2, $response->getAll());
        $this->assertInstanceOf(CourierOrderEvent::class, $response->getEvent('0'));
        $this->assertSame('In asteptare', $response->getEvent('0')->getName());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCourierOrderEvents())->setData($this->fixture('getCourierOrderEvents.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Invalid order', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCourierOrderEvents())->setData($this->fixture('getCourierOrderEvents.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->getEvent('0'));
    }
}
