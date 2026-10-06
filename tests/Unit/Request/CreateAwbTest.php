<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Objects\AwbIntern;
use Fancourier\Request\CreateAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateAwbTest extends TestCase
{
    private function request(): CreateAwb
    {
        return (new CreateAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_shipment_list_by_default(): void
    {
        $this->assertSame(['clientId' => 12345, 'shipments' => []], $this->request()->pack());
    }

    #[Test]
    public function it_packs_each_awb_and_an_optional_platform_id(): void
    {
        $awb = new AwbIntern();
        $packed = $this->request()->addAwb($awb)->setPlatformId('PLATFORM-1')->pack();

        $this->assertSame(12345, $packed['clientId']);
        $this->assertSame('PLATFORM-1', $packed['platformId']);
        $this->assertCount(1, $packed['shipments']);
        $this->assertSame($awb->pack(), $packed['shipments'][0]);
    }
}
