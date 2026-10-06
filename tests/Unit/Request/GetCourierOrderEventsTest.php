<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetCourierOrderEvents;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetCourierOrderEventsTest extends TestCase
{
    private function request(): GetCourierOrderEvents
    {
        return (new GetCourierOrderEvents())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_query_by_default(): void
    {
        $this->assertSame([], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_language_when_a_supported_one_is_set(): void
    {
        $this->assertSame(['language' => 'ro'], $this->request()->setLanguage('RO')->pack());
    }

    #[Test]
    public function it_ignores_an_unsupported_language(): void
    {
        $this->assertSame([], $this->request()->setLanguage('de')->pack());
    }
}
