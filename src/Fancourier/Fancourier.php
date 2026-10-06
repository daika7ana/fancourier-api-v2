<?php

declare(strict_types=1);

namespace Fancourier;

use Fancourier\Request\RequestInterface;
use Fancourier\Request\CreateAwb;
use Fancourier\Request\CreateAwbExternal;
use Fancourier\Request\CreateAwbBankAccount;
use Fancourier\Request\DeleteAwb;
use Fancourier\Request\GetServices;
use Fancourier\Request\GetServiceOptions;
use Fancourier\Request\GetCountries;
use Fancourier\Request\GetCounties;
use Fancourier\Request\GetCountiesExternal;
use Fancourier\Request\GetCities;
use Fancourier\Request\GetCitiesExternal;
use Fancourier\Request\GetStreets;
use Fancourier\Request\GetCosts;
use Fancourier\Request\GetCostsExternal;
use Fancourier\Request\GetPudo;
use Fancourier\Request\PrintAwb;
use Fancourier\Request\CreateCourierOrder;
use Fancourier\Request\DeleteCourierOrder;
use Fancourier\Request\GetShippingSlip;
use Fancourier\Request\GetAwbEvents;
use Fancourier\Request\TrackAwb;
use Fancourier\Request\GetBankTransfers;
use Fancourier\Request\GetAwbConfirmations;
use Fancourier\Request\GetCourierOrders;
use Fancourier\Request\GetCourierOrderEvents;
use Fancourier\Request\TrackCourierOrder;
use Fancourier\Request\GetBranches;
use Psr\Log\LoggerInterface;

class Fancourier
{
    public const string API_URL = 'https://api.fancourier.ro/';

    protected Auth $auth;

    protected bool $verifyHost = true;
    protected bool $verifyPeer = true;

    protected int $conTimeout = 3;
    protected int $timeout = 6;

    protected string $baseUrl = self::API_URL;

    /** Optional shared transport; null means each request keeps its own. */
    protected ?Client $client = null;

    protected ?LoggerInterface $logger = null;
    protected ?RetryPolicy $retryPolicy = null;

    public function __construct(string $clientId, string $username, string $password, string $bearer_token = '')
    {
        $this->auth = new Auth($clientId, $username, $password, $bearer_token);
    }

    /**
     * Normalize a base URL to trailing-slash form; an empty string yields the
     * given default.
     *
     * @internal
     */
    public static function normalizeBaseUrl(string $baseUrl, string $default = ''): string
    {
        return $baseUrl === '' ? $default : rtrim($baseUrl, '/') . '/';
    }

    /**
     * Point the client at another base URL (sandbox, proxy, mock server).
     */
    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = self::normalizeBaseUrl($baseUrl, self::API_URL);
        $this->auth->setBaseUrl($this->baseUrl);

