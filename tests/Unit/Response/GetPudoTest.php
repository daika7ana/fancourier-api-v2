<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\Pudo;
use Fancourier\Response\GetPudo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetPudoTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetPudo())->setData($this->fixture('getPudo.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(Pudo::class, $response->get('123'));
        $this->assertSame('Pudo Test', $response->get('123')->getName());
        $this->assertSame('Bucuresti', $response->get('123')->getRoutingLocation());
        $this->assertInstanceOf(Pudo::class, $response->get());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetPudo())->setData($this->fixture('getPudo.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No pudo', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        // UPGRADE_PLAN §7 #10 — getAddress() falls back to '' for an array return; Phase 3.
        $response = (new GetPudo())->setData($this->fixture('getPudo.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(Pudo::class, $response->get('1'));
        $this->assertSame('', $response->get('1')->getName());
        $this->assertSame('', $response->get('1')->getEmail());
        $this->assertFalse($response->get('1')->getHighDemand());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
