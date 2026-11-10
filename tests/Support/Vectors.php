<?php

namespace CoinsPay\Tests\Support;

use CoinsPay\Client;

final class Vectors
{
    const INVOICE_ID = '7c9e6679-7425-40de-944b-e07fc1f90ae7';

    const ERROR_CLASS = [
        'Api' => 'CoinsPay\Exception\ApiException',
        'InvalidRequest' => 'CoinsPay\Exception\InvalidRequestException',
        'Authentication' => 'CoinsPay\Exception\AuthenticationException',
        'PermissionDenied' => 'CoinsPay\Exception\PermissionDeniedException',
        'NotFound' => 'CoinsPay\Exception\NotFoundException',
        'Conflict' => 'CoinsPay\Exception\ConflictException',
        'Unprocessable' => 'CoinsPay\Exception\UnprocessableException',
        'RateLimit' => 'CoinsPay\Exception\RateLimitException',
        'ServiceUnavailable' => 'CoinsPay\Exception\ServiceUnavailableException',
        'ApiConnection' => 'CoinsPay\Exception\ApiConnectionException',
        'ApiTimeout' => 'CoinsPay\Exception\ApiTimeoutException',
        'InvalidResponse' => 'CoinsPay\Exception\InvalidResponseException',
    ];

    /**
     * @param string $name
     * @return array<string, mixed>
     */
    public static function load($name)
    {
        return json_decode(file_get_contents(__DIR__ . "/../data/vectors/$name.json"), true);
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     * @param array<string, mixed> $options
     * @return array{0: Client, 1: FakeHttp, 2: \ArrayObject}
     */
    public static function client(array $steps, array $options = [])
    {
        $http = new FakeHttp($steps);
        $sleeps = new \ArrayObject();
        $client = new Client('cpk_test_secret_key_value', 'https://pay.example.com', $options + [
            'http_client' => $http,
            'sleep' => function ($seconds) use ($sleeps) {
                $sleeps[] = $seconds;
            },
        ]);
        return [$client, $http, $sleeps];
    }

    public static function callByRetry(Client $client, $retry)
    {
        switch ($retry) {
            case 'safe':
                return $client->invoices->get(self::INVOICE_ID);
            case 'idempotent':
                return $client->invoices->create(['currency' => 'USD', 'amount' => '49.90'], ['idempotency_key' => 'order-1042']);
            case 'unsafe':
                return $client->addresses->create(['chain' => 'tron']);
            case 'cancel':
                return $client->invoices->cancel(self::INVOICE_ID);
        }
        throw new \LogicException($retry);
    }
}
