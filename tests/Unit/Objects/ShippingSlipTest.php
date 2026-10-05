<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\ShippingSlip;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippingSlipTest extends TestCase
{
    private function data(): array
    {
        return [
            'info' => [
                'awbNumber' => '6324356450139',
                'service' => 'Cont Colector',
                'serviceId' => '4',
                'weight' => '1',
                'dimensions' => ['height' => 10, 'width' => 20, 'length' => 30],
                'payment' => 14.4,
                'returnPayment' => 3.5,
                'cod' => 75.29,
                'declaredValue' => 59.88,
                'observations' => 'POS',
                'content' => 'Order #465',
                'packages' => ['envelope' => 1, 'parcel' => 2],
                'date' => '2023-11-20 18:53:07',
                'cost' => 14.4,
                'costCenter' => 'CC1',
                'refund' => 'refunded',
                'currency' => 'LEI',
            ],
            'recipient' => ['name' => 'COM S.R.L.'],
            'sender' => ['name' => 'NETWORK SRL'],
        ];
    }

    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $slip = new ShippingSlip($this->data());

        $this->assertSame('6324356450139', $slip->getAwbNumber());
        $this->assertSame('Cont Colector', $slip->getService());
        $this->assertSame('4', $slip->getServiceId());
        $this->assertSame('1', $slip->getWeight());
        $this->assertSame(10.0, $slip->getHeight());
        $this->assertSame(20.0, $slip->getWidth());
        $this->assertSame(30.0, $slip->getLength());
        $this->assertSame(14.4, $slip->getPayment());
        $this->assertSame(3.5, $slip->getReturnPayment());
        $this->assertSame(75.29, $slip->getReimbursement());
        $this->assertSame(59.88, $slip->getDeclaredValue());
        $this->assertSame('POS', $slip->getNotes());
        $this->assertSame('Order #465', $slip->getContents());
        $this->assertSame(1, $slip->getEnvelopes());
        $this->assertSame(2, $slip->getParcels());
        $this->assertSame('2023-11-20 18:53:07', $slip->getDateTime());
        $this->assertSame(14.4, $slip->getCost());
        $this->assertSame('CC1', $slip->getCostCenter());
        $this->assertSame('refunded', $slip->getRefund());
        $this->assertSame('LEI', $slip->getCurrency());
        $this->assertSame(['name' => 'COM S.R.L.'], $slip->getRecipient());
        $this->assertSame(['name' => 'NETWORK SRL'], $slip->getSender());
    }

    #[Test]
    public function it_defaults_when_optional_data_is_missing(): void
    {
        $slip = new ShippingSlip([]);

        $this->assertSame('', $slip->getAwbNumber());
        $this->assertSame(0.0, $slip->getHeight());
        $this->assertSame(0, $slip->getEnvelopes());
        $this->assertSame(0, $slip->getParcels());
        $this->assertSame([], $slip->getRecipient());
        $this->assertSame([], $slip->getSender());
    }
}
