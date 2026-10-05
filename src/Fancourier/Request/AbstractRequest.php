<?php

namespace Fancourier\Request;

use Fancourier\Fancourier;
use Fancourier\Auth;
use Fancourier\Client;
use Fancourier\Response\Generic;

abstract class AbstractRequest implements RequestInterface
{
    const string TYPE_RECIPIENT = 'destinatar';
    const string TYPE_SENDER = 'expeditor';
    const string TYPE_OTHER = 'Altul';

	const string PUDO_FANBOX = 'fanbox';
	const string PUDO_PAYPOINT = 'paypoint';
	const string PUDO_OFFICE = 'office';

	/*
	* Documented code lists (additive, defect #24). Values are taken verbatim
	* from the FAN Courier API v2.0 spec (API_GAP_ANALYSIS.md §4) — do not guess.
	*/

	// AWB service options (spec §5.2 / API_GAP §4.4)
	const string OPTION_OPEN_ON_DELIVERY = 'A';
	const string OPTION_OPOD = 'B';
	const string OPTION_DROP_OFF_OFFICE = 'C';
	const string OPTION_PICKUP_OFFICE = 'D';
	const string OPTION_DROP_OFF_PAYPOINT = 'E';
	const string OPTION_DELIVERY_PAYPOINT = 'F';
	const string OPTION_SMS_BUSINESS = 'M';
	const string OPTION_PICKUP_PREALERT = 'O';
	const string OPTION_PREALERT = 'P';
	const string OPTION_SATURDAY_DELIVERY = 'S';
	const string OPTION_PICKUP_LOCKER = 'V';
	const string OPTION_DROP_OFF_LOCKER = 'W';
	const string OPTION_EPOD = 'X';
	const string OPTION_MPOS = 'Y';

	// Courier order types (spec §5.12 / API_GAP §4.5)
	const string ORDER_TYPE_STANDARD = 'Standard';
	const string ORDER_TYPE_EXPRESS_LOCO_1H = 'Express Loco 1h';
	const string ORDER_TYPE_EXPRESS_LOCO_2H = 'Express Loco 2h';
	const string ORDER_TYPE_EXPRESS_LOCO_4H = 'Express Loco 4h';
	const string ORDER_TYPE_EXPRESS_LOCO_6H = 'Express Loco 6h';

	// Service types (spec §5.1 / API_GAP §4.6)
	const string SERVICE_STANDARD = 'Standard';
	const string SERVICE_REDCODE = 'RedCode';
	const string SERVICE_CASH_ON_DELIVERY = 'Cont Colector';
	const string SERVICE_EXPRESS_LOCO_2H = 'Express Loco 2H';
	const string SERVICE_EXPRESS_LOCO_4H = 'Express Loco 4H';
	const string SERVICE_EXPRESS_LOCO_6H = 'Express Loco 6H';
	const string SERVICE_EXPORT = 'Export';
	const string SERVICE_REDCODE_CASH_ON_DELIVERY = 'Red code-Cont Colector';
	const string SERVICE_EXPRESS_LOCO_2H_CASH_ON_DELIVERY = 'Express Loco 2H-Cont Colector';
	const string SERVICE_EXPRESS_LOCO_4H_CASH_ON_DELIVERY = 'Express Loco 4H-Cont Colector';
	const string SERVICE_EXPRESS_LOCO_6H_CASH_ON_DELIVERY = 'Express Loco 6H-Cont Colector';
	const string SERVICE_EXPRESS_LOCO_1H = 'Express Loco 1H';
	const string SERVICE_EXPRESS_LOCO_1H_CASH_ON_DELIVERY = 'Express Loco 1H-Cont Colector';
	const string SERVICE_EXPORT_CASH_ON_DELIVERY = 'Export-Cont Colector';
	const string SERVICE_COLLECT_POINT = 'CollectPoint';
	const string SERVICE_COLLECT_POINT_CASH_ON_DELIVERY = 'CollectPoint Cont Colector';
	const string SERVICE_WHITE_GOODS = 'Produse Albe';
	const string SERVICE_WHITE_GOODS_CASH_ON_DELIVERY = 'Produse Albe-Cont Colector';
	const string SERVICE_FREIGHT = 'Transport Marfa';
	const string SERVICE_FREIGHT_CASH_ON_DELIVERY = 'Transport Marfa-Cont Colector';
	const string SERVICE_FREIGHT_WHITE_GOODS = 'Transport Marfa Produse Albe';
	const string SERVICE_FREIGHT_WHITE_GOODS_CASH_ON_DELIVERY = 'Transport Marfa Produse Albe-Cont Colector';
	const string SERVICE_FANBOX = 'FANbox';
	const string SERVICE_FANBOX_CASH_ON_DELIVERY = 'FANbox Cont Colector';

