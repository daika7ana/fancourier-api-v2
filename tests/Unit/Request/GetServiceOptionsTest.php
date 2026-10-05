<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetServiceOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetServiceOptionsTest extends TestCase
{
    private function request(): GetServiceOptions
    {
        return (new GetServiceOptions())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_the_default_service(): void
    {
        $this->assertSame(
            ['clientId' => 12345, 'service' => 'Standard'],
            $this->request()->pack()
        );
    }

    #[Test]
    public function it_packs_a_custom_service(): void
    {
        $this->assertSame(
            ['clientId' => 12345, 'service' => 'Rutier'],
            $this->request()->setService('Rutier')->pack()
        );
    }
}
