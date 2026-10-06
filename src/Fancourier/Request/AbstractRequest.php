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
    public const string TYPE_RECIPIENT = 'destinatar';
    public const string TYPE_SENDER = 'expeditor';
    public const string TYPE_OTHER = 'Altul';

    public const string PUDO_FANBOX = 'fanbox';
    public const string PUDO_PAYPOINT = 'paypoint';
    public const string PUDO_OFFICE = 'office';

    /*
    * Documented code lists (additive, defect #24). Values are taken verbatim
    * from the FAN Courier API v2.0 spec — do not guess.
    */

    // AWB service options (spec §5.2)
    public const string OPTION_OPEN_ON_DELIVERY = 'A';
    public const string OPTION_OPOD = 'B';
    public const string OPTION_DROP_OFF_OFFICE = 'C';
    public const string OPTION_PICKUP_OFFICE = 'D';
    public const string OPTION_DROP_OFF_PAYPOINT = 'E';
    public const string OPTION_DELIVERY_PAYPOINT = 'F';
    public const string OPTION_SMS_BUSINESS = 'M';
    public const string OPTION_PICKUP_PREALERT = 'O';
    public const string OPTION_PREALERT = 'P';
    public const string OPTION_SATURDAY_DELIVERY = 'S';
    public const string OPTION_PICKUP_LOCKER = 'V';
    public const string OPTION_DROP_OFF_LOCKER = 'W';
    public const string OPTION_EPOD = 'X';
    public const string OPTION_MPOS = 'Y';

    // Courier order types (spec §5.12)
    public const string ORDER_TYPE_STANDARD = 'Standard';
    public const string ORDER_TYPE_EXPRESS_LOCO_1H = 'Express Loco 1h';
    public const string ORDER_TYPE_EXPRESS_LOCO_2H = 'Express Loco 2h';
    public const string ORDER_TYPE_EXPRESS_LOCO_4H = 'Express Loco 4h';
    public const string ORDER_TYPE_EXPRESS_LOCO_6H = 'Express Loco 6h';

    // Service types (spec §5.1)
    public const string SERVICE_STANDARD = 'Standard';
    public const string SERVICE_REDCODE = 'RedCode';
    public const string SERVICE_CASH_ON_DELIVERY = 'Cont Colector';
    public const string SERVICE_EXPRESS_LOCO_2H = 'Express Loco 2H';
    public const string SERVICE_EXPRESS_LOCO_4H = 'Express Loco 4H';
    public const string SERVICE_EXPRESS_LOCO_6H = 'Express Loco 6H';
    public const string SERVICE_EXPORT = 'Export';
    public const string SERVICE_REDCODE_CASH_ON_DELIVERY = 'Red code-Cont Colector';
    public const string SERVICE_EXPRESS_LOCO_2H_CASH_ON_DELIVERY = 'Express Loco 2H-Cont Colector';
    public const string SERVICE_EXPRESS_LOCO_4H_CASH_ON_DELIVERY = 'Express Loco 4H-Cont Colector';
    public const string SERVICE_EXPRESS_LOCO_6H_CASH_ON_DELIVERY = 'Express Loco 6H-Cont Colector';
    public const string SERVICE_EXPRESS_LOCO_1H = 'Express Loco 1H';
    public const string SERVICE_EXPRESS_LOCO_1H_CASH_ON_DELIVERY = 'Express Loco 1H-Cont Colector';
    public const string SERVICE_EXPORT_CASH_ON_DELIVERY = 'Export-Cont Colector';
    public const string SERVICE_COLLECT_POINT = 'CollectPoint';
    public const string SERVICE_COLLECT_POINT_CASH_ON_DELIVERY = 'CollectPoint Cont Colector';
    public const string SERVICE_WHITE_GOODS = 'Produse Albe';
    public const string SERVICE_WHITE_GOODS_CASH_ON_DELIVERY = 'Produse Albe-Cont Colector';
    public const string SERVICE_FREIGHT = 'Transport Marfa';
    public const string SERVICE_FREIGHT_CASH_ON_DELIVERY = 'Transport Marfa-Cont Colector';
    public const string SERVICE_FREIGHT_WHITE_GOODS = 'Transport Marfa Produse Albe';
    public const string SERVICE_FREIGHT_WHITE_GOODS_CASH_ON_DELIVERY = 'Transport Marfa Produse Albe-Cont Colector';
    public const string SERVICE_FANBOX = 'FANbox';
    public const string SERVICE_FANBOX_CASH_ON_DELIVERY = 'FANbox Cont Colector';

    // AWB event codes (spec §5.10)
    public const string AWB_EVENT_C0 = 'C0';
    public const string AWB_EVENT_C1 = 'C1';
    public const string AWB_EVENT_H0 = 'H0';
    public const string AWB_EVENT_H1 = 'H1';
    public const string AWB_EVENT_H2 = 'H2';
    public const string AWB_EVENT_H3 = 'H3';
    public const string AWB_EVENT_H4 = 'H4';
    public const string AWB_EVENT_H10 = 'H10';
    public const string AWB_EVENT_H11 = 'H11';
    public const string AWB_EVENT_H12 = 'H12';
    public const string AWB_EVENT_H13 = 'H13';
    public const string AWB_EVENT_H15 = 'H15';
    public const string AWB_EVENT_H17 = 'H17';
    public const string AWB_EVENT_S1 = 'S1';
    public const string AWB_EVENT_S2 = 'S2';
    public const string AWB_EVENT_S3 = 'S3';
    public const string AWB_EVENT_S4 = 'S4';
    public const string AWB_EVENT_S5 = 'S5';
    public const string AWB_EVENT_S6 = 'S6';
    public const string AWB_EVENT_S7 = 'S7';
    public const string AWB_EVENT_S8 = 'S8';
    public const string AWB_EVENT_S9 = 'S9';
    public const string AWB_EVENT_S10 = 'S10';
    public const string AWB_EVENT_S11 = 'S11';
    public const string AWB_EVENT_S12 = 'S12';
    public const string AWB_EVENT_S14 = 'S14';
    public const string AWB_EVENT_S15 = 'S15';
    public const string AWB_EVENT_S16 = 'S16';
    public const string AWB_EVENT_S19 = 'S19';
    public const string AWB_EVENT_S20 = 'S20';
    public const string AWB_EVENT_S21 = 'S21';
    public const string AWB_EVENT_S22 = 'S22';
    public const string AWB_EVENT_S24 = 'S24';
    public const string AWB_EVENT_S25 = 'S25';
    public const string AWB_EVENT_S27 = 'S27';
    public const string AWB_EVENT_S28 = 'S28';
    public const string AWB_EVENT_S30 = 'S30';
    public const string AWB_EVENT_S33 = 'S33';
    public const string AWB_EVENT_S35 = 'S35';
    public const string AWB_EVENT_S37 = 'S37';
    public const string AWB_EVENT_S38 = 'S38';
    public const string AWB_EVENT_S42 = 'S42';
    public const string AWB_EVENT_S43 = 'S43';
    public const string AWB_EVENT_S46 = 'S46';
    public const string AWB_EVENT_S47 = 'S47';
    public const string AWB_EVENT_S49 = 'S49';
    public const string AWB_EVENT_S50 = 'S50';

    // Courier order event codes (spec §5.11)
    public const int ORDER_EVENT_PENDING = 0;
    public const int ORDER_EVENT_PLACED = 1;
    public const int ORDER_EVENT_PICKED_UP = 2;
    public const int ORDER_EVENT_NOT_PICKED_UP = 3;
    public const int ORDER_EVENT_CANCELLED = 4;
    public const int ORDER_EVENT_POSTPONED = 5;
    public const int ORDER_EVENT_SENDER_NOT_FOUND = 8;
    public const int ORDER_EVENT_PICKED_UP_BORDEROU = 12;
    public const int ORDER_EVENT_CANCELLATION_IN_PROGRESS = 99;


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
