<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\CourierOrderEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CourierOrderEventTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $event = new CourierOrderEvent('1', 'In asteptare');

        $this->assertSame('1', $event->getId());
        $this->assertSame('In asteptare', $event->getName());
    }
}
