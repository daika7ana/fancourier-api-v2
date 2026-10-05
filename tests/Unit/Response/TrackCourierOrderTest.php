<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\CourierOrderTracker;
use Fancourier\Response\TrackCourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TrackCourierOrderTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new TrackCourierOrder())->setData($this->fixture('trackCourierOrder.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(CourierOrderTracker::class, $response->getOrder(18601914));
        $this->assertSame('SI329300184', $response->getOrder(18601914)->getOrderNo());
        $this->assertCount(1, $response->getOrder(18601914)->getEvents());
        $this->assertSame('In asteptare', $response->getOrder(18601914)->getStatus()['name']);
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new TrackCourierOrder())->setData($this->fixture('trackCourierOrder.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Invalid order', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new TrackCourierOrder())->setData($this->fixture('trackCourierOrder.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(CourierOrderTracker::class, $response->getOrder(18601914));
        $this->assertSame('', $response->getOrder(18601914)->getOrderNo());
        $this->assertSame([], $response->getOrder(18601914)->getEvents());
        $this->assertSame('', $response->getOrder(18601914)->getStatus()['name']);
    }
}
