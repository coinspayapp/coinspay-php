<?php

namespace CoinsPay;

use CoinsPay\Exception\InvalidArgumentException;
use CoinsPay\Http\CurlClient;
use CoinsPay\Http\HttpClientInterface;
use CoinsPay\Internal\Operations;
use CoinsPay\Internal\Requestor;

/**
 * Client of the CoinsPay API.
 *
 *     $coinspay = new \CoinsPay\Client(getenv('COINSPAY_KEY'), 'https://pay.example.com');
 *     $invoice = $coinspay->invoices->create(['currency' => 'USD', 'amount' => '49.90', 'reference' => 'order-1042']);
 *     header('Location: ' . $invoice->pay_url);
 */
final class Client
{
    const VERSION = '0.1.0';

    // D: without it "$" also matches before a final line break.
    const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD';

    /** @var Service\InvoiceService */
    public $invoices;

    /** @var Service\AddressService */
    public $addresses;

    /** @var Service\DepositService */
    public $deposits;

    /** @var Service\BalanceService */
    public $balances;

    /** @var Service\PayoutService */
    public $payouts;

    /** @var Service\ShopService */
    public $shop;

    /** @var Service\ChainService */
    public $chains;

    /** @var Service\CurrencyService */
    public $currencies;

    /** @var Requestor */
    private $requestor;

    /** @var string */
    private $baseUrl;

    /** @var bool */
    private $testMode;

    /**
     * @param string $apiKey API key of the shop: cpk_…, or cpk_test_… for test mode. Keep it on your server.
     * @param string $baseUrl The dashboard origin (https://pay.example.com) or the API address ending in /api/v1.
     * @param array<string, mixed> $options
     *        timeout             float, seconds for one attempt; 30 by default
     *        connect_timeout     float, seconds; 10 by default
     *        max_retries         int, retries after failures that are safe to retry; 2 by default, 0 turns them off
     *        max_retry_delay     float, the longest pause before a retry in seconds; a longer Retry-After fails at once; 5 by default
     *        allow_insecure_http bool, allows plain http to hosts other than localhost, for a test stand
     *        http_client         HttpClientInterface, your own transport; CurlClient by default
     *        headers             array<string, string>, sent with every request
     */
    public function __construct(
        #[\SensitiveParameter]
        $apiKey,
        $baseUrl,
        array $options = []
    ) {
        if (!is_string($apiKey) || trim($apiKey) === '') {
            throw new InvalidArgumentException('The API key is required');
        }
        $apiKey = trim($apiKey);
        if (preg_match('/[\s\x00-\x1f\x7f]/', $apiKey)) {
            throw new InvalidArgumentException('The API key contains spaces or control characters');
        }
        $http = isset($options['http_client']) ? $options['http_client'] : new CurlClient();
        if (!$http instanceof HttpClientInterface) {
            throw new InvalidArgumentException('http_client must implement CoinsPay\Http\HttpClientInterface');
        }

        $this->baseUrl = self::normalizeBaseUrl($baseUrl, !empty($options['allow_insecure_http']));
        $this->testMode = strpos($apiKey, 'cpk_test_') === 0;
        $sleep = isset($options['sleep']) ? $options['sleep'] : function ($seconds) {
            usleep((int) round($seconds * 1000000));
        };
        $this->requestor = new Requestor(
            $apiKey,
            $this->baseUrl,
            $http,
            [
                'timeout' => self::positive($options, 'timeout', 30.0),
                'connect_timeout' => self::positive($options, 'connect_timeout', 10.0),
                'max_retries' => self::nonNegative($options, 'max_retries', 2),
                'max_retry_delay' => self::positive($options, 'max_retry_delay', 5.0, true),
                'headers' => isset($options['headers']) && is_array($options['headers']) ? $options['headers'] : [],
            ],
            $sleep,
            'coinspay-php/' . self::VERSION . ' php/' . PHP_VERSION
        );

        $this->invoices = new Service\InvoiceService($this);
        $this->addresses = new Service\AddressService($this);
        $this->deposits = new Service\DepositService($this);
        $this->balances = new Service\BalanceService($this);
        $this->payouts = new Service\PayoutService($this);
        $this->shop = new Service\ShopService($this);
        $this->chains = new Service\ChainService($this);
        $this->currencies = new Service\CurrencyService($this);
    }

    /** @return string The API address requests go to, ending in /api/v1. */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /** @return bool True for a test key (cpk_test_…): invoices only, paid without money. */
    public function isTestMode()
    {
        return $this->testMode;
    }

