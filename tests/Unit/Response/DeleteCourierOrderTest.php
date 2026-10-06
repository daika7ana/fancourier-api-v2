<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\DeleteCourierOrder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DeleteCourierOrderTest extends TestCase
{
    #[Test]
    public function it_reports_success_as_true(): void
    {
        $response = (new DeleteCourierOrder())->setData($this->fixture('deleteCourierOrder.success'));

        $this->assertTrue($response->isOk());
        $this->assertTrue($response->getData());
    }

    #[Test]
    public function it_reports_an_api_failure(): void
    {
        $response = (new DeleteCourierOrder())->setData($this->fixture('deleteCourierOrder.failure'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Order not found', $response->getErrorMessage());
        $this->assertFalse($response->getData());
    }

    #[Test]
    public function it_falls_back_on_missing_optional_keys(): void
    {
        $response = (new DeleteCourierOrder())->setData($this->fixture('deleteCourierOrder.missing-keys'));

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('Missing status', $response->getErrorMessage());
        $this->assertFalse($response->getData());
    }
    private function fixture(string $name): string
    {
        return (string) file_get_contents(__DIR__ . '/../../fixtures/' . $name . '.json');
    }
}
