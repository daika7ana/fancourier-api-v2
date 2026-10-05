<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\Street;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StreetTest extends TestCase
{
    private function data(): array
    {
        return [
            'id' => 17,
            'street' => 'Fabrica de Glucoza',
            'type' => 'Sosea',
            'county' => 'Bucuresti',
            'locality' => 'Bucuresti',
            'details' => [
                ['zipCode' => '020331', 'sector' => '2'],
                ['zipCode' => '020332', 'sector' => '2'],
            ],
        ];
    }

    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $street = new Street($this->data());

        $this->assertSame('17', $street->getId());
        $this->assertSame('Fabrica de Glucoza', $street->getName());
        $this->assertSame('Sosea', $street->getType());
        $this->assertSame('Bucuresti', $street->getCounty());
        $this->assertSame('Bucuresti', $street->getCity());
    }

    #[Test]
    public function it_indexes_details_by_zip_code(): void
    {
        $street = new Street($this->data());

        $this->assertTrue($street->hasZipCode('020331'));
        $this->assertFalse($street->hasZipCode('999999'));
        $this->assertSame(['zipCode' => '020331', 'sector' => '2'], $street->getDetails('020331'));
        $this->assertFalse($street->getDetails('999999'));
    }

    #[Test]
    public function it_returns_an_api_shaped_array(): void
    {
        $street = new Street($this->data());

        $this->assertSame([
            'id' => 17,
            'street' => 'Fabrica de Glucoza',
            'type' => 'Sosea',
            'locality' => 'Bucuresti',
            'county' => 'Bucuresti',
            'details' => [
                '020331' => ['zipCode' => '020331', 'sector' => '2'],
                '020332' => ['zipCode' => '020332', 'sector' => '2'],
            ],
        ], $street->getArray());
    }
}
