<?php

namespace CoinsPay\Tests;

use CoinsPay\Client;
use CoinsPay\Exception\ApiConnectionException;
use CoinsPay\Exception\ApiTimeoutException;
use CoinsPay\Exception\InvalidResponseException;
use CoinsPay\Tests\Support\Vectors;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class SocketTest extends TestCase
{
    /** @var string */
    private $mock;

    protected function set_up()
    {
        parent::set_up();
        $this->mock = (string) getenv('COINSPAY_MOCK_URL');
        if ($this->mock === '') {
            $this->markTestSkipped('COINSPAY_MOCK_URL is not set');
        }
    }

    private function client($mode, array $options = [])
    {
        return new Client('cpk_test_wire', "{$this->mock}/$mode/api/v1", $options + ['max_retries' => 0, 'allow_insecure_http' => true]);
    }

    public function test_sends_what_the_api_expects()
    {
        $invoice = $this->client('echo')->invoices->create(['currency' => 'USD', 'amount' => '1.50', 'title' => 'Заказ ✓'], ['idempotency_key' => 'order-77']);
        $raw = $invoice->toArray();
        $this->assertSame('POST', $raw['method']);
        $this->assertSame('/api/v1/invoices', $raw['path']);
        $this->assertSame('Bearer cpk_test_wire', $raw['headers']['authorization']);
        $this->assertSame('application/json', $raw['headers']['content-type']);
        $this->assertSame('order-77', $raw['headers']['idempotency-key']);
        $this->assertArrayNotHasKey('expect', $raw['headers']);
        $this->assertSame(strlen($raw['body']), (int) $raw['headers']['content-length']);
        $this->assertSame(['currency' => 'USD', 'amount' => '1.50', 'title' => 'Заказ ✓'], json_decode($raw['body'], true));
    }

    public function test_reuses_one_connection()
    {
        $client = $this->client('echo');
        $client->invoices->get(Vectors::INVOICE_ID);
        $client->invoices->get(Vectors::INVOICE_ID);
        $this->assertSame(200, $client->getLastResponse()->status);
    }

    public function test_gives_up_after_the_timeout()
    {
        $started = microtime(true);
        $this->expectException(ApiTimeoutException::class);
        try {
            $this->client('slow', ['timeout' => 0.3])->invoices->get(Vectors::INVOICE_ID);
        } finally {
            $this->assertLessThan(2, microtime(true) - $started);
        }
    }

    public function test_tells_a_refused_connection_from_a_sent_request()
    {
        $client = new Client('cpk_x', 'http://127.0.0.1:1', ['max_retries' => 0]);
        try {
            $client->invoices->get(Vectors::INVOICE_ID);
            $this->fail('no error');
        } catch (ApiConnectionException $error) {
            $this->assertFalse($error->wasSent());
        }
    }

    public function test_does_not_follow_a_redirect()
    {
        try {
            $this->client('redirect')->invoices->get(Vectors::INVOICE_ID);
            $this->fail('no error');
        } catch (InvalidResponseException $error) {
            $this->assertSame(302, $error->getStatus());
        }
        $hits = json_decode(file_get_contents("{$this->mock}/hits"), true);
        $this->assertSame(0, $hits['target']);
    }

    public function test_html_page_is_an_invalid_answer()
    {
        try {
            $this->client('html502')->invoices->get(Vectors::INVOICE_ID);
            $this->fail('no error');
        } catch (InvalidResponseException $error) {
            $this->assertSame(502, $error->getStatus());
        }
    }
}
