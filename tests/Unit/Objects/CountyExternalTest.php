<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Objects;

use Fancourier\Objects\CountyExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CountyExternalTest extends TestCase
{
    #[Test]
    public function it_exposes_the_constructor_values(): void
    {
        $county = new CountyExternal('24', 'Ialomita', 'IL', 'Romania');

        $this->assertSame('24', $county->getId());
        $this->assertSame('Ialomita', $county->getName());
        $this->assertSame('IL', $county->getCode());
        $this->assertSame('Romania', $county->getCountry());
    }
}
