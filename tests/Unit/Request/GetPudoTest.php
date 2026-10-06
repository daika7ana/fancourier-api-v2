<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetPudo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetPudoTest extends TestCase
{
    #[Test]
    public function it_packs_the_default_type_when_no_id_is_set(): void
    {
        $this->assertSame(['type' => 'fanbox'], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_id_instead_of_the_type_when_set(): void
    {
        $this->assertSame(['id' => 'PUDO-123'], $this->request()->setId('PUDO-123')->pack());
    }
    private function request(): GetPudo
    {
        return (new GetPudo())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }
}
