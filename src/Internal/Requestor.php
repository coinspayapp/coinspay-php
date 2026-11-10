<?php

namespace CoinsPay\Internal;

use CoinsPay\Exception\ApiConnectionException;
use CoinsPay\Exception\ApiException;
use CoinsPay\Exception\ApiTimeoutException;
use CoinsPay\Exception\ConflictException;
use CoinsPay\Exception\InvalidArgumentException;
use CoinsPay\Exception\InvalidResponseException;
use CoinsPay\Exception\RateLimitException;
use CoinsPay\Http\HttpClientInterface;
use CoinsPay\Http\TransportException;
use CoinsPay\ResponseInfo;

/**
 * Sends API requests: headers, retries, errors.
 *
 * @internal
 */
final class Requestor
{
    const IDEMPOTENCY_KEY = '/^[\x21-\x7e]{8,128}$/D';

    /** @var Secret */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /** @var HttpClientInterface */
    private $http;

    /** @var array<string, mixed> */
    private $config;

    /** @var callable */
    private $sleep;

    /** @var ResponseInfo|null */
    private $lastResponse;

    /** @var string */
    private $userAgent;

    /**
     * @param string $apiKey
     * @param string $baseUrl
     * @param HttpClientInterface $http
     * @param array<string, mixed> $config timeout, connect_timeout, max_retries, max_retry_delay, headers
     * @param callable $sleep function (float $seconds)
     * @param string $userAgent
     */
    public function __construct(
        #[\SensitiveParameter]
        $apiKey,
        $baseUrl,
        HttpClientInterface $http,
        array $config,
        callable $sleep,
        $userAgent
    ) {
        $this->apiKey = new Secret($apiKey);
        $this->baseUrl = $baseUrl;
        $this->http = $http;
        $this->config = $config;
        $this->sleep = $sleep;
        $this->userAgent = $userAgent;
    }

    /** @return ResponseInfo|null */
    public function lastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @param string $method
     * @param string $path Relative to /api/v1, already filled.
     * @param array<string, mixed>|null $query
     * @param array<string, mixed>|null $body
     * @param string $retry safe, idempotent, cancel or unsafe.
     * @param string|null $idempotencyKey
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return array<string, mixed>|list<mixed> The parsed JSON body.
     */
    public function send($method, $path, $query, $body, $retry, $idempotencyKey, array $options)
    {
        $maxRetries = isset($options['max_retries']) ? (int) $options['max_retries'] : $this->config['max_retries'];
        $timeout = isset($options['timeout']) ? (float) $options['timeout'] : $this->config['timeout'];
        $url = $this->baseUrl . $path;
        if ($query) {
            $search = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
            if ($search !== '') {
                $url .= '?' . $search;
            }
        }
        // The same bytes on every attempt: the idempotency key is bound to the body.
        $payload = $method === 'POST' ? self::encode($body) : null;
        $headers = $this->headers($payload !== null, $idempotencyKey, isset($options['headers']) ? $options['headers'] : []);
        $this->lastResponse = null;

        for ($attempt = 0; ; $attempt++) {
            $retryableTransport = true;
            try {
                $response = $this->http->send($method, $url, $headers, $payload, $timeout, $this->config['connect_timeout']);
                $data = self::parse($response->status, $response->body);
                if ($response->status >= 200 && $response->status < 300) {
                    $this->lastResponse = new ResponseInfo($response->status, $response->headers, $idempotencyKey !== null && $response->status === 200);
                    return $data;
                }
                $failure = self::error($response->status, $data, $response->headers);
            } catch (TransportException $error) {
                // Not chained as "previous": its trace holds the request headers, the key among them.
                $failure = $error->isTimeout()
                    ? new ApiTimeoutException($error->getMessage())
                    : new ApiConnectionException($error->getMessage(), $error->wasSent());
                $retryableTransport = $error->isRetryable();
            } catch (InvalidResponseException $error) {
                $failure = $error;
            }

            // A retried cancel that finds the invoice cancelled: the first attempt went through.
            if ($retry === 'cancel' && $attempt > 0 && $failure instanceof ConflictException
                && $failure->getErrorCode() === 'INVOICE_NOT_OPEN') {
                $details = $failure->getDetails();
                if (isset($details['status']) && $details['status'] === 'cancelled') {
                    return $this->send('GET', preg_replace('#/cancel$#', '', $path), null, null, 'safe', null, $options);
                }
            }

            $delay = $attempt < $maxRetries ? $this->retryDelay($failure, $retry, $attempt, $retryableTransport) : null;
            if ($delay === null) {
                throw $failure;
            }
            call_user_func($this->sleep, $delay);
        }
    }

