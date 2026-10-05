<?php

namespace Fancourier\Tests\Unit;

use Fancourier\Client;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Direct, hermetic exercise of the real cURL transport.
 *
 * `file://` URLs make cURL read a local file without touching the network, so a
 * zero-byte temp file reliably produces an empty-but-successful transfer body.
 */
final class ClientTest extends TestCase
{
    private const EMPTY_RESPONSE_ERROR = 'FAN Courier returned an empty response';

    /** @var list<string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->tempFiles = [];
    }

    private function tempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'fanclient_');
        if ($path === false) {
            self::fail('Could not create a temporary file');
        }

        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }

    #[Test]
    public function post_json_treats_an_empty_body_as_a_failure(): void
    {
        $client = new Client();

        $result = $client->postJson('file://'.$this->tempFile(''), ['a' => 1]);

        $this->assertFalse($result);
        $this->assertSame(self::EMPTY_RESPONSE_ERROR, $client->getError());
    }

    #[Test]
    public function post_treats_an_empty_body_as_a_failure(): void
    {
        $client = new Client();

        $result = $client->post('file://'.$this->tempFile(''), ['a' => 1]);

        $this->assertFalse($result);
        $this->assertSame(self::EMPTY_RESPONSE_ERROR, $client->getError());
    }

    #[Test]
    public function post_ma_treats_an_empty_body_as_a_failure(): void
    {
        $client = new Client();

        $result = $client->postMultiArray('file://'.$this->tempFile(''), ['a' => 1]);

        $this->assertFalse($result);
        $this->assertSame(self::EMPTY_RESPONSE_ERROR, $client->getError());
    }

    #[Test]
    public function a_non_empty_body_is_still_returned(): void
    {
        $client = new Client();

        $result = $client->postJson('file://'.$this->tempFile('{"ok":true}'), ['a' => 1]);

        $this->assertSame('{"ok":true}', $result);
        $this->assertSame('', $client->getError());
    }

    #[Test]
    public function a_transport_error_reports_the_curl_error(): void
    {
        $client = new Client();

        $result = $client->postJson('nosuchproto://example', ['a' => 1]);

        $this->assertFalse($result);
        $this->assertNotSame('', $client->getError());
        $this->assertNotSame(self::EMPTY_RESPONSE_ERROR, $client->getError());
    }
}
