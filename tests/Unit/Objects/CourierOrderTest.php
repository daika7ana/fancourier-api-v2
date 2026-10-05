<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\CourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CourierOrderTest extends TestCase
{
    private function data(): array
    {
        return [
            'info' => [
                'id' => '18601914',
                'number' => 'SI329300184',
                'status' => ['id' => 0, 'name' => 'In asteptare'],
                'date' => '2023-11-25',
                'hour' => '09:00',
                'packages' => ['envelope' => 0, 'parcel' => 3],
                'weight' => 3,
                'dimensions' => ['height' => 0.1, 'length' => 0.2, 'width' => 0.3],
                'pickupDate' => '2023-11-25',
                'pickupHours' => ['firstHour' => '10:00', 'secondHour' => '13:00'],
                'observation' => 'call before',
                'type' => 'Standard',
                'awbs' => ['123'],
            ],
            'sender' => ['name' => 'FAN COURIER - cont test'],
        ];
    }

    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $order = new CourierOrder($this->data());

        $this->assertSame('18601914', $order->getId());
        $this->assertSame('SI329300184', $order->getNumber());
        $this->assertSame(['id' => 0, 'name' => 'In asteptare'], $order->getStatus());
        $this->assertSame('2023-11-25', $order->getDate());
        $this->assertSame('09:00', $order->getHour());
        $this->assertSame(0, $order->getEnvelopes());
        $this->assertSame(3, $order->getParcels());
        $this->assertSame(3.0, $order->getWeight());
        $this->assertSame(['height' => 0.1, 'length' => 0.2, 'width' => 0.3], $order->getDimensions());
        $this->assertSame(0.1, $order->getHeight());
        $this->assertSame(0.2, $order->getLength());
        $this->assertSame(0.3, $order->getWidth());
        $this->assertSame('2023-11-25', $order->getPickupDate());
        $this->assertSame(['firstHour' => '10:00', 'secondHour' => '13:00'], $order->getPickupHours());
        $this->assertSame('call before', $order->getNotes());
        $this->assertSame('Standard', $order->getType());
        $this->assertSame(['123'], $order->getAwbs());
        $this->assertSame(['name' => 'FAN COURIER - cont test'], $order->getSender());
    }

    #[Test]
    public function it_defaults_when_optional_data_is_missing(): void
    {
        $order = new CourierOrder([]);

        $this->assertSame('', $order->getId());
        $this->assertSame([], $order->getStatus());
        // getEnvelopes()/getParcels() read missing array keys when packages is empty
        // (undefined-key warning), so they are only exercised with populated data above.
        $this->assertSame(0.0, $order->getWeight());
        // UPGRADE_PLAN §7 #7 — dimensions must default to a float, not [].
        $this->assertSame(0.0, $order->getHeight());
        $this->assertSame(0.0, $order->getLength());
        $this->assertSame(0.0, $order->getWidth());
        $this->assertSame([], $order->getDimensions());
        $this->assertSame([], $order->getPickupHours());
    }
}
