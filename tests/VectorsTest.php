<?php

namespace CoinsPay\Tests;

use CoinsPay\Exception\SignatureVerificationException;
use CoinsPay\Tests\Support\Vectors;
use CoinsPay\Webhook;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class VectorsTest extends TestCase
{
    public function test_signatures()
    {
        foreach (Vectors::load('signatures')['cases'] as $case) {
            $reason = 'ok';
            try {
                Webhook::verifySignature($case['body'], $case['header'], $case['secrets'], $case['tolerance'], $case['now']);
            } catch (SignatureVerificationException $error) {
                $reason = $error->getReason();
            }
            $this->assertSame($case['expect'], $reason, $case['name']);
        }
    }

    public function test_retries()
    {
        foreach (Vectors::load('retries')['cases'] as $case) {
            $options = isset($case['maxRetries']) ? ['max_retries' => $case['maxRetries']] : [];
            list($client, $http, $sleeps) = Vectors::client($case['steps'], $options);
            $error = null;
            try {
                Vectors::callByRetry($client, $case['retry']);
            } catch (\Exception $caught) {
                $error = $caught;
            }
            $expect = $case['expect'];
            $name = $case['name'];
            $this->assertCount($expect['attempts'], $http->requests, "$name: attempts");

            if ($expect['result'] === 'ok') {
                $this->assertNull($error, $name . ': ' . ($error ? get_class($error) . ' ' . $error->getMessage() : ''));
                if (isset($expect['replayed'])) {
                    $this->assertSame($expect['replayed'], $client->getLastResponse()->replayed, $name);
                }
            } else {
                $this->assertNotNull($error, $name);
                $this->assertSame(Vectors::ERROR_CLASS[$expect['error']], get_class($error), $name);
                if (isset($expect['code'])) {
                    $this->assertSame($expect['code'], $error->getErrorCode(), $name);
                }
                if (array_key_exists('retryAfter', $expect)) {
                    $this->assertSame($expect['retryAfter'], $error->getRetryAfter(), $name);
                }
                if (isset($expect['sent'])) {
                    $this->assertSame($expect['sent'], $error->wasSent(), $name);
                }
            }

            if (isset($expect['sleeps'])) {
                $this->assertCount(count($expect['sleeps']), $sleeps, "$name: sleeps");
                foreach ($expect['sleeps'] as $index => $want) {
                    $ms = $sleeps[$index] * 1000;
                    if (is_array($want)) {
                        $this->assertTrue($ms >= $want[0] && $ms <= $want[1], "$name: sleep $ms");
                    } else {
                        $this->assertEquals($want, $ms, "$name: sleep");
                    }
                }
            }
            $attempts = $http->requests;
            if (isset($expect['lastRequest'])) {
                $last = array_pop($attempts);
                $this->assertSame($expect['lastRequest'], $last['method'] . ' ' . $last['path'], $name);
            }
            $this->assertCount(1, array_unique(array_map(function ($request) {
                return (string) $request['body'] . '|' . (isset($request['headers']['idempotency-key']) ? $request['headers']['idempotency-key'] : '');
            }, $attempts)), "$name: one body and key");
        }
    }

    public function test_errors()
    {
        foreach (Vectors::load('errors')['cases'] as $case) {
            list($client) = Vectors::client([$case], ['max_retries' => 0]);
            $error = null;
            try {
                $client->invoices->get(Vectors::INVOICE_ID);
            } catch (\Exception $caught) {
                $error = $caught;
            }
            $expect = $case['expect'];
            $name = $case['status'] . ' ' . $expect['error'];
            $this->assertNotNull($error, $name);
            $this->assertSame(Vectors::ERROR_CLASS[$expect['error']], get_class($error), $name);
            $this->assertSame($case['status'], $error->getStatus(), $name);
            if (isset($expect['code'])) {
                $this->assertSame($expect['code'], $error->getErrorCode(), $name);
            }
            if (isset($expect['message'])) {
                $this->assertSame($expect['message'], $error->getMessage(), $name);
            }
            if (array_key_exists('details', $expect)) {
                $this->assertSame($expect['details'], $error->getDetails(), $name);
            }
            if (array_key_exists('retryAfter', $expect)) {
                $this->assertSame($expect['retryAfter'], $error->getRetryAfter(), $name);
            }
        }
    }
}
