<?php

namespace Fancourier\Tests\Unit;

use Fancourier\Client;
use Fancourier\Response\Generic;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Small hermetic smoke tests so the offline suite is never empty.
 */
class SmokeTest extends TestCase
{
    #[Test]
    public function it_builds_a_string_file_for_multipart_posts(): void
    {
        $file = Client::prepare_file_string('payload', 'payload.txt', 'text/plain');

        $this->assertInstanceOf(\CURLStringFile::class, $file);
        $this->assertSame('payload', $file->data);
        $this->assertSame('payload.txt', $file->postname);
        $this->assertSame('text/plain', $file->mime);
    }

    #[Test]
    public function a_fresh_generic_response_is_ok_and_has_no_data(): void
    {
        $response = new Generic();

        $this->assertTrue($response->isOk());
        $this->assertNull($response->getData());
    }

    #[Test]
    public function generic_response_reports_errors(): void
    {
        $response = (new Generic())->setErrorCode(-1)->setErrorMessage('boom');

        $this->assertFalse($response->isOk());
        $this->assertSame(-1, $response->getErrorCode());
        $this->assertSame('boom', $response->getErrorMessage());
    }
}
