# Error handling

Two layers report failure: the transport layer (`Client` / `Auth`) and the API body parser
(`Response\Generic` and its subclasses). Everything surfaces through the same
`ResponseInterface` methods.

## The response contract

`Fancourier\Response\ResponseInterface`:

| Method | Returns |
|---|---|
| `getErrorCode()` | `int\|string\|null` |
| `getErrorMessage()` | `?string` |
| `getData()` | `mixed` — the decoded payload (or raw string) |
| `setErrorCode(int\|string\|null $code): static` | — |
| `setErrorMessage(?string $message): static` | — |
| `setData(mixed $data): static` | — |

`Response\Generic` (the base of every response) adds:

```php
public function isOk(): bool
{
    return empty($this->getErrorCode()) && empty($this->getErrorMessage());
}
```

So a response is successful only when it has **no error code and no error message**.

```php
$response = $fan->deleteAwb($request);

if ($response->isOk()) {
    // ...
} else {
    var_dump($response->getErrorCode(), $response->getErrorMessage());
}
```

## How errors are recorded

### 1. Transport (cURL) failures

If a transfer fails, `AbstractRequest::send()` stores code `-1` and the cURL error text:

```php
$this->response->setErrorCode(-1)->setErrorMessage($this->client->getError());
```

`Client` sets that text for:

- a cURL error (`curl_error()`), and
- a **zero-length body** on the POST-family transfers (`post`, `postJson`,
  `postMultiArray`), via `complete_transfer()`: `FAN Courier returned an empty response`.
  The API always answers with a body, so an empty body is a failure — it must not report
  `isOk() === true` for a broken request.

`Client::postJson()` also reports `Failed to encode request payload as JSON` when
`json_encode()` fails. `Client::get()` (used by `GET` and the PUT/DELETE-emulated verbs)
returns `false` on a cURL error; because a `GET` response is not routed through
`complete_transfer()`, an empty `GET` body may surface as code `-1` with an empty message.

### 2. API bodies that report failure

Response subclasses call the protected helper `Generic::setErrorFromBody()` when the body
is not a success body (typically `status !== "success"`):

```php
protected function setErrorFromBody(
    mixed $body = null,
    int|string $code = -1,
    string $fallback = 'Unknown error',
): static
```

- Array body: message is taken from `message`, then `error`, then `errors`.
- String body (non-JSON): the raw string becomes the message.
- Array messages are `json_encode()`d.
- An empty/absent message becomes `$fallback`.
- The result is always a non-empty message plus an error code, so `isOk()` cannot report
  success on a non-success body.

`PrintAwb` is special: a JSON body with `status` of `fail`/`error` is a failure and uses the
status string itself as the error code; a **non-JSON** body is the label payload (PDF bytes,
ZPL text or HTML), so it is stored as success data.

### 3. Auth failures

Token retrieval is lazy. `Auth::getToken()` catches login exceptions, stores the message in
`getTokenMessage()` and returns `false`:

```php
$token = $fan->getToken(true);
if ($token === false) {
    echo $fan->getTokenMessage();
}
```

When a request is sent, `AbstractRequest::assertUsableToken()` refuses to proceed with a
missing token and throws **before any HTTP request is issued**:

```
RuntimeException: Authentication failed: no bearer token[: <token message>]
```

This prevents the library from emitting an empty `Bearer ` header and issuing an
unauthenticated request.

### 4. Configuration/programming errors

`AbstractRequest::send()` throws on invalid internal state rather than sending a malformed
request:

| Situation | Exception |
|---|---|
| No `Auth` instance attached (request sent outside the facade) | `\RuntimeException` — `No Auth instance set; call authenticate() before send()` |
| Empty gateway | `\DomainException` — `No request gateway implemented` |
| Empty HTTP method | `\DomainException` — `No request method implemented` |
| Unknown HTTP method | `\DomainException` — `Unsupported request method: …` |

Value validation in setters throws too, e.g.:

- `GetCosts::setPaymentType()` — `\InvalidArgumentException` for a value other than
  `destinatar` / `expeditor`.
- `CreateCourierOrder::setSizes()` and `Objects\AwbIntern::setSizes()` /
  `Objects\AwbExtern::setSizes()` — `\Exception` when any dimension is `<= 0`.
- `Client::get()` / `post()` / `postJson()` / `postMultiArray()` — `\InvalidArgumentException`
  for an empty URL.

## Token expiry and retry

A request is retried **at most once**:

1. Before dispatching, the request remembers whether the cached token was already expired.
2. It sends the request with the current token.
3. If the transfer fails **and** the token was stale (or became stale), it forces a
   refresh (`getToken(true)`) and dispatches once more.

There is no retry loop, so a persistently failing request returns its failure after the
second attempt. The API documents no explicit expiry-error body, so expiry is approximated
by a transport failure plus a stale local token.

## Handling pattern

```php
$response = $fan->getCosts($request);

if (!$response->isOk()) {
    // Human-readable message is guaranteed non-empty for API-body failures.
    error_log($response->getErrorMessage());
    // Some responses expose the raw errors array:
    // $response->getAllErrors();
    return;
}

$total = $response->getCostTotal();
```

Wrap calls in `try`/`catch` if you want to distinguish programming/auth failures from API
failures:

```php
try {
    $response = $fan->createAwb($request);
} catch (\RuntimeException $e) {
    // Authentication failed, or the request was not configured correctly.
} catch (\DomainException $e) {
    // Missing/unsupported gateway or HTTP method.
}
```

See the [endpoint reference](endpoints.md) for per-response getters and
[getting started](getting-started.md#authentication-and-the-token-lifecycle) for the token
lifecycle.
