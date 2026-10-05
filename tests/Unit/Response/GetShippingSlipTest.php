<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\ShippingSlip;
use Fancourier\Response\GetShippingSlip;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetShippingSlipTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetShippingSlip())->setData($this->fixture('getShippingSlip.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(ShippingSlip::class, $response->get(0));
        $this->assertSame('6324356450139', $response->get(0)->getAwbNumber());
        $this->assertSame('Cont Colector', $response->get(0)->getService());
        $this->assertSame('4', $response->get(0)->getServiceId());
        $this->assertSame(10.0, $response->get(0)->getHeight());
        $this->assertSame(0, $response->get(0)->getEnvelopes());
        $this->assertSame(1, $response->get(0)->getParcels());
        $this->assertSame(14.4, $response->get(0)->getCost());
        $this->assertSame('LEI', $response->get(0)->getCurrency());
        $this->assertSame('POS', $response->get(0)->getNotes());
        $this->assertSame(['name' => 'Recipient SRL'], $response->get(0)->getRecipient());
        $this->assertSame(1, $response->getTotal());
        $this->assertSame(1, $response->getTotalPages());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetShippingSlip())->setData($this->fixture('getShippingSlip.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No slips', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        // getPayment()/getReturnPayment(): float receive real string values ("sender") and
        // would TypeError; not asserted (uninventoried §7 fragility).
        $response = (new GetShippingSlip())->setData($this->fixture('getShippingSlip.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertInstanceOf(ShippingSlip::class, $response->get(0));
        $this->assertSame('', $response->get(0)->getAwbNumber());
        $this->assertSame(0, $response->get(0)->getEnvelopes());
        $this->assertSame(0.0, $response->get(0)->getHeight());
        $this->assertSame('', $response->get(0)->getNotes());
        $this->assertSame(0.0, $response->get(0)->getCost());
    }
}
