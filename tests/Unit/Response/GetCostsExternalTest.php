<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\GetCostsExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetCostsExternalTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetCostsExternal())->setData($this->fixture('getCostsExternal.success'));

        $this->assertTrue($response->isOk());
        $this->assertSame(1.2, $response->getKmCost());
        $this->assertSame(3.4, $response->getWeightCost());
        $this->assertSame(0.5, $response->getInsuranceCost());
        $this->assertSame(0.0, $response->getOptionsCost());
        $this->assertSame(2.1, $response->getFuelCost());
        $this->assertSame(20.0, $response->getCost());
        $this->assertSame(3.8, $response->getCostVat());
        $this->assertSame(23.8, $response->getCostTotal());
        $this->assertSame([], $response->getAllErrors());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetCostsExternal())->setData($this->fixture('getCostsExternal.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Cost calculation failed', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetCostsExternal())->setData($this->fixture('getCostsExternal.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame(0.0, $response->getKmCost());
        $this->assertSame(0.0, $response->getWeightCost());
        $this->assertSame(10.0, $response->getCostTotal());
        $this->assertSame([], $response->getAllErrors());
    }
}
