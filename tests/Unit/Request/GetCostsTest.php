<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCosts;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCostsTest extends TestCase
{
    private function request(): GetCosts
    {
        return (new GetCosts())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_a_minimal_internal_tariff_request(): void
    {
        $this->assertSame(
            [
                'clientId' => 12345,
                'info' => [
                    'service' => 'Standard',
                    'payment' => 'destinatar',
                    'weight' => null,
                    'packages' => [],
                ],
                'recipient' => [
                    'locality' => null,
                    'county' => null,
                ],
            ],
            $this->request()->pack()
        );
    }

    #[Test]
    public function it_packs_a_fully_populated_internal_tariff_request(): void
    {
        $packed = $this->request()
            ->setService('Rutier')
            ->setPaymentType(GetCosts::TYPE_SENDER)
            ->setWeight(2.5)
            ->setEnvelopes(1)
            ->setParcels(2)
            ->setOptions('AY')
            ->addOption('z')
            ->setHeight(30)
            ->setWidth(20)
            ->setLength(10)
            ->setDeclaredValue(100)
            ->setCity('Cluj')
            ->setCounty('Cluj')
            ->setSenderCity('Bucuresti')
            ->setSenderCounty('Ilfov')
            ->pack();

        $this->assertSame(
            [
                'clientId' => 12345,
                'info' => [
                    'service' => 'Rutier',
                    'payment' => 'expeditor',
                    'weight' => 2.5,
                    'packages' => ['envelope' => 1, 'parcel' => 2],
                    'options' => ['A', 'Y', 'Z'],
                    'dimensions' => ['height' => 30, 'width' => 20, 'length' => 10],
                    'declaredValue' => 100,
                ],
                'recipient' => ['locality' => 'Cluj', 'county' => 'Cluj'],
                'sender' => ['locality' => 'Bucuresti', 'county' => 'Ilfov'],
            ],
            $packed
        );
    }

    #[Test]
    public function it_rejects_an_invalid_payment_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->request()->setPaymentType('bogus');
    }
}
