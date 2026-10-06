<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\CityExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CityExternalTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $city = new CityExternal('233', 'Fetesti', 'Ialomita', 'Romania');

        $this->assertSame('233', $city->getId());
        $this->assertSame('Fetesti', $city->getName());
        $this->assertSame('Ialomita', $city->getCounty());
        $this->assertSame('Romania', $city->getCountry());
    }
}
