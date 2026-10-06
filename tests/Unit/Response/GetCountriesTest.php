<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\Country;
use Fancourier\Response\GetCountries;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCountriesTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCountries())->setData($this->fixture('getCountries.success'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(Country::class, $response->getCountry('Romania'));
        $this->assertSame('RO', $response->getCountry('Romania')->getCode());
        $this->assertTrue($response->getCountry('Romania')->hasLandShipping());
        $this->assertTrue($response->getCountry('Romania')->hasAirShipping());
        $this->assertSame([1 => 'Rutier', 2 => 'Aerian'], $response->getCountry('Romania')->getShipping());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCountries())->setData($this->fixture('getCountries.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No countries', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCountries())->setData($this->fixture('getCountries.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->getCountry('Romania'));
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
