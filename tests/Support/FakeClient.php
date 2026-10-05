<?php

namespace Fancourier\Tests\Support;

use Fancourier\Client;

/**
 * Test-only transport double for {@see Client}.
 *
 * Records the last call and returns a canned response (or a failure), so
 * request objects can be exercised offline. No network is ever touched.
 */
final class FakeClient extends Client
{
    /** @var string */
    private $response = '{}';

    /** @var bool */
    private $failure = false;

    /** @var string */
    private $error = '';

    /** @var string One of: '', 'get', 'post', 'post_json', 'post_ma'. */
    public $lastCall = '';

    /** @var string */
    public $lastUrl = '';

    /** @var array<string, mixed> */
    public $lastData = [];

    /** @var bool */
    public $isPut = false;

    /** @var bool */
    public $isDelete = false;

    /** @var string|null */
    public $lastAuthorization = null;

    public function setResponse(string $response): self
    {
        $this->response = $response;
        $this->failure = false;

        return $this;
    }

    public function setFailure(string $error = 'transport failure'): self
    {
        $this->failure = true;
        $this->error = $error;

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function addHeader($name, $value)
    {
        if (strtolower((string) $name) === 'authorization') {
            $this->lastAuthorization = (string) $value;
        }

        return $this;
    }

    public function setPutRequest($enabled = false)
    {
        $this->isPut = (bool) $enabled;
        if ($enabled) {
            $this->isDelete = false;
        }

        return $this;
    }

    public function setDeleteRequest($enabled = false)
    {
        $this->isDelete = (bool) $enabled;
        if ($enabled) {
            $this->isPut = false;
        }

        return $this;
    }

    public function get(string $url)
    {
        $this->record('get', $url, []);

        return $this->reply();
    }

    public function post(string $url, array $data)
    {
        $this->record('post', $url, $data);

        return $this->reply();
    }

    public function postJson(string $url, array $data)
    {
        $this->record('post_json', $url, $data);

        return $this->reply();
    }

    public function postMultiArray(string $url, array $data)
    {
        $this->record('post_ma', $url, $data);

        return $this->reply();
    }

    private function record(string $call, string $url, array $data): void
    {
        $this->lastCall = $call;
        $this->lastUrl = $url;
        $this->lastData = $data;
    }

    /**
     * @return string|false
     */
    private function reply()
    {
        return $this->failure ? false : $this->response;
    }
}
