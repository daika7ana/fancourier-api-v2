<?php

namespace Fancourier\Tests\Unit\Request;

use Fancourier\Auth;
use Fancourier\Request\GetAwbEvents;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GetAwbEventsTest extends TestCase
{
    private function request(): GetAwbEvents
    {
        return (new GetAwbEvents())->authenticate(new Auth(12345, 'user', 'pass', 'test-token'));
    }

    #[Test]
    public function it_packs_an_empty_query_by_default(): void
    {
        $this->assertSame([], $this->request()->pack());
    }

    #[Test]
    public function it_packs_the_language_when_a_supported_one_is_set(): void
    {
        $this->assertSame(['language' => 'en'], $this->request()->setLanguage('EN')->pack());
    }

    #[Test]
    public function it_ignores_an_unsupported_language(): void
    {
        $this->assertSame([], $this->request()->setLanguage('fr')->pack());
    }
}
