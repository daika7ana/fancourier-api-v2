<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\Pudo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PudoTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $pudo = new Pudo($this->data());

        $this->assertSame('PUDO-1', $pudo->getId());
        $this->assertSame('FANbox Bucuresti', $pudo->getName());
        $this->assertSame('BUC', $pudo->getRoutingLocation());
        $this->assertSame('Lockers', $pudo->getDescription());
        $this->assertSame('44.5', $pudo->getLatitude());
        $this->assertSame('26.1', $pudo->getLongitude());
        $this->assertSame(['locality' => 'Bucuresti', 'street' => 'Fabrica de Glucoza'], $pudo->getAddress());
        $this->assertSame(['Mon-Fri' => '08:00-20:00'], $pudo->getSchedule());
        $this->assertSame(['small' => true], $pudo->getDrawer());
        $this->assertSame(['0700000000'], $pudo->getPhones());
        $this->assertSame('pudo@example.com', $pudo->getEmail());
        $this->assertTrue($pudo->getHighDemand());
        $this->assertSame(['card', 'cash'], $pudo->getPaymentMethods());
    }

    #[Test]
    public function it_returns_an_api_shaped_array(): void
    {
        $pudo = new Pudo($this->data());

        $array = $pudo->getArray();

        $this->assertSame($this->data(), $array);
    }

    #[Test]
    public function it_defaults_optional_fields_when_data_is_empty(): void
    {
        $pudo = new Pudo([]);

        $this->assertSame('', $pudo->getId());
        $this->assertSame('', $pudo->getName());
        $this->assertSame([], $pudo->getSchedule());
        // getAddress() must fall back to [], not ''.
        $this->assertSame([], $pudo->getAddress());
        $this->assertFalse($pudo->getHighDemand());
    }
    private function data(): array
    {
        return [
            'id' => 'PUDO-1',
            'name' => 'FANbox Bucuresti',
            'routingLocation' => 'BUC',
            'description' => 'Lockers',
            'latitude' => '44.5',
            'longitude' => '26.1',
            'address' => ['locality' => 'Bucuresti', 'street' => 'Fabrica de Glucoza'],
            'schedule' => ['Mon-Fri' => '08:00-20:00'],
            'drawer' => ['small' => true],
            'phones' => ['0700000000'],
            'email' => 'pudo@example.com',
            'highDemand' => true,
            'paymentMethods' => ['card', 'cash'],
        ];
    }
}
