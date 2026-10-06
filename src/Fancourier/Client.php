<?php

declare(strict_types=1);

namespace Fancourier;

class Client {
	private \CurlHandle|false|null $curl = null;

	private string $error		= '';

	private bool $is_put		= false;
	private bool $is_delete		= false;
	
	private bool $verify_host = true;
	private bool $verify_peer = true;

	private int $timeout = 6;
	private int $con_timeout = 3;

	private string $useragent	= 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0';

	/** @var array<string, string> */
	private array $headers	= [];	// custom headers

	public function __construct(?string $useragent = null)
		{
		if (!is_null($useragent) && ($useragent != '') )
			{
			$this->useragent = $useragent;
			}
		}

	/*
	* Init curl and set common options. Called by get/post functions
	*/
	private function init(): void
		{
		$this->curl = curl_init();
		if ($this->curl === false)
			{
			throw new \RuntimeException('Failed to initialise cURL');
			}
		// init default data
		curl_setopt($this->curl, CURLOPT_USERAGENT, $this->useragent);

		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->con_timeout);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);

		if (!$this->verify_host)
			{
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
			}
		if (!$this->verify_peer)
			{
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			}

		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

		$this->set_custom_headers();
		}

	private function set_custom_headers(): void
		{
		if (count($this->headers) > 0)
			{
			$curl_headers = [];
			foreach ($this->headers as $hname=>$hvalue)
				{
				$curl_headers[] = $hname.":".$hvalue;
				}

			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $curl_headers);
			}
		}

	private function close(): void
		{
		curl_close($this->curl);
		// reset put/delete requests
		$this->is_put = false;
		$this->is_delete = false;
		}

	public function get(string $url): string|false
		{
		//echo '<div style="font-family:monospace; padding: 5px; border: 1px solid red; margin: 5px">'.$url.'</div>';
		$this->init();
		
		curl_setopt($this->curl, CURLOPT_URL, $url);
		//curl_setopt($this->curl, CURLOPT_HEADER, 0);
		curl_setopt($this->curl, CURLOPT_POST, false);
		if ($this->is_put)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
			}
		elseif ($this->is_delete)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			}

		$response = curl_exec($this->curl);

		if (!curl_error($this->curl) && $response)
			{
			$this->close();
			return $response;
			}

		$this->set_error(curl_error($this->curl));
		$this->close();
		return false;
		}

	/*
	Use this static function to prepare a file for posting it using cURL
	Pass the resulting object of this function inside the $data array of the post function
	*/
	public static function prepare_file(string $file): \CURLFile
		{
		$mime = mime_content_type($file) ?: 'application/octet-stream';
		$info = pathinfo($file);
		$name = $info['basename'];
		return new \CURLFile($file, $mime, $name);
		}


	/*
	Use this static function to prepare a string for posting it using cURL to a file field
	Pass the resulting object of this function inside the $data array of the post function
	*/
	public static function prepare_file_string(string $data, string $postname, string $mime = 'text/plain'): \CURLStringFile
		{
		return new \CURLStringFile($data, $postname, $mime);
		}


	public function post(string $url, array $data): string|false
		{
		$this->init();

		curl_setopt($this->curl, CURLOPT_URL, $url);
		//curl_setopt($this->curl, CURLOPT_HEADER, 0);

		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		if ($this->is_put)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
			}
		elseif ($this->is_delete)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			}

		$response = curl_exec($this->curl);

		return $this->complete_transfer($response);
		}
	
	// curl doesn't like multilevel arrays in CURLOPT_POSTFIELDS, so we have to manually build the data with http_build_query
	public function postMultiArray(string $url, array $data): string|false
		{
		$this->init();

		$datastr = http_build_query($data, '', '&');
		$datastr = str_replace(["%5B", "%5D"], ["[", "]"], $datastr);
/*
		echo '<pre>';
		print_r($data);
		echo $datastr;
		echo '</pre>';
*/
		curl_setopt($this->curl, CURLOPT_URL, $url);
		//curl_setopt($this->curl, CURLOPT_HEADER, 0);

		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $datastr);
		if ($this->is_put)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
			}
		elseif ($this->is_delete)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			}

		$response = curl_exec($this->curl);

		return $this->complete_transfer($response);
		}
	
	public function postJson(string $url, array $data): string|false
		{
		$this->addHeader('Content-Type', 'application/json'); // add content-type to headers
		$this->init();

		curl_setopt($this->curl, CURLOPT_URL, $url);

		//curl_setopt($this->curl, CURLOPT_HEADER, ['Content-Type: application/json'] );

		//curl_setopt($this->curl, CURLOPT_POST, 1);
		$jsondata = json_encode($data);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $jsondata);
		if ($this->is_put)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
			}
		elseif ($this->is_delete)
			{
			curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			}
		
		$this->deleteHeader('Content-Type'); // remove the custom content-type to not interfere with other requests

		$response = curl_exec($this->curl);

		return $this->complete_transfer($response);
		}

	/*
	* Resolve the result of a completed cURL transfer.
	*
	* A transport (cURL) error is the only failure that carries the real error
	* message. A successful transfer with a zero-length body is treated as a
	* failure too (defect #11): the API always answers with a JSON body, so an
	* empty body means something went wrong, and reporting it as a success would
	* show `isOk() === true` for a broken request.
	*
	* @return string|false
	*/
	private function complete_transfer(string|false $response): string|false
		{
		$curl_error = curl_error($this->curl);

		if ($curl_error !== '')
			{
			$this->set_error($curl_error);
			$this->close();
			return false;
			}

		if ($response === '')
			{
			$this->set_error('FAN Courier returned an empty response');
			$this->close();
			return false;
			}

		$this->close();
		return $response;
		}

	private function set_error(string $error): static
		{
		$this->error = $error;
		return $this;
		}

	public function getError(): string
		{
		return $this->error;
		}

	/*
	* Add a custom header to curl
	*/
	public function addHeader(string $name, string $value): static
		{
		$this->headers[ $name ] = $value;
		return $this;
		}

	/*
	* Remove a custom header from curl requests
	*/
	public function deleteHeader(string $name): static
		{
		if (array_key_exists($name, $this->headers))
			{
			unset($this->headers[ $name ]);
			}
		return $this;
		}
	
	/* set put request (automatically disables delete request) */
	public function setPutRequest(bool $enabled = false): static
		{
		$this->is_put = $enabled;
		$this->is_delete = false;
		return $this;
		}
	
	/* set delete request (automatically disables put request) */
	public function setDeleteRequest(bool $enabled = false): static
		{
		$this->is_delete = $enabled;
		$this->is_put = false;
		return $this;
		}

	/* if you need to skip host/peer validation */
	public function setVerify(bool $host = true, bool $peer = true): static
		{
		$this->verify_host = $host;
		$this->verify_peer = $peer;
		return $this;
		}

	/* if you need a custom request timeout */
	public function setTimeout(int $con_timeout = 3, int $timeout = 6): static
		{
		$this->con_timeout = $con_timeout;
		$this->timeout = $timeout;
		return $this;
		}

	public function __destruct()
		{

		}
}