	// AWB event codes (spec §5.10 / API_GAP §4.7)
	const string AWB_EVENT_C0 = 'C0';
	const string AWB_EVENT_C1 = 'C1';
	const string AWB_EVENT_H0 = 'H0';
	const string AWB_EVENT_H1 = 'H1';
	const string AWB_EVENT_H2 = 'H2';
	const string AWB_EVENT_H3 = 'H3';
	const string AWB_EVENT_H4 = 'H4';
	const string AWB_EVENT_H10 = 'H10';
	const string AWB_EVENT_H11 = 'H11';
	const string AWB_EVENT_H12 = 'H12';
	const string AWB_EVENT_H13 = 'H13';
	const string AWB_EVENT_H15 = 'H15';
	const string AWB_EVENT_H17 = 'H17';
	const string AWB_EVENT_S1 = 'S1';
	const string AWB_EVENT_S2 = 'S2';
	const string AWB_EVENT_S3 = 'S3';
	const string AWB_EVENT_S4 = 'S4';
	const string AWB_EVENT_S5 = 'S5';
	const string AWB_EVENT_S6 = 'S6';
	const string AWB_EVENT_S7 = 'S7';
	const string AWB_EVENT_S8 = 'S8';
	const string AWB_EVENT_S9 = 'S9';
	const string AWB_EVENT_S10 = 'S10';
	const string AWB_EVENT_S11 = 'S11';
	const string AWB_EVENT_S12 = 'S12';
	const string AWB_EVENT_S14 = 'S14';
	const string AWB_EVENT_S15 = 'S15';
	const string AWB_EVENT_S16 = 'S16';
	const string AWB_EVENT_S19 = 'S19';
	const string AWB_EVENT_S20 = 'S20';
	const string AWB_EVENT_S21 = 'S21';
	const string AWB_EVENT_S22 = 'S22';
	const string AWB_EVENT_S24 = 'S24';
	const string AWB_EVENT_S25 = 'S25';
	const string AWB_EVENT_S27 = 'S27';
	const string AWB_EVENT_S28 = 'S28';
	const string AWB_EVENT_S30 = 'S30';
	const string AWB_EVENT_S33 = 'S33';
	const string AWB_EVENT_S35 = 'S35';
	const string AWB_EVENT_S37 = 'S37';
	const string AWB_EVENT_S38 = 'S38';
	const string AWB_EVENT_S42 = 'S42';
	const string AWB_EVENT_S43 = 'S43';
	const string AWB_EVENT_S46 = 'S46';
	const string AWB_EVENT_S47 = 'S47';
	const string AWB_EVENT_S49 = 'S49';
	const string AWB_EVENT_S50 = 'S50';

	// Courier order event codes (spec §5.11 / API_GAP §4.8)
	const int ORDER_EVENT_PENDING = 0;
	const int ORDER_EVENT_PLACED = 1;
	const int ORDER_EVENT_PICKED_UP = 2;
	const int ORDER_EVENT_NOT_PICKED_UP = 3;
	const int ORDER_EVENT_CANCELLED = 4;
	const int ORDER_EVENT_POSTPONED = 5;
	const int ORDER_EVENT_SENDER_NOT_FOUND = 8;
	const int ORDER_EVENT_PICKED_UP_BORDEROU = 12;
	const int ORDER_EVENT_CANCELLATION_IN_PROGRESS = 99;
	
	
	protected $gateway;
	protected $method;

