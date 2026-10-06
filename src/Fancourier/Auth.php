<?php

declare(strict_types=1);

namespace Fancourier;

use Fancourier\Fancourier;
use Fancourier\Client;

class Auth {
	private int|string $clientId;
	private string $username;
	private string $password;

	private string $btoken = '';
    private string $btoken_expires_at = '';
	private string $btoken_message = '';

	protected bool $verifyHost = true;
	protected bool $verifyPeer = true;

	protected int $timeout = 6;
	protected int $con_timeout = 3;

	protected string $gateway = 'login';

	public function __construct(int|string $clientId, string $username, string $password, string $token = '')
		{
		$this->clientId = $clientId;
		$this->username = $username;
		$this->password = $password;

		$this->btoken   = $token;
		// if the token is empty, it will automatically retrieve it when performing a request
		}

	public function getClientId(): int|string	{	return $this->clientId;	}

	public function getToken(bool $refresh = false): string|false
		{
		$this->btoken_message = '';
		if ($refresh || ($this->btoken == '') || $this->isTokenExpired())
			{
			try {
				$this->retrieve_token();
			}
			catch (\Exception $e)
				{
				$this->btoken_message = $e->getMessage();
				return false;
				}
			}

		return $this->btoken;
		}

	/**
	 * Whether the stored token's expiry timestamp is in the past.
	 *
	 * The API returns `expiresAt` as 'Y-m-d H:i:s' with an unspecified timezone,
	 * so the comparison uses the process timezone. A missing or unparsable
	 * expiry is treated as "not expired" (conservative: keep using the cached
	 * token instead of forcing a refresh), see defect #27 / API_GAP_ANALYSIS §6.4.
	 */
	public function isTokenExpired(): bool
		{
		if ($this->btoken_expires_at === '')
			{
			return false;
			}

		$expiresAt = strtotime($this->btoken_expires_at);
		if ($expiresAt === false)
			{
			return false;
			}

		return $expiresAt <= time();
		}

	public function getTokenMessage(): string
		{
		return $this->btoken_message;
		}

	public function getTokenExpiresAt(): string
	{
		return $this->btoken_expires_at; // Date format: Y-m-d H:i:s (unknown timezone)
	}

	// Widened private -> protected so the token path is unit-testable without
	// network (a test subclass can override the retrieval).
	protected function retrieve_token(): bool
		{
		$client = new Client();
		$client->setVerify($this->verifyHost, $this->verifyPeer);
		$client->setTimeout($this->con_timeout, $this->timeout);

		$url = Fancourier::API_URL.$this->gateway;

		$data = [
			'username' => $this->username,
			'password' => $this->password
			];
		$response = $client->post($url, $data);
        // {"status":"success","data":{"token":"48944740|EJU0MgzeWY4y1zy9JpQg3cu3cDiqoVg1ZXsIrqLQ","expiresAt":"2024-06-11 07:23:53"}}

		if ($response === false)
			{
			throw new \Exception("Server error: ".$client->getError());
			}

		$response_json = json_decode($response, true);

		if (json_last_error() === JSON_ERROR_NONE)
			{
			if ($response_json['status'] == 'success')
				{
				$this->btoken = $response_json['data']['token'];
				$this->btoken_expires_at = $response_json['data']['expiresAt'];
				}
			else
				{
				throw new \Exception("Login failed: ".$response_json['message']);
				}
			}
		else
			{
			throw new \Exception("Server error: invalid response: ".$response);
			}

		unset($client);

		return ($this->btoken !== '');
		}


    public function setVerify(bool $host = true, bool $peer = true): static
    {
        $this->verifyHost = $host;
        $this->verifyPeer = $peer;
		return $this;
    }

	public function setTimeout(int $con_timeout = 3, int $timeout = 6): static
	{
		$this->con_timeout = $con_timeout;
		$this->timeout = $timeout;
		return $this;
	}
}
