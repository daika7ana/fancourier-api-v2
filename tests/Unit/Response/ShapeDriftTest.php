<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\BankTransfer;
use Fancourier\Objects\CityExternal;
use Fancourier\Objects\ShippingSlip;
use Fancourier\Response\GetBankTransfers;
use Fancourier\Response\GetCitiesExternal;
use Fancourier\Response\GetCosts;
use Fancourier\Response\GetCostsExternal;
use Fancourier\Response\GetCounties;
use Fancourier\Response\GetShippingSlip;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Shape-drift guard: the API sometimes returns int IDs where the parsed model
 * exposes strings, numeric-string money, perPage=0, and omits optional keys.
 * The parsers must normalize those at the boundary and never TypeError.
 */
class ShapeDriftTest extends TestCase
{
    #[Test]
    public function costs_normalizes_numeric_string_money_and_missing_keys(): void
    {
        $response = (new GetCosts())->setData($this->fixture('getCosts.drift'));

        $this->assertTrue($response->isOk());
        $this->assertSame(1.2, $response->getKmCost());
        $this->assertSame(3.4, $response->getWeightCost());
        $this->assertSame(0.5, $response->getInsuranceCost());
        $this->assertSame(0.0, $response->getOptionsCost());
        $this->assertSame(2.1, $response->getFuelCost());
        $this->assertSame(23.8, $response->getCostTotal());
        $this->assertSame([], $response->getAllErrors());
    }

    #[Test]
    public function costs_external_normalizes_numeric_string_money_and_null(): void
    {
        $response = (new GetCostsExternal())->setData($this->fixture('getCostsExternal.drift'));

        $this->assertTrue($response->isOk());
        $this->assertSame(0.0, $response->getKmCost());
        $this->assertSame(3.0, $response->getWeightCost());
        $this->assertSame(2.0, $response->getOptionsCost());
        $this->assertSame(0.0, $response->getFuelCost());
        $this->assertSame(24.4, $response->getCostTotal());
    }

    #[Test]
    public function bank_transfers_does_not_divide_by_zero_when_per_page_is_zero(): void
    {
        $response = (new GetBankTransfers())->setData($this->fixture('getBankTransfers.drift'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(BankTransfer::class, $response->get(0));
        $this->assertSame(670.33, $response->get(0)->getAmountCollected());
        $this->assertSame(0, $response->getPerPage());
        $this->assertSame(3, $response->getTotalPages());
    }

    #[Test]
    public function shipping_slip_does_not_divide_by_zero_when_per_page_is_zero(): void
    {
        $response = (new GetShippingSlip())->setData($this->fixture('getShippingSlip.drift'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(ShippingSlip::class, $response->get(0));
        $this->assertSame('4', $response->get(0)->getServiceId());
        $this->assertSame(14.4, $response->get(0)->getCost());
        $this->assertSame(10.0, $response->get(0)->getHeight());
        $this->assertSame(1, $response->getTotalPages());
    }

    #[Test]
    public function counties_accepts_an_int_id_where_the_model_exposes_a_string(): void
    {
        $response = (new GetCounties())->setData($this->fixture('getCounties.drift'));

        $this->assertTrue($response->isOk());
        $this->assertSame('12', $response->getCounty('Cluj')->getId());
    }

    #[Test]
    public function cities_external_accepts_an_int_id_and_zero_per_page(): void
    {
        $response = (new GetCitiesExternal())->setData($this->fixture('getCitiesExternal.drift'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(CityExternal::class, $response->getAll()[1]);
        $this->assertSame('1', $response->getAll()[1]->getId());
        $this->assertSame(2, $response->getTotalPages());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