        return $this;
    }

    /**
     * Replace the transport for every request sent through this facade.
     * Useful to inject a fake/custom HTTP client (e.g. in tests).
     */
    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Share a PSR-16 cache with the authentication layer so bearer tokens
     * survive across processes/requests.
     */
    public function setTokenCache(\Psr\SimpleCache\CacheInterface $cache, string $key = 'fancourier.token'): static
    {
        $this->auth->setTokenCache($cache, $key);

        return $this;
    }

    /**
     * Attach a PSR-3 logger for request and token-lifecycle records. Tokens,
     * passwords and request bodies are never logged.
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;
        $this->auth->setLogger($logger);

        return $this;
    }

    /**
     * Opt in to retry/backoff for transient failures. `null` disables retries
     * (the default). See {@see RetryPolicy} for the non-idempotent POST guard.
     */
    public function setRetryPolicy(?RetryPolicy $policy): static
    {
        $this->retryPolicy = $policy;

        return $this;
    }

    /**
     * Use this if you need to skip host/peer verification
     * @param bool $verifyHost
     * @param bool $verifyPeer
     */
    public function setVerify(bool $verifyHost = true, bool $verifyPeer = true): static
    {
        $this->verifyHost = $verifyHost;
        $this->verifyPeer = $verifyPeer;
        $this->auth->setVerify($verifyHost, $verifyPeer);

        return $this;
    }

    /**
     * Use this if you need to set a custom request timeout
     * @param int $conTimeout
     * @param int $timeout
     */
    public function setTimeout(int $conTimeout = 3, int $timeout = 6): static
    {
        $this->conTimeout = $conTimeout;
        $this->timeout = $timeout;
        $this->auth->setTimeout($conTimeout, $timeout);

        return $this;
    }

    /**
     * @param CreateAwb $request
     * @return \Fancourier\Response\CreateAwb
     */
    public function createAwb(CreateAwb $request): Response\CreateAwb
    {
        /** @var Response\CreateAwb $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param CreateAwbExternal $request
     * @return \Fancourier\Response\CreateAwbExternal
     */
    public function createAwbExternal(CreateAwbExternal $request): Response\CreateAwbExternal
    {
        /** @var Response\CreateAwbExternal $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param CreateAwbBankAccount $request
     * @return \Fancourier\Response\CreateAwbBankAccount
     */
    public function createAwbBankAccount(CreateAwbBankAccount $request): Response\CreateAwbBankAccount
    {
        /** @var Response\CreateAwbBankAccount $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param PrintAwb $request
     * @return \Fancourier\Response\PrintAwb
     */
    public function printAwb(PrintAwb $request): Response\PrintAwb
    {
        /** @var Response\PrintAwb $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param DeleteAwb $request
     * @return \Fancourier\Response\DeleteAwb
     */
    public function deleteAwb(DeleteAwb $request): Response\DeleteAwb
    {
        /** @var Response\DeleteAwb $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetCosts $request
     * @return \Fancourier\Response\GetCosts
     */
    public function getCosts(GetCosts $request): Response\GetCosts
    {
        /** @var Response\GetCosts $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetCostsExternal $request
     * @return \Fancourier\Response\GetCostsExternal
     */
    public function getCostsExternal(GetCostsExternal $request): Response\GetCostsExternal
    {
        /** @var Response\GetCostsExternal $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @return \Fancourier\Response\GetServices
     */
    public function getServices(): Response\GetServices
    {
        /** @var Response\GetServices $response */
        $response = $this->send(new GetServices());

        return $response;
    }

    /**
     * @param GetServiceOptions $request
     * @return \Fancourier\Response\GetServiceOptions
     */
    public function getServiceOptions(GetServiceOptions $request): Response\GetServiceOptions
    {
        /** @var Response\GetServiceOptions $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @return \Fancourier\Response\GetCounties
     */
    public function getCounties(): Response\GetCounties
    {
        /** @var Response\GetCounties $response */
        $response = $this->send(new GetCounties());

        return $response;
    }

    /**
     * @param GetCities $request
     * @return \Fancourier\Response\GetCities
     */
    public function getCities(GetCities $request): Response\GetCities
    {
        /** @var Response\GetCities $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetStreets $request
     * @return \Fancourier\Response\GetStreets
     */
    public function getStreets(GetStreets $request): Response\GetStreets
    {
        /** @var Response\GetStreets $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetPudo $request
     * @return \Fancourier\Response\GetPudo
     */
    public function getPudo(GetPudo $request): Response\GetPudo
    {
        /** @var Response\GetPudo $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetShippingSlip $request
     * @return \Fancourier\Response\GetShippingSlip
     */
    public function getShippingSlip(GetShippingSlip $request): Response\GetShippingSlip
    {
        /** @var Response\GetShippingSlip $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetAwbEvents $request
     * @return \Fancourier\Response\GetAwbEvents
     */
    public function getAwbEvents(GetAwbEvents $request): Response\GetAwbEvents
    {
        /** @var Response\GetAwbEvents $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param TrackAwb $request
     * @return \Fancourier\Response\TrackAwb
     */
    public function trackAwb(TrackAwb $request): Response\TrackAwb
    {
        /** @var Response\TrackAwb $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @return \Fancourier\Response\GetCountries
     */
    public function getCountries(): Response\GetCountries
    {
        /** @var Response\GetCountries $response */
        $response = $this->send(new GetCountries());

        return $response;
    }

    /**
     * @param GetCountiesExternal $request
     * @return \Fancourier\Response\GetCountiesExternal
     */
    public function getCountiesExternal(GetCountiesExternal $request): Response\GetCountiesExternal
    {
        /** @var Response\GetCountiesExternal $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetCitiesExternal $request
     * @return \Fancourier\Response\GetCitiesExternal
     */
    public function getCitiesExternal(GetCitiesExternal $request): Response\GetCitiesExternal
    {
        /** @var Response\GetCitiesExternal $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param CreateCourierOrder $request
     * @return \Fancourier\Response\CreateCourierOrder
     */
    public function createCourierOrder(CreateCourierOrder $request): Response\CreateCourierOrder
    {
        /** @var Response\CreateCourierOrder $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param DeleteCourierOrder $request
     * @return \Fancourier\Response\DeleteCourierOrder
     */
    public function deleteCourierOrder(DeleteCourierOrder $request): Response\DeleteCourierOrder
    {
        /** @var Response\DeleteCourierOrder $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetCourierOrders $request
     * @return \Fancourier\Response\GetCourierOrders
     */
    public function getCourierOrders(GetCourierOrders $request): Response\GetCourierOrders
    {
        /** @var Response\GetCourierOrders $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetCourierOrderEvents $request
     * @return \Fancourier\Response\GetCourierOrderEvents
     */
    public function getCourierOrderEvents(GetCourierOrderEvents $request): Response\GetCourierOrderEvents
    {
        /** @var Response\GetCourierOrderEvents $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param TrackCourierOrder $request
     * @return \Fancourier\Response\TrackCourierOrder
     */
    public function trackCourierOrder(TrackCourierOrder $request): Response\TrackCourierOrder
    {
        /** @var Response\TrackCourierOrder $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetBankTransfers $request
     * @return \Fancourier\Response\GetBankTransfers
     */
    public function getBankTransfers(GetBankTransfers $request): Response\GetBankTransfers
    {
        /** @var Response\GetBankTransfers $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetBranches $request
     * @return \Fancourier\Response\GetBranches
     */
    public function getBranches(GetBranches $request): Response\GetBranches
    {
        /** @var Response\GetBranches $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param GetAwbConfirmations $request
     * @return \Fancourier\Response\GetAwbConfirmations
     */
    public function getAwbConfirmations(GetAwbConfirmations $request): Response\GetAwbConfirmations
    {
        /** @var Response\GetAwbConfirmations $response */
        $response = $this->send($request);

        return $response;
    }

    /**
     * @param bool $refresh Force token refresh even if token given in constructor
     * @return string|false
     */
    public function getToken(bool $refresh = false): string|false
    {
        return $this->auth->getToken($refresh);
    }

    /**
     * @return string
     */
    public function getTokenExpiresAt(): string
    {
        return $this->auth->getTokenExpiresAt();
    }

    /**
     * @return string
     */
    public function getTokenMessage(): string
    {
        return $this->auth->getTokenMessage();
    }

    /**
     * @param RequestInterface $request
     * @return \Fancourier\Response\ResponseInterface
     */
    protected function send(RequestInterface $request): Response\ResponseInterface
    {
        $request = $request->authenticate($this->auth);

        if ($this->client !== null) {
            $request = $request->setClient($this->client);
        }

        if ($this->logger !== null) {
            $request = $request->setLogger($this->logger);
        }

        return $request
            ->setRetryPolicy($this->retryPolicy)
            ->setBaseUrl($this->baseUrl)
            ->setVerify($this->verifyHost, $this->verifyPeer)
            ->setTimeout($this->conTimeout, $this->timeout)
            ->send();
    }
}
