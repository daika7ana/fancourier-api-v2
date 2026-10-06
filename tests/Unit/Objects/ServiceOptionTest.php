<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\ServiceOption;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ServiceOptionTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $option = new ServiceOption('A', 'Deschidere la livrare');

        $this->assertSame('A', $option->getCode());
        $this->assertSame('Deschidere la livrare', $option->getName());
    }
}
