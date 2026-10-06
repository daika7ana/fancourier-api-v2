<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\CityExternal;
use Fancourier\Response\GetCitiesExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCitiesExternalTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCitiesExternal())->setData($this->fixture('getCitiesExternal.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(CityExternal::class, $response->getAll()['1']);
        $this->assertSame('Sofia', $response->getAll()['1']->getName());
        $this->assertSame(1, $response->getTotal());
        $this->assertSame(20, $response->getPerPage());
        $this->assertSame(1, $response->getCurrentPage());
        $this->assertSame(1, $response->getTotalPages());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCitiesExternal())->setData($this->fixture('getCitiesExternal.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No cities', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCitiesExternal())->setData($this->fixture('getCitiesExternal.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertSame(0, $response->getTotal());
        $this->assertSame(0, $response->getTotalPages());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
