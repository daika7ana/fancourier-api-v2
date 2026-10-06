<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\ServiceOption;
use Fancourier\Response\GetServiceOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetServiceOptionsTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new GetServiceOptions())->setData($this->fixture('getServiceOptions.success'));

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->hasOption('R'));
        $this->assertInstanceOf(ServiceOption::class, $response->getOption('R'));
        $this->assertSame('Ramburs', $response->getOption('R')->getName());
        $this->assertFalse($response->hasOption('X'));
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new GetServiceOptions())->setData($this->fixture('getServiceOptions.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('No options', $response->getErrorMessage());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new GetServiceOptions())->setData($this->fixture('getServiceOptions.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertSame([], $response->getAll());
        $this->assertFalse($response->hasOption('R'));
        $this->assertFalse($response->getOption('R'));
    }
}