    /**
     * A random idempotency key, for when there is no natural one such as an order or payout number.
     *
     * @return string
     */
    public static function newIdempotencyKey()
    {
        return Requestor::newIdempotencyKey();
    }

    /** @return ResponseInfo|null Status, headers and "replayed" of the last answer. */
    public function getLastResponse()
    {
        return $this->requestor->lastResponse();
    }

    /**
     * Any API request, for methods this version of the library does not know yet.
     *
     * @param string $method GET or POST.
     * @param string $path Relative to /api/v1, for example /invoices.
     * @param array<string, mixed> $options query, body, idempotency_key, timeout, max_retries, headers
     * @return mixed The parsed JSON body as an array.
     */
    public function request($method, $path, array $options = [])
    {
        if ($method !== 'GET' && $method !== 'POST') {
            throw new InvalidArgumentException('The method must be GET or POST');
        }
        if (!is_string($path) || !preg_match('#^/[\w\-./]*$#D', $path) || preg_match('#(^|/)\.\.?(/|$)#', $path)) {
            throw new InvalidArgumentException("Bad API path: $path");
        }
        $key = isset($options['idempotency_key']) ? Requestor::checkIdempotencyKey($options['idempotency_key']) : null;
        return $this->requestor->send(
            $method,
            $path,
            isset($options['query']) ? $options['query'] : null,
            isset($options['body']) ? $options['body'] : null,
            $method === 'GET' ? 'safe' : ($key !== null ? 'idempotent' : 'unsafe'),
            $key,
            $options
        );
    }

