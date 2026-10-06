<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\Street;
use Fancourier\Response\GetStreets;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetStreetsTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetStreets())->setData($this->fixture('getStreets.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(Street::class, $response->getAll()[100]);
        $this->assertSame('Test Street', $response->getAll()[100]->getName());
        $this->assertTrue($response->getAll()[100]->hasZipCode('000000'));
        $this->assertSame('Bucuresti', $response->getAll()[100]->getCity());
        $this->assertSame(1, $response->getTotal());
        $this->assertSame(10, $response->getPerPage());
        $this->assertSame(1, $response->getCurrentPage());
        $this->assertSame(1, $response->getTotalPages());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetStreets())->setData($this->fixture('getStreets.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No streets', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetStreets())->setData($this->fixture('getStreets.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertSame(0, $response->getTotal());
        $this->assertSame(0, $response->getTotalPages());
    }
}
