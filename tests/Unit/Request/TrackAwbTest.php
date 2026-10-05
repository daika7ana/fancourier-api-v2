<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\TrackAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TrackAwbTest extends TestCase
{
    private function request(): TrackAwb
    {
        return (new TrackAwb())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_awb_list_by_default(): void
    {
        $this->assertSame(['clientId' => 12345, 'awb' => []], $this->request()->pack());
    }

    #[Test]
    public function it_packs_all_added_awbs_and_the_language(): void
    {
        $request = $this->request()->addAwb('A1')->setAwb('A2')->setLanguage('en');

        $this->assertSame(
            ['clientId' => 12345, 'awb' => ['A1', 'A2'], 'language' => 'en'],
            $request->pack()
        );
    }

    #[Test]
    public function it_can_reset_the_awb_list(): void
    {
        $request = $this->request()->addAwb('A1');

        $this->assertSame(['clientId' => 12345, 'awb' => []], $request->resetAwbs()->pack());
    }
}
