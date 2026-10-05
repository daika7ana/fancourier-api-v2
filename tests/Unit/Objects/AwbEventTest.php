<?php

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\AwbEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AwbEventTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $event = new AwbEvent('C0', 'Expeditie ridicata');

        $this->assertSame('C0', $event->getId());
        $this->assertSame('Expeditie ridicata', $event->getName());
    }
}
