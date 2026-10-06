<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\County;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CountyTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $county = new County('24', 'Ialomita');

        $this->assertSame('24', $county->getId());
        $this->assertSame('Ialomita', $county->getName());
    }
}
