<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\CreateCourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CreateCourierOrderTest extends TestCase
{
    #[Test]
    public function it_parses_a_success_body(): void
    {
        $response = (new CreateCourierOrder())->setData($this->fixture('createCourierOrder.success'));

        $this->assertTrue($response->isOk());
        $this->assertSame('ORDER-1', $response->getId());
        $this->assertSame('ORDER-1', $response->getData());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new CreateCourierOrder())->setData($this->fixture('createCourierOrder.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Order rejected', $response->getErrorMessage());
    }

    /**
     * Defect #20 (UPGRADE_PLAN §7): getId() was missing; it exposes data.id.
     */
    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new CreateCourierOrder())->setData($this->fixture('createCourierOrder.missing-keys'));

        $this->assertTrue($response->isOk());
        $this->assertNull($response->getId());
        $this->assertNull($response->getData());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
