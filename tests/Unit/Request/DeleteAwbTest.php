<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\DeleteAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteAwbTest extends TestCase
{
    #[Test]
    public function it_packs_a_null_awb_by_default(): void
    {
        $this->assertSame(['clientId' => 12345, 'awb' => null], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_awb_to_delete(): void
    {
        $this->assertSame(
            ['clientId' => 12345, 'awb' => '2347300120337'],
            $this->request()->setAwb('2347300120337')->pack(),
        );
    }
    private function request(): DeleteAwb
    {
        return (new DeleteAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }
}
