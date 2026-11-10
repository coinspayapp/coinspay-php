<?php

namespace CoinsPay\Http;

/**
 * Sends one HTTP request. Implement it to route requests through your own
 * HTTP stack; CurlClient is the default. It must not follow redirects.
 */
interface HttpClientInterface
{
    /**
     * @param string $method GET or POST.
     * @param string $url
     * @param array<int, string> $headers Lines "Name: value"; they hold the API key, so mark the
     *        parameter #[\SensitiveParameter] and keep it out of logs and exceptions.
     * @param string|null $body
     * @param float $timeout Whole request, seconds.
     * @param float $connectTimeout Seconds.
     * @return Response Any status, including 4xx and 5xx.
     * @throws TransportException No answer.
     */
    public function send($method, $url, array $headers, $body, $timeout, $connectTimeout);
}
