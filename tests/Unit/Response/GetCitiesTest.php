<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\City;
use Fancourier\Response\GetCities;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCitiesTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCities())->setData($this->fixture('getCities.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(2, $response->getAll());
        $this->assertInstanceOf(City::class, $response->getCity('fetesti'));
        $this->assertSame(12.5, $response->getCity('fetesti')->getExtKm());
        $this->assertSame('Bucuresti', $response->getCity('Bucuresti')->getCounty());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCities())->setData($this->fixture('getCities.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No cities', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCities())->setData($this->fixture('getCities.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->getCity('Bucuresti'));
    }
}
