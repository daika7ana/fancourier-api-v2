<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\County;
use Fancourier\Response\GetCounties;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCountiesTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCounties())->setData($this->fixture('getCounties.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(2, $response->getAll());
        $this->assertInstanceOf(County::class, $response->getCounty('Cluj'));
        $this->assertSame('12', $response->getCounty('Cluj')->getId());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCounties())->setData($this->fixture('getCounties.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No counties', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCounties())->setData($this->fixture('getCounties.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->getCounty('Cluj'));
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
