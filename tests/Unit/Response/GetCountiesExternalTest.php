<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\CountyExternal;
use Fancourier\Response\GetCountiesExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCountiesExternalTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCountiesExternal())->setData($this->fixture('getCountiesExternal.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(CountyExternal::class, $response->getAll()['1']);
        $this->assertSame('Sofia', $response->getAll()['1']->getName());
        $this->assertSame('SOF', $response->getAll()['1']->getCode());
        $this->assertSame('Bulgaria', $response->getAll()['1']->getCountry());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCountiesExternal())->setData($this->fixture('getCountiesExternal.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No counties', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCountiesExternal())->setData($this->fixture('getCountiesExternal.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
    }
}
