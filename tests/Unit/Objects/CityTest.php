<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\City;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CityTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $city = new City('11', 'Bucuresti', 'Bucuresti', 'Bucuresti', 3.5);

        $this->assertSame('11', $city->getId());
        $this->assertSame('Bucuresti', $city->getName());
        $this->assertSame('Bucuresti', $city->getCounty());
        $this->assertSame('Bucuresti', $city->getAgency());
        $this->assertSame(3.5, $city->getExtKm());
    }
}
