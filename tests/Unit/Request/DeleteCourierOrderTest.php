<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\DeleteCourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeleteCourierOrderTest extends TestCase
{
    private function request(): DeleteCourierOrder
    {
        return (new DeleteCourierOrder())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_a_null_id_by_default(): void
    {
        $this->assertSame(['clientId' => 12345, 'id' => null], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_order_id_to_delete(): void
    {
        $this->assertSame(
            ['clientId' => 12345, 'id' => 'ORDER-1'],
            $this->request()->setOrder('ORDER-1')->pack()
        );
    }
}
