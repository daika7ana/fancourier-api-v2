<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetAwbConfirmations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetAwbConfirmationsTest extends TestCase
{
    private function request(): GetAwbConfirmations
    {
        return (new GetAwbConfirmations())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_awb_list_by_default(): void
    {
        $this->assertSame(['clientId' => 12345, 'awb' => []], $this->request()->pack());
    }

    #[Test]
    public function it_packs_all_added_awbs_and_can_reset_them(): void
    {
        $request = $this->request()->addAwb('A1')->setAwb('A2');

        $this->assertSame(['clientId' => 12345, 'awb' => ['A1', 'A2']], $request->pack());
        $this->assertSame(['clientId' => 12345, 'awb' => []], $request->resetAwbs()->pack());
    }
}
