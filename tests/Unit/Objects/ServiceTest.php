<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\Service;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $service = new Service('4', 'Cont Colector', 'Ramburs');

        $this->assertSame('4', $service->getId());
        $this->assertSame('Cont Colector', $service->getName());
        $this->assertSame('Ramburs', $service->getDescription());
    }
}