    /** @var Auth */
    protected $auth;

    /** @var Client */
    protected $client;

    protected $clientOverrides = [
        'verify' => false,
        'timeout' => false
    ];

    /** @var Generic */
    protected $response;

    public function __construct()
    {
        $this->client = new Client();
        $this->response = new Generic();
    }

    #[\Override]
    public function authenticate(Auth $auth)
    {
        $this->auth = $auth;
        return $this;
    }

    #[\Override]
    public function setVerify($verifyHost = true, $verifyPeer = true)
    {
        if ($this->clientOverrides['verify'] !== true) {
            $this->client->setVerify($verifyHost, $verifyPeer);
            $this->clientOverrides['verify'] = true;
        }

        return $this;
    }

    #[\Override]
    public function setTimeout($conTimeout = 3, $timeout = 6)
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
    public function send()
    {
        if (empty($this->gateway)) {
            throw new \DomainException("No request gateway implemented");
        }
		
        if (empty($this->method)) {
            throw new \DomainException("No request method implemented");
        }

		$data = $this->pack();

		// #27: remember whether the cached token was already stale before this
		// call, so a later transport failure can trigger one refresh + retry.
		$tokenWasExpired = $this->auth->isTokenExpired();

		$token = $this->auth->getToken();
		$this->assertUsableToken($token);

		// add authorization token
		$this->client->addHeader('Authorization', 'Bearer '.$token);

		$responseString = $this->dispatch($data);

		// #27 refresh + retry exactly once: a transport failure against a stale
		// token may just mean the bearer is no longer accepted. The single `if`
		// (no loop) guarantees at most one retry.
		// ponytail: the API documents no expiry error body (API_GAP_ANALYSIS §6.4),
		// so expiry is approximated by a transport failure plus a stale local
		// token. Upgrade path: parse the API's expiry signature once documented
		// and retry on that signal instead.
		if (false === $responseString && ($tokenWasExpired || $this->auth->isTokenExpired())) {
			$token = $this->auth->getToken(true);
			$this->assertUsableToken($token);
			$this->client->addHeader('Authorization', 'Bearer '.$token);
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
     * Refuse to perform an unauthenticated request when Auth cannot provide a
     * token (defect #26): without this guard send() would emit
     * 'Bearer ' . false and return an opaque API error.
     *
     * @param string|false $token
     */
    private function assertUsableToken($token): void
    {
        if (false === $token || $token === '') {
            $message = 'Authentication failed: no bearer token';
            $authMessage = $this->auth->getTokenMessage();
            if (is_string($authMessage) && $authMessage !== '') {
                $message .= ': '.$authMessage;
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
    private function dispatch(array $data)
    {
		if ($this->method == 'GET')
			{
			$get_params = http_build_query($data, '', '&');
			return $this->client->get(Fancourier::API_URL . $this->gateway . '?' .$get_params);
			}
		else
		if ($this->method == 'POST')
			{
			return $this->client->postJson(Fancourier::API_URL . $this->gateway, $data);
			}
		else
		if ($this->method == 'PUT')
			{
			$get_params = http_build_query($data, '', '&');
			return $this->client->setPutRequest(true)->get(Fancourier::API_URL . $this->gateway . '?' .$get_params);
			}
		else
		if ($this->method == 'POSTPUT')
			{
			return $this->client->setPutRequest(true)->postMultiArray(Fancourier::API_URL . $this->gateway, $data);
			}
		else
		if ($this->method == 'DELETE')
			{
			$get_params = http_build_query($data, '', '&');
			return $this->client->setDeleteRequest(true)->get(Fancourier::API_URL . $this->gateway . '?' .$get_params);
			}
		else
		if ($this->method == 'POSTDELETE')
			{
			return $this->client->setDeleteRequest(true)->postMultiArray(Fancourier::API_URL . $this->gateway, $data);
			}
		else
			{
			throw new \DomainException("Unsupported request method: ".$this->method);
			}
    }

}
