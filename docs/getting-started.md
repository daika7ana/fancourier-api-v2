# Getting started

## Requirements

| Requirement | Version / extension |
|---|---|
| PHP | `^8.3` (declared in `composer.json`) |
| Extensions | `ext-curl`, `ext-json`, `ext-fileinfo` |

`ext-fileinfo` is required by `Client::prepare_file()`, which uses `mime_content_type()`.

## Installation

### Composer

```bash
composer require daika7ana/fancourier-api
```

The package is PSR-4 autoloaded under the `Fancourier\` namespace with its root at
`src/Fancourier/`.

### Without Composer

The library ships a hand-rolled autoloader at `src/autoload.php`:

```php
require_once '/path/to/fancourier-api/src/autoload.php';
```

The bundled examples prefer `vendor/autoload.php` when Composer has installed the
dependencies and fall back to `src/autoload.php` otherwise (see `examples/_init.php`).

## Authentication and the token lifecycle

Construct the client with your account credentials. The fourth argument is a bearer token;
passing an empty string tells the library to fetch one automatically before the first
request.

```php
use Fancourier\Fancourier;

$fan = new Fancourier(
    getenv('FANCOURIER_CLIENT_ID'),
    getenv('FANCOURIER_USERNAME'),
    getenv('FANCOURIER_PASSWORD'),
    '', // token from cache, or '' to retrieve on first use
);
```

`Fancourier::__construct(string $clientId, string $username, string $password, string $bearer_token = '')`

- Credentials are never hardcoded by the library. Read them from environment variables or
  your own config. The bundled examples read `FANCOURIER_TEST_CLIENT_ID`,
  `FANCOURIER_TEST_USERNAME` and `FANCOURIER_TEST_PASSWORD`.
- `$clientId` accepts an `int|string` at the `Auth` layer and is sent as `clientId` in most
  request payloads.

### Token methods

| Method | Returns | Notes |
|---|---|---|
| `getToken(bool $refresh = false)` | `string\|false` | Returns the cached token, fetching one if the cache is empty or expired. `$refresh = true` forces a re-login even when a token is cached. Returns `false` on failure. |
| `getTokenExpiresAt()` | `string` | The API's `expiresAt` value as `Y-m-d H:i:s`. The timezone is unspecified by the API. |
| `getTokenMessage()` | `string` | The error message from the last failed token retrieval; empty on success. |

```php
$token = $fan->getToken(true);       // force refresh
if ($token === false) {
    echo $fan->getTokenMessage();    // why login failed
}
```

### Lifecycle rules

- A generated token is valid for **24 hours**. The examples cache it in
  `examples/examples_token.txt` and discard the file after ~24h (`86000` seconds).
- If no token is supplied, it is retrieved automatically on the first request; you never
  have to call `getToken()` yourself (though you may, e.g. to cache it).
- `Auth::isTokenExpired()` treats a missing or unparsable `expiresAt` as **not expired**
  (it keeps using the cached token rather than forcing a refresh).
- **Refresh + retry once:** if a request fails at the transport level and the cached token
  was stale (or became stale), `AbstractRequest::send()` refreshes the token and retries
  the request **exactly once**. There is no loop, so at most one retry happens.
- **Auth failure throws:** if no usable token can be obtained, `send()` throws
  `\RuntimeException` *before* the request is issued — it will not emit an empty
  `Bearer ` header. The exception message includes `getTokenMessage()` when available.

## Client configuration

Both settings apply to authentication requests and to every endpoint call.

```php
// Skip TLS host/peer verification (default: both true). Use only when required.
$fan->setVerify(false, false);

// Connection timeout (seconds) and total timeout (seconds).
$fan->setTimeout(3, 6);
```

| Method | Defaults | Notes |
|---|---|---|
| `setVerify(bool $verifyHost = true, bool $verifyPeer = true)` | `true, true` | Forwards to `Auth` and every request's `Client`. |
| `setTimeout(int $conTimeout = 3, int $timeout = 6)` | `3, 6` | Forwards to `Auth` and every request's `Client`. |

A request may also override verify/timeout itself via `RequestInterface::setVerify()` /
`setTimeout()`; the facade sets them on each request before sending.

## Execution flow

```
$fan->someEndpoint($request)
        │
        ▼
Fancourier::send($request)               // protected
        │  $request->authenticate($this->auth)
        │          ->setVerify(...)
        │          ->setTimeout(...)
        │          ->send()
        ▼
AbstractRequest::send()
        │  $data = $this->pack()          // typed payload array
        │  $token = $auth->getToken()
        │  Client::addHeader('Authorization', 'Bearer ' . $token)
        ▼
AbstractRequest::dispatch($data)
        │  GET  → Client::get()
        │  POST → Client::postJson()
        │  PUT  → Client::setPutRequest(true)->get()
        │  DELETE → Client::setDeleteRequest(true)->get()
        │  POSTPUT / POSTDELETE → Client::postMultiArray()
        ▼
Client (cURL)  ──►  raw response string | false
        ▼
$response->setData($responseString)      // subclass parses the JSON/binary body
```

Key points:

- The facade method names an endpoint; it returns the endpoint's typed response object.
- `AbstractRequest` owns the gateway path and HTTP verb; `Client` owns cURL, SSL
  verification, timeouts and headers.
- `Client::postMultiArray()` manually builds the query string because cURL mishandles
  nested arrays in `CURLOPT_POSTFIELDS`. `POSTPUT`/`POSTDELETE` route through it.
- `RequestInterface` (`authenticate`, `setVerify`, `setTimeout`, `send`, `pack`) is the
  shared request contract; `ResponseInterface` is the shared response contract.

## Reading responses

Every response extends `Fancourier\Response\Generic`:

```php
$response = $fan->getCosts($request);

$response->isOk();             // true when there is no error code/message
$response->getData();          // unprocessed decoded API payload (or raw string)
$response->getErrorMessage();  // error text, when isOk() is false
$response->getErrorCode();     // int|string|null
```

Endpoint responses add typed getters and collection accessors — see
[the endpoint reference](endpoints.md), e.g. `GetCosts` exposes `getCostTotal()`,
`TrackAwb` exposes `getAll()` / `getAwb()`. Error semantics are documented in full in
[error handling](errors.md).

## Running the bundled examples

Each script in `examples/` bootstraps itself and lists the available request/response
methods in comments. Run them with the credentials in the environment:

```bash
export FANCOURIER_TEST_CLIENT_ID=...
export FANCOURIER_TEST_USERNAME=...
export FANCOURIER_TEST_PASSWORD=...

php examples/getCosts.php
```

`examples/_init.php` resolves the autoloader and token file relative to its own location,
so the examples can be run from any working directory. They cache the bearer token in
`examples/examples_token.txt` (gitignored).

## Upgrading from 1.x

Version 2.0 is a breaking major (typed API, camelCase `Client` methods, removed dead
methods, backed enums). See [Upgrading to 2.0](upgrading-to-2.0.md) and the authoritative
[`MIGRATION.md`](../MIGRATION.md).
