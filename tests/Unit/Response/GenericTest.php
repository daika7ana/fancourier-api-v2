<?php

declare(strict_types=1);

namespace Fancourier\Tests\Unit\Response;

use Fancourier\Response\Generic;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GenericTest extends TestCase
{
    #[Test]
    public function it_is_ok_when_code_and_message_are_null(): void
    {
        $this->assertTrue((new Generic())->isOk());
    }

    /**
     * Regression: an error code of 0 is a real error, not an empty value.
     */
    #[Test]
    public function an_error_code_of_zero_is_not_ok(): void
    {
        $response = (new Generic())->setErrorCode(0);

        $this->assertFalse($response->isOk());
    }

    #[Test]
    public function a_set_error_message_is_not_ok(): void
    {
        $this->assertFalse((new Generic())->setErrorMessage('boom')->isOk());
    }

    #[Test]
    public function an_empty_error_message_is_still_ok(): void
    {
        $this->assertTrue((new Generic())->setErrorMessage('')->isOk());
    }

    #[Test]
    public function reset_clears_the_base_state(): void
    {
        $response = (new Generic())
            ->setErrorCode(42)
            ->setErrorMessage('boom')
            ->setData(['x' => 1])
            ->setHttpStatusCode(500);

        $this->assertSame($response, $response->reset());
        $this->assertNull($response->getErrorCode());
        $this->assertNull($response->getErrorMessage());
        $this->assertNull($response->getData());
        $this->assertNull($response->getHttpStatusCode());
        $this->assertTrue($response->isOk());
    }
}
