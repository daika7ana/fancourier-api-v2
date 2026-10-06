<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\Country;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $country = new Country([
            'id' => '14',
            'name' => 'Letonia',
            'code' => 'LV',
            'deliveryMode' => [
                ['id' => 2, 'name' => 'Aerian'],
                ['id' => 1, 'name' => 'Rutier'],
            ],
        ]);

        $this->assertSame('14', $country->getId());
        $this->assertSame('Letonia', $country->getName());
        $this->assertSame('LV', $country->getCode());
        $this->assertSame([2 => 'Aerian', 1 => 'Rutier'], $country->getShipping());
        $this->assertTrue($country->hasAirShipping());
        $this->assertTrue($country->hasLandShipping());
    }

    #[Test]
    public function it_reports_missing_shipping_modes(): void
    {
        $country = new Country([
            'id' => '89',
            'name' => 'Laos',
            'code' => 'LA',
            'deliveryMode' => [
                ['id' => 2, 'name' => 'Aerian'],
            ],
        ]);

        $this->assertTrue($country->hasAirShipping());
        $this->assertFalse($country->hasLandShipping());
    }
}
