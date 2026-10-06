<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCostsExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCostsExternalTest extends TestCase
{
    #[Test]
    public function it_packs_a_minimal_external_tariff_request(): void
    {
        $packed = $this->request()->setCountry('DE')->pack();

        $this->assertSame(12345, $packed['clientId']);
        $this->assertSame('rutier', $packed['info']['deliveryMode']);
        $this->assertSame('Export', $packed['info']['service']);
        $this->assertSame('document', $packed['info']['documentType']);
        $this->assertNull($packed['info']['weight']);
        $this->assertSame(
            ['height' => 0, 'width' => 0, 'length' => 0],
            $packed['info']['dimensions'],
        );
        $this->assertSame(
            ['envelope' => 0, 'parcel' => 0],
            $packed['info']['packages'],
        );
        $this->assertSame(['country' => 'DE'], $packed['recipient']);
        $this->assertArrayNotHasKey('sender', $packed);
    }

    #[Test]
    public function it_packs_a_fully_populated_external_tariff_request(): void
    {
        $packed = $this->request()
            ->setCountry('DE')
            ->setDeliveryMode('aerian')
            ->setDocumentType('non document')
            ->setWeight(5)
            ->setHeight(3)
            ->setWidth(2)
            ->setLength(1)
            ->setEnvelopes(1)
            ->setParcels(2)
            ->setSenderCity('Bucuresti')
            ->setSenderCounty('Ilfov')
            ->pack();

        $this->assertSame(
            [
                'clientId' => 12345,
                'info' => [
                    'service' => 'Export',
                    'deliveryMode' => 'aerian',
                    'documentType' => 'non document',
                    'weight' => 5,
                    'dimensions' => ['height' => 3, 'width' => 2, 'length' => 1],
                    'packages' => ['envelope' => 1, 'parcel' => 2],
                ],
                'recipient' => ['country' => 'DE'],
                'sender' => ['locality' => 'Bucuresti', 'county' => 'Ilfov'],
            ],
            $packed,
        );
    }

    #[Test]
    public function it_ignores_an_unsupported_delivery_mode(): void
    {
        $request = $this->request()->setDeliveryMode('aerian');
        $request->setDeliveryMode('submarin');

        $this->assertSame('aerian', $request->getDeliveryMode());
    }
    private function request(): GetCostsExternal
    {
        return (new GetCostsExternal())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }
}
