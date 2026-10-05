<?php

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Objects\AwbIntern;
use Fancourier\Response\CreateAwb;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CreateAwbTest extends TestCase
{
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }

    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = new CreateAwb();
        $response->setAwbList([new AwbIntern()]);
        $response->setData($this->fixture('createAwb.success'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertInstanceOf(AwbIntern::class, $response->getAll()[0]);
        $this->assertSame(2347300120337, $response->getAll()[0]->getAwb());
        $this->assertFalse($response->getAll()[0]->hasErrors());
        $this->assertSame('B', $response->getAll()[0]->getDetails()['letter']);
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new CreateAwb())->setData($this->fixture('createAwb.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Invalid AWB data', $response->getErrorMessage());
        $this->assertSame([], $response->getAll());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = new CreateAwb();
        $response->setAwbList([new AwbIntern()]);
        $response->setData($this->fixture('createAwb.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertCount(1, $response->getAll());
        $this->assertFalse($response->getAll()[0]->hasErrors());
        $this->assertSame('', $response->getAll()[0]->getDetails()['tariff']);
        $this->assertSame('', $response->getAll()[0]->getDetails()['office']);
    }
}