    /**
     * @internal Used by the services.
     *
     * @param string $operation
     * @param array<string, mixed> $input id, params, idempotency_key, options
     * @return mixed
     */
    public function call($operation, array $input)
    {
        $spec = Operations::ALL[$operation];
        $path = $spec['path'];
        if (strpos($path, '{id}') !== false) {
            $id = isset($input['id']) ? $input['id'] : null;
            if (!is_string($id) || !preg_match(self::UUID, $id)) {
                throw new InvalidArgumentException('The id must be a UUID, got ' . var_export($id, true));
            }
            $path = str_replace('{id}', strtolower($id), $path);
        }

        $options = isset($input['options']) ? $input['options'] : [];
        $params = isset($input['params']) ? $input['params'] : [];
        if ($spec['params'] !== null) {
            $params = self::checkKeys($params, $spec['params'], '');
            self::checkDecimals($params, $spec['decimals']);
        }
        if (isset($options['extra'])) {
            if (!is_array($options['extra'])) {
                throw new InvalidArgumentException('The "extra" option must be an array');
            }
            // Fields this version of the library does not know yet: sent as given.
            $params = array_merge($params, $options['extra']);
        }

        $data = $this->requestor->send(
            $spec['method'],
            $path,
            $spec['method'] === 'GET' ? $params : null,
            $spec['method'] === 'POST' ? $params : null,
            $spec['retry'],
            isset($input['idempotency_key']) ? $input['idempotency_key'] : null,
            $options
        );

        $class = $spec['returns'];
        if ($spec['page'] !== null) {
            $items = isset($data[$spec['page']]) && is_array($data[$spec['page']]) ? $data[$spec['page']] : [];
            return new Page(array_map([$class, 'fromArray'], array_filter($items, 'is_array')), isset($data['next_cursor']) ? $data['next_cursor'] : null);
        }
        if ($spec['unwrap'] !== null) {
            $data = isset($data[$spec['unwrap']]) ? $data[$spec['unwrap']] : null;
        }
        if (!is_array($data)) {
            throw new Exception\InvalidResponseException("Unexpected answer of $operation", 200);
        }
        return $spec['many'] ? array_map([$class, 'fromArray'], array_values(array_filter($data, 'is_array'))) : $class::fromArray($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo()
    {
        return ['baseUrl' => $this->baseUrl, 'testMode' => $this->testMode];
    }

    /**
     * The API address from the dashboard origin or the full address ending in /api/v1.
     *
     * @param string $value
     * @param bool $allowInsecureHttp
     * @return string
     */
    public static function normalizeBaseUrl($value, $allowInsecureHttp)
    {
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidArgumentException('The base URL is required');
        }
        $parts = parse_url(trim($value));
        if ($parts === false || !isset($parts['scheme'], $parts['host']) || $parts['host'] === '') {
            // Without the value: an API key passed here by mistake must not reach the logs.
            throw new InvalidArgumentException('The base URL is not a URL; the arguments are ($apiKey, $baseUrl)');
        }
        if (isset($parts['query']) || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('The base URL must not contain a query, a fragment or credentials');
        }
        $scheme = strtolower($parts['scheme']);
        $host = strtolower($parts['host']);
        $local = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
        if ($scheme !== 'https' && !($scheme === 'http' && ($local || $allowInsecureHttp))) {
            throw new InvalidArgumentException('The base URL must use https (http is allowed for localhost or with allow_insecure_http)');
        }
        $path = isset($parts['path']) ? $parts['path'] : '';
        $path = trim($path, '/') === '' ? '/api/v1' : rtrim($path, '/');
        if (substr($path, -7) !== '/api/v1') {
            throw new InvalidArgumentException('The base URL must be the dashboard origin or end with /api/v1');
        }
        return $scheme . '://' . $host . (isset($parts['port']) ? ':' . $parts['port'] : '') . $path;
    }

    /**
     * Unknown keys are typos: the API would silently ignore them. Lists with gaps, as
     * array_filter() leaves them, are renumbered: json_encode() would make them objects.
     *
     * @param mixed $params
     * @param array<string, mixed> $allowed
     * @param string $where
     * @return array<string, mixed>
     */
    private static function checkKeys($params, array $allowed, $where)
    {
        if (!is_array($params)) {
            throw new InvalidArgumentException(($where === '' ? 'Parameters' : rtrim($where, '.')) . ' must be an array');
        }
        $checked = [];
        foreach ($params as $key => $value) {
            if (!is_string($key) || !isset($allowed[$key])) {
                throw new InvalidArgumentException("Unknown parameter $where$key; pass new API fields in the \"extra\" option");
            }
            if (is_array($allowed[$key]) && is_array($value)) {
                if (count(array_filter(array_keys($value), 'is_int')) === count($value)) {
                    $list = [];
                    foreach (array_values($value) as $index => $item) {
                        $list[] = self::checkKeys($item, $allowed[$key], "$where{$key}[$index].");
                    }
                    $value = $list;
                } else {
                    $value = self::checkKeys($value, $allowed[$key], "$where$key.");
                }
            }
            $checked[$key] = $value;
        }
        return $checked;
    }

    /**
     * Amounts go as decimal strings: a float would lose digits in json_encode() of PHP 7.0.
     *
     * @param array<string, mixed> $params
     * @param array<int, string> $paths
     */
    private static function checkDecimals(array $params, array $paths)
    {
        foreach ($paths as $path) {
            self::visitDecimal($params, explode('.', $path), '');
        }
    }

    /**
     * @param mixed $value
     * @param array<int, string> $segments
     * @param string $where
     */
    private static function visitDecimal($value, array $segments, $where)
    {
        if ($value === null) {
            return;
        }
        if ($segments === []) {
            if (!is_string($value)) {
                throw new InvalidArgumentException("$where must be a decimal string such as \"49.90\", not " . gettype($value));
            }
            return;
        }
        if (!is_array($value)) {
            return;
        }
        $segment = array_shift($segments);
        $many = substr($segment, -2) === '[]';
        $name = $many ? substr($segment, 0, -2) : $segment;
        if (!array_key_exists($name, $value)) {
            return;
        }
        $prefix = $where === '' ? $name : "$where.$name";
        if (!$many) {
            self::visitDecimal($value[$name], $segments, $prefix);
        } elseif (is_array($value[$name])) {
            foreach ($value[$name] as $index => $item) {
                self::visitDecimal($item, $segments, "{$prefix}[$index]");
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param string $name
     * @param float $default
     * @param bool $zero
     * @return float
     */
    private static function positive(array $options, $name, $default, $zero = false)
    {
        if (!isset($options[$name])) {
            return $default;
        }
        $value = $options[$name];
        if (!is_int($value) && !is_float($value) || $value < 0 || (!$zero && $value == 0)) {
            throw new InvalidArgumentException("$name must be a positive number");
        }
        return (float) $value;
    }

    /**
     * @param array<string, mixed> $options
     * @param string $name
     * @param int $default
     * @return int
     */
    private static function nonNegative(array $options, $name, $default)
    {
        if (!isset($options[$name])) {
            return $default;
        }
        if (!is_int($options[$name]) || $options[$name] < 0) {
            throw new InvalidArgumentException("$name must be a non-negative integer");
        }
        return $options[$name];
    }
}