    /**
     * Pause in seconds before the next attempt, or null when the failure must not be retried.
     *
     * @param \Exception $failure
     * @param string $retry
     * @param int $attempt
     * @param bool $retryableTransport
     * @return float|null
     */
    private function retryDelay($failure, $retry, $attempt, $retryableTransport)
    {
        $status = $failure instanceof ApiException || $failure instanceof InvalidResponseException ? $failure->getStatus() : null;
        $hinted = null;
        if ($status === 429) {
            // Rate limits act before the request is handled: any request may repeat.
            $retryable = true;
            $hinted = $failure instanceof RateLimitException ? $failure->getRetryAfter() : null;
        } elseif ($status === 503) {
            $retryable = true;
        } elseif ($status === 502 || $status === 504) {
            $retryable = $retry !== 'unsafe';
        } elseif ($failure instanceof ApiConnectionException) {
            $retryable = $retryableTransport && (!$failure->wasSent() || $retry !== 'unsafe');
        } else {
            $retryable = false;
        }
        if (!$retryable) {
            return null;
        }
        $delay = $hinted !== null ? (float) $hinted : 0.5 * pow(2, $attempt) * (0.75 + mt_rand(0, 1000) / 2000);
        return $delay > $this->config['max_retry_delay'] ? null : $delay;
    }

    /**
     * @param bool $hasBody
     * @param string|null $idempotencyKey
     * @param array<string, string> $extra
     * @return array<int, string>
     */
    private function headers($hasBody, $idempotencyKey, array $extra)
    {
        $headers = array_change_key_case(array_merge($this->config['headers'], $extra), CASE_LOWER);
        $headers['accept'] = 'application/json';
        $headers['user-agent'] = $this->userAgent;
        $headers['authorization'] = 'Bearer ' . $this->apiKey->reveal();
        if ($hasBody) {
            $headers['content-type'] = 'application/json';
        }
        if ($idempotencyKey !== null) {
            $headers['idempotency-key'] = $idempotencyKey;
        }
        $lines = [];
        foreach ($headers as $name => $value) {
            if (preg_match('/[\r\n]/', $name . $value)) {
                throw new InvalidArgumentException("Header $name contains a line break");
            }
            $lines[] = $name . ': ' . $value;
        }
        return $lines;
    }

    /**
     * @param mixed $body
     * @return string
     */
    private static function encode($body)
    {
        if ($body === null || $body === []) {
            return '{}';
        }
        $json = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new InvalidArgumentException('The request cannot be encoded as JSON: ' . json_last_error_msg());
        }
        return $json;
    }

    /**
     * @param int $status
     * @param string $text
     * @return mixed
     */
    private static function parse($status, $text)
    {
        if ($status >= 300 && $status < 400) {
            throw new InvalidResponseException("The CoinsPay API answered with a redirect ($status); redirects are not followed", $status);
        }
        if ($text === '') {
            if ($status >= 200 && $status < 300) {
                throw new InvalidResponseException("Empty answer with status $status", $status);
            }
            return null;
        }
        $data = json_decode($text, true, 512, JSON_BIGINT_AS_STRING);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidResponseException("The answer with status $status is not JSON", $status);
        }
        return $data;
    }

    /**
     * @param int $status
     * @param mixed $data
     * @param array<string, string> $headers
     * @return \Exception
     */
    private static function error($status, $data, array $headers)
    {
        if (!is_array($data) || !isset($data['error']['code']) || !is_string($data['error']['code'])) {
            return new InvalidResponseException("HTTP $status without an API error body", $status);
        }
        $error = $data['error'];
        $message = isset($error['message']) && is_string($error['message']) ? $error['message'] : $error['code'];
        $details = isset($error['details']) && is_array($error['details']) ? $error['details'] : null;
        return ApiException::create($status, $error['code'], $message, $details, $headers);
    }

    /**
     * @param string $key
     * @return string
     */
    public static function checkIdempotencyKey($key)
    {
        if (!is_string($key) || !preg_match(self::IDEMPOTENCY_KEY, $key)) {
            throw new InvalidArgumentException('The idempotency key must be 8 to 128 printable ASCII characters without spaces');
        }
        return $key;
    }

    /** @return string A random UUID v4. */
    public static function newIdempotencyKey()
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
