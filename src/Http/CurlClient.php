<?php

namespace CoinsPay\Http;

/**
 * HTTP over ext-curl. One handle is reused, so requests to the API share a
 * connection. Redirects are never followed: a redirect would carry the API key
 * to another address.
 */
final class CurlClient implements HttpClientInterface
{
    /** The request never left: no DNS answer, connection refused. */
    const NOT_SENT = [5, 6, 7];

    /** TLS failures: retrying does not help. */
    const TLS = [35, 51, 53, 54, 58, 59, 60, 64, 66, 77, 80, 82, 83, 90, 91];

    /** @var resource|\CurlHandle|null */
    private $handle;

    /** @var array<int, mixed> */
    private $curlOptions;

    /**
     * @param array<int, mixed> $curlOptions Extra CURLOPT_* options, for example CURLOPT_PROXY.
     *        Options that keep requests safe (TLS checks, no redirects) cannot be changed.
     */
    public function __construct(array $curlOptions = [])
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('The CoinsPay library needs the PHP curl extension');
        }
        $this->curlOptions = $curlOptions;
    }

    public function __destruct()
    {
        if ($this->handle !== null && PHP_VERSION_ID < 80000) {
            curl_close($this->handle);
        }
    }

    public function send(
        $method,
        $url,
        #[\SensitiveParameter]
        array $headers,
        $body,
        $timeout,
        $connectTimeout
    ) {
        if ($this->handle === null) {
            $this->handle = curl_init();
        } else {
            curl_reset($this->handle);
        }
        $handle = $this->handle;

        $responseHeaders = [];
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_TIMEOUT_MS => (int) ceil($timeout * 1000),
            CURLOPT_CONNECTTIMEOUT_MS => (int) ceil($connectTimeout * 1000),
            CURLOPT_ENCODING => '',
            // No "Expect: 100-continue": the body is small.
            CURLOPT_HTTPHEADER => array_merge($headers, ['Expect:']),
            CURLOPT_HEADERFUNCTION => function ($curl, $line) use (&$responseHeaders) {
                if (stripos($line, 'HTTP/') === 0) {
                    $responseHeaders = [];
                } elseif (strpos($line, ':') !== false) {
                    list($name, $value) = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($name))] = trim($value);
                }
                return strlen($line);
            },
        ];
        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($handle, $this->curlOptions);
        curl_setopt_array($handle, $options);

        $text = curl_exec($handle);
        if ($text === false) {
            $errno = curl_errno($handle);
            $message = 'Could not reach the CoinsPay API: ' . curl_error($handle);
            if ($errno === 28) {
                // A connect timeout sent nothing; a read timeout may have been processed.
                $sent = curl_getinfo($handle, CURLINFO_PRETRANSFER_TIME) > 0;
                throw new TransportException($message, $sent, $sent);
            }
            if (in_array($errno, self::TLS, true)) {
                throw new TransportException($message, false, false, false);
            }
            throw new TransportException($message, !in_array($errno, self::NOT_SENT, true));
        }
        return new Response(curl_getinfo($handle, CURLINFO_HTTP_CODE), $responseHeaders, $text);
    }
}
