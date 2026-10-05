<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\Service;
use Fancourier\Response\GetServices;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetServicesTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetServices())->setData($this->fixture('getServices.success'));

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->hasService('Standard'));
        $this->assertInstanceOf(Service::class, $response->getService('Standard'));
        $this->assertSame('Standard service', $response->getService('Standard')->getDescription());
        $this->assertFalse($response->hasService('Unknown'));
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetServices())->setData($this->fixture('getServices.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No services', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetServices())->setData($this->fixture('getServices.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->hasService('Standard'));
    }
}
