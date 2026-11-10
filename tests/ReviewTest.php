<?php

namespace CoinsPay\Tests;

use CoinsPay\Client;
use CoinsPay\Exception\ApiConnectionException;
use CoinsPay\Exception\InvalidArgumentException;
use CoinsPay\Tests\Support\Vectors;
use CoinsPay\Webhook;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class ReviewTest extends TestCase
{
    public function test_the_key_is_not_in_any_dump_of_the_client()
    {
        $client = new Client('cpk_live_super_secret', 'https://pay.example.com');
        ob_start();
        var_dump($client);
        print_r($client);
        // The services refer back to the client: var_export warns about the circle and goes on.
        @var_export($client);
        var_dump((array) $client);
        $dump = ob_get_clean();
        $this->assertStringNotContainsString('super_secret', $dump);
        try {
            serialize($client);
            $this->fail('the client serialized');
        } catch (\Exception $error) {
            $this->assertStringNotContainsString('super_secret', $error->getMessage());
        }
    }

    public function test_a_connection_error_does_not_chain_the_transport_trace()
    {
        list($client) = Vectors::client([['network' => 'reset']], ['max_retries' => 0]);
        try {
            $client->invoices->get(Vectors::INVOICE_ID);
            $this->fail('no error');
        } catch (ApiConnectionException $error) {
            $this->assertNull($error->getPrevious());
            $this->assertStringNotContainsString('secret_key_value', print_r($error, true));
        }
    }

    public function test_a_final_line_break_does_not_pass_as_a_uuid()
    {
        list($client, $http) = Vectors::client([]);
        $this->expectException(InvalidArgumentException::class);
        try {
            $client->invoices->get(Vectors::INVOICE_ID . "\n");
        } finally {
            $this->assertCount(0, $http->requests);
        }
    }

    public function test_a_nan_or_negative_tolerance_is_refused()
    {
        $header = Webhook::signatureHeader('{}', 's', 0);
        foreach ([NAN, -1, INF, '300'] as $tolerance) {
            try {
                Webhook::verifySignature('{}', $header, 's', $tolerance, 1000000000);
                $this->fail('accepted ' . var_export($tolerance, true));
            } catch (InvalidArgumentException $error) {
                $this->assertNotEmpty($error->getMessage());
            }
        }
    }

    public function test_a_list_with_gaps_is_sent_as_a_list()
    {
        list($client, $http) = Vectors::client([['status' => 201, 'body' => ['invoice' => ['id' => Vectors::INVOICE_ID]]]]);
        $items = array_filter([
            ['name' => 'A', 'quantity' => '1', 'price' => '1'],
            null,
            ['name' => 'B', 'quantity' => '2', 'price' => '1'],
        ]);
        $client->invoices->create(['currency' => 'USD', 'items' => $items]);
        $body = json_decode($http->requests[0]['body'], true);
        $this->assertSame([0, 1], array_keys($body['items']));
        $this->assertSame('B', $body['items'][1]['name']);
    }

    public function test_the_last_response_is_cleared_by_a_failed_request()
    {
        list($client) = Vectors::client([
            ['status' => 200, 'body' => ['invoice' => ['id' => Vectors::INVOICE_ID]]],
            ['status' => 404, 'body' => ['error' => ['code' => 'NOT_FOUND', 'message' => 'Not found']]],
        ], ['max_retries' => 0]);
        $client->invoices->get(Vectors::INVOICE_ID);
        $this->assertSame(200, $client->getLastResponse()->status);
        try {
            $client->invoices->get(Vectors::INVOICE_ID);
        } catch (\Exception $error) {
            $this->assertNull($client->getLastResponse());
        }
    }

    public function test_a_key_passed_as_the_base_url_is_not_repeated_in_the_error()
    {
        try {
            new Client('https://pay.example.com', 'cpk_live_secret_value');
            $this->fail('no error');
        } catch (InvalidArgumentException $error) {
            $this->assertStringNotContainsString('cpk_live_secret_value', $error->getMessage());
        }
    }
}
