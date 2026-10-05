<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\CreateCourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateCourierOrderTest extends TestCase
{
    private function request(): CreateCourierOrder
    {
        return (new CreateCourierOrder())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_a_standard_order_without_a_recipient_block(): void
    {
        // UPGRADE_PLAN §7 #3/#4 — Phase 3: getPickupDate() returns notes and getAwb() has an
        // unused required parameter; neither getter is asserted here.
        $packed = $this->request()
            ->setAwb('A1')
            ->setParcels(2)
            ->setEnvelopes(1)
            ->setWeight(3.5)
            ->setSizes(10, 20, 30)
            ->setPickupDate('2024-01-02')
            ->setPickupHours('10:00', '16:00')
            ->setNotes('leave at the gate')
            ->pack();

        $this->assertSame(
            [
                'clientId' => 12345,
                'info' => [
                    'awbNumber' => 'A1',
                    'packages' => ['parcel' => 2, 'envelope' => 1],
                    'weight' => 3.5,
                    'dimensions' => ['width' => 30, 'length' => 10, 'height' => 20],
                    'orderType' => 'Standard',
                    'pickupDate' => '2024-01-02',
                    'pickupHours' => ['first' => '10:00', 'second' => '16:00'],
                    'observations' => 'leave at the gate',
                ],
            ],
            $packed
        );
        $this->assertArrayNotHasKey('recipient', $packed);
    }

    #[Test]
    public function it_packs_the_recipient_block_for_a_non_standard_order(): void
    {
        $packed = $this->request()
            ->setOrderType('Express Loco')
            ->setPickupHours('10:00', '16:00')
            ->setRecipientName('John Doe')
            ->setContactPerson('Jane Doe')
            ->setPhone('0700000000')
            ->setAltPhone('0711111111')
            ->setEmail('jane@example.com')
            ->setCounty('Cluj')
            ->setCity('Cluj-Napoca')
            ->setStreet('Main')
            ->setNumber(12)
            ->setPostalCode('400001')
            ->setBuilding('B1')
            ->setEntrance('2')
            ->setFloor('3')
            ->setApartment('4')
            ->pack();

        $this->assertSame(
            [
                'name' => 'John Doe',
                'contactPerson' => 'Jane Doe',
                'phone' => '0700000000',
                'secondaryPhone' => '0711111111',
                'email' => 'jane@example.com',
                'address' => [
                    'county' => 'Cluj',
                    'locality' => 'Cluj-Napoca',
                    'street' => 'Main',
                    'streetNo' => '12',
                    'zipCode' => '400001',
                    'building' => 'B1',
                    'entrance' => '2',
                    'floor' => '3',
                    'apartment' => '4',
                ],
            ],
            $packed['recipient']
        );
    }
}
