<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\CourierOrder;
use Fancourier\Response\GetCourierOrders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCourierOrdersTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCourierOrders())->setData($this->fixture('getCourierOrders.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(CourierOrder::class, $response->get(18601914));
        $this->assertSame('SI329300184', $response->get(18601914)->getNumber());
        $this->assertSame(3.0, $response->get(18601914)->getWeight());
        $this->assertSame(0.1, $response->get(18601914)->getHeight());
        $this->assertSame(1, $response->getTotal());
        $this->assertSame(10, $response->getPerPage());
        $this->assertSame(1, $response->getCurrentPage());
        $this->assertSame(1, $response->getTotalPages());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCourierOrders())->setData($this->fixture('getCourierOrders.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No orders', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        // getEnvelopes()/getParcels() read $this->packages['envelope'] before the ?? fallback
        // (cast precedence), so an order without packages would warn; not asserted here.
        $response = (new GetCourierOrders())->setData($this->fixture('getCourierOrders.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(CourierOrder::class, $response->get(99));
        $this->assertSame('', $response->get(99)->getNumber());
        $this->assertSame('', $response->get(99)->getDate());
        $this->assertSame(0.0, $response->get(99)->getWeight());
        $this->assertSame([], $response->get(99)->getAwbs());
    }
}
