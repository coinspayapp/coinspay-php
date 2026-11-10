# CoinsPay for PHP

Official library for the [CoinsPay](https://coinspay.app) crypto payments API: invoices
with a payment page, receiving addresses, payments, payouts to your wallets and webhooks.
PHP 7.0 or newer with ext-curl and ext-json, no other dependencies.
The API reference is at [coinspay.app/docs](https://coinspay.app/docs).

```bash
composer require coinspay/coinspay-php
```

Without Composer: `require 'coinspay-php/autoload.php';`.

## Quick start

Create an API key in the dashboard: shop → "API keys". A key with "Invoices only" access is
enough for a website; addresses, payments, balances and payouts need full access. Keep the
key on your server.

```php
$coinspay = new \CoinsPay\Client(getenv('COINSPAY_KEY'), 'https://coinspay.app');

$invoice = $coinspay->invoices->create([
    'currency' => 'USD',
    'amount' => '49.90',
    'reference' => 'order-1042',
    'return_url' => 'https://shop.example/orders/1042',
], ['idempotency_key' => 'order-1042']);

header('Location: ' . $invoice->pay_url);
```

Amounts are decimal strings (`'49.90'`), never floats: `json_encode` in PHP 7.0 loses
digits of a float, so the library refuses it. Unknown parameter keys are refused too: the
API would silently ignore a typo. Pass a field this version does not know yet in the
`extra` option.

## Webhooks

```php
use CoinsPay\Exception\SignatureVerificationException;
use CoinsPay\Model\InvoiceEvent;
use CoinsPay\Webhook;

try {
    $event = Webhook::constructEvent(
        file_get_contents('php://input'),   // the body exactly as received
        Webhook::headerFromGlobals(),
        getenv('COINSPAY_WEBHOOK_SECRET')
    );
} catch (SignatureVerificationException $error) {
    http_response_code(401);
    exit;
}
http_response_code(200);

if (!$event->test && $event instanceof InvoiceEvent && $event->type === 'invoice.paid') {
    markOrderPaid($event->data->invoice->reference, $event->data->invoice->id);
}
```

- One event may arrive twice: drop duplicates by `$event->id`.
- Rely on the state in the body, not on the arrival order.
- While you replace the secret, pass both: `[$newSecret, $oldSecret]`.
- An event of a type this version does not know is an `UnknownEvent`.
- `Webhook::signatureHeader($body, $secret)` signs a body for tests of your handler.

## Objects

Fields are public properties named as in the API: `$invoice->pay_url`,
`$invoice->items[0]->price`. `toArray()` and `json_encode()` give the object exactly as
the API sent it, including fields this version does not know yet. Status values are
constants: `\CoinsPay\Model\InvoiceStatus::PAID`.

## Lists

```php
$page = $coinspay->invoices->list(['status' => 'paid', 'limit' => 100]); // $page->data, $page->next_cursor
foreach ($coinspay->invoices->iterate(['status' => 'paid']) as $invoice) {
    // every paid invoice, page after page
}
```

## Errors

```php
use CoinsPay\ErrorCode;
use CoinsPay\Exception\ApiConnectionException;
use CoinsPay\Exception\ApiException;
use CoinsPay\Exception\PermissionDeniedException;

try {
    $coinspay->balances->list();
} catch (PermissionDeniedException $error) {
    if ($error->getErrorCode() === ErrorCode::KEY_ACCESS_DENIED) {
        // an "Invoices only" key
    }
} catch (ApiConnectionException $error) {
    if (!$error->wasSent()) {
        // never reached the API: safe to try again
    }
} catch (ApiException $error) {
    error_log($error->getStatus() . ' ' . $error->getErrorCode());
}
```

`ApiException` subclasses by status: `InvalidRequestException` 400,
`AuthenticationException` 401, `PermissionDeniedException` 403, `NotFoundException` 404,
`ConflictException` 409, `UnprocessableException` 422, `RateLimitException` 429 (with
`getRetryAfter()`), `ServiceUnavailableException` 503. Without an answer:
`ApiConnectionException` and `ApiTimeoutException`; a non-JSON answer:
`InvalidResponseException`. All implement `CoinsPay\Exception\ExceptionInterface`.

## Retries and idempotency

Failures that are safe to retry are retried twice with a short pause: network errors and
timeouts, 429, 502, 503 and 504 for reads, quotes, cancellations and creates with an
idempotency key. `addresses->create()` has no idempotency key and is retried only when the
request surely did not reach the API.

`invoices->create()` without `idempotency_key` gets a random key for its own retries; pass
your order number to make your own retries safe too. `payouts->create()` requires a key.
A repeat with a used key returns the existing object in its current state, and
`$coinspay->getLastResponse()->replayed` is `true`; after a failed request
`getLastResponse()` is `null`.

The key never shows in `var_dump()`, `print_r()`, `var_export()` or `serialize()` of the
client, and exceptions of requests do not carry it. Only an exception thrown by the
constructor itself (a wrong base URL) has the key among the arguments of its trace: PHP 8.2+
hides it (`#[\SensitiveParameter]`), on PHP 7.4–8.1 set `zend.exception_ignore_args = On`
as the production `php.ini` does.

## Client behaviour

- The API address must use `https`. Plain `http` is accepted for `localhost` and `127.0.0.1`, or with `'allow_insecure_http' => true` for a test stand.
- Each attempt times out after 30 seconds, and connecting after 10.
- Redirects are not followed, so the key is never sent to another host.
- IDs in the path are checked as UUIDs before the request is sent.
- The key never shows up in exceptions, `var_dump` or `print_r`.
- The transport can be replaced, for example to go through a proxy (see Options).

## Options

```php
$coinspay = new \CoinsPay\Client($key, 'https://coinspay.app', [
    'timeout' => 30,          // seconds per attempt
    'connect_timeout' => 10,
    'max_retries' => 2,
    'max_retry_delay' => 5,   // a longer Retry-After fails at once
    'http_client' => new \CoinsPay\Http\CurlClient([CURLOPT_PROXY => 'http://proxy:3128']),
]);
$coinspay->invoices->get($id, ['timeout' => 5, 'max_retries' => 0]);
$coinspay->request('GET', '/something/new'); // a method this version does not know yet
```

## Development

```bash
composer install
vendor/bin/phpunit
```

The library supports PHP 7.0 to 8.5, so the code avoids newer syntax. Tests that need a real HTTP server are skipped unless `COINSPAY_MOCK_URL` points to one. `node scripts/mock-server.mjs` from [coinspay-node](https://github.com/coinspayapp/coinspay-node) starts it and prints its address.

Files that start with "Generated from the CoinsPay OpenAPI document" and the data in `tests/data` come from our generator and are shared by the libraries for Node.js, PHP, Python and Go. Please do not edit them by hand: if a model or a test vector looks wrong, open an issue.

## License

MIT
