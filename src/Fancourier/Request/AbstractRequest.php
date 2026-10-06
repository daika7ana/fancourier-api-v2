<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Fancourier;
use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Response\Generic;
use Fancourier\Response\ResponseInterface;

abstract class AbstractRequest implements RequestInterface
{
    protected string $gateway = '';
    protected string $method = '';

    protected ?Auth $auth = null;

    protected Client $client;

    /** @var array{verify: bool, timeout: bool} */
    protected array $clientOverrides = [
        'verify' => false,
        'timeout' => false,
    ];

    protected Generic $response;

    public function __construct()
    {
        $this->client = new Client();
        $this->response = new Generic();
    }

    #[\Override]
    public function authenticate(Auth $auth): static
    {
        $this->auth = $auth;

        return $this;
    }

    #[\Override]
    public function setVerify(bool $verifyHost = true, bool $verifyPeer = true): static
    {
        if ($this->clientOverrides['verify'] !== true) {
            $this->client->setVerify($verifyHost, $verifyPeer);
            $this->clientOverrides['verify'] = true;
        }

        return $this;
    }

    #[\Override]
    public function setTimeout(int $conTimeout = 3, int $timeout = 6): static
    {
        if ($this->clientOverrides['timeout'] !== true) {
            $this->client->setTimeout($conTimeout, $timeout);
            $this->clientOverrides['timeout'] = true;
        }

        return $this;
    }

    /**
     * @return Generic
     */
    #[\Override]
    public function send(): ResponseInterface
    {
        if ($this->auth === null) {
            throw new \RuntimeException('No Auth instance set; call authenticate() before send()');
        }

        $auth = $this->auth;

        if (empty($this->gateway)) {
            throw new \DomainException("No request gateway implemented");
        }

        if (empty($this->method)) {
            throw new \DomainException("No request method implemented");
        }

        $data = $this->pack();

        // #27: remember whether the cached token was already stale before this
        // call, so a later transport failure can trigger one refresh + retry.
        $tokenWasExpired = $auth->isTokenExpired();

        $token = $auth->getToken();
        $this->assertUsableToken($token);

        // add authorization token
        $this->client->addHeader('Authorization', 'Bearer ' . $token);

        $responseString = $this->dispatch($data);

        // #27 refresh + retry exactly once: a transport failure against a stale
        // token may just mean the bearer is no longer accepted. The single `if`
        // (no loop) guarantees at most one retry.
        // ponytail: the API documents no expiry error body,
        // so expiry is approximated by a transport failure plus a stale local
        // token. Upgrade path: parse the API's expiry signature once documented
        // and retry on that signal instead.
        if (false === $responseString && ($tokenWasExpired || $auth->isTokenExpired())) {
            $token = $auth->getToken(true);
            $this->assertUsableToken($token);
            $this->client->addHeader('Authorization', 'Bearer ' . $token);
            $responseString = $this->dispatch($data);
        }

        if (false === $responseString) {
            $this->response->setErrorCode(-1)->setErrorMessage($this->client->getError());
        } else {
            $this->response->setData($responseString);
        }

        return $this->response;
    }

    /**
     * Return the configured Auth instance, or fail loudly when a request is
     * built/sent before authenticate() was called.
     */
    protected function auth(): Auth
    {
        if ($this->auth === null) {
            throw new \RuntimeException('No Auth instance set; call authenticate() first');
        }

        return $this->auth;
    }

    /**
     * Refuse to perform an unauthenticated request when Auth cannot provide a
     * token (defect #26): without this guard send() would emit
     * 'Bearer ' . false and return an opaque API error.
     *
     * @param string|false $token
     */
    private function assertUsableToken(string|false $token): void
    {
        if (false === $token || $token === '') {
            $message = 'Authentication failed: no bearer token';
            $authMessage = $this->auth()->getTokenMessage();
            if ($authMessage !== '') {
                $message .= ': ' . $authMessage;
            }

            throw new \RuntimeException($message);
        }
    }

    /**
     * Send the packed payload over the transport selected by $this->method.
     *
     * @param array<string, mixed> $data
     * @return string|false
     */
    private function dispatch(array $data): string|false
    {
        if ($this->method == 'GET') {
            $get_params = http_build_query($data, '', '&');

            return $this->client->get(Fancourier::API_URL . $this->gateway . '?' . $get_params);
        } elseif ($this->method == 'POST') {
            return $this->client->postJson(Fancourier::API_URL . $this->gateway, $data);
        } elseif ($this->method == 'PUT') {
            $get_params = http_build_query($data, '', '&');

            return $this->client->setPutRequest(true)->get(Fancourier::API_URL . $this->gateway . '?' . $get_params);
        } elseif ($this->method == 'POSTPUT') {
            return $this->client->setPutRequest(true)->postMultiArray(Fancourier::API_URL . $this->gateway, $data);
        } elseif ($this->method == 'DELETE') {
            $get_params = http_build_query($data, '', '&');

            return $this->client->setDeleteRequest(true)->get(Fancourier::API_URL . $this->gateway . '?' . $get_params);
        } elseif ($this->method == 'POSTDELETE') {
            return $this->client->setDeleteRequest(true)->postMultiArray(Fancourier::API_URL . $this->gateway, $data);
        } else {
            throw new \DomainException("Unsupported request method: " . $this->method);
        }
    }

}
