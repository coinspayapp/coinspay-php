<?php

namespace CoinsPay\Tests;

use CoinsPay\Client;
use CoinsPay\ErrorCode;
use CoinsPay\Exception\ExceptionInterface;
use CoinsPay\Exception\InvalidArgumentException;
use CoinsPay\Model\Invoice;
use CoinsPay\Model\InvoiceEvent;
use CoinsPay\Model\InvoiceStatus;
use CoinsPay\Model\UnknownEvent;
use CoinsPay\Page;
use CoinsPay\Tests\Support\Vectors;
use CoinsPay\Webhook;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class ClientTest extends TestCase
{
    const ID = Vectors::INVOICE_ID;

    private static function ok(array $body, $status = 200)
    {
        return ['status' => $status, 'body' => $body];
    }

    public function test_base_url_is_the_origin_or_ends_in_api_v1()
    {
        $make = function ($url, $insecure = false) {
            return (new Client('cpk_x', $url, ['allow_insecure_http' => $insecure]))->getBaseUrl();
        };
        $this->assertSame('https://pay.example.com/api/v1', $make('https://pay.example.com'));
        $this->assertSame('https://pay.example.com/api/v1', $make('https://Pay.Example.com/'));
        $this->assertSame('https://pay.example.com/api/v1', $make('https://pay.example.com/api/v1/'));
        $this->assertSame('https://example.com/coinspay/api/v1', $make('https://example.com/coinspay/api/v1'));
        $this->assertSame('https://pay.example.com:8443/api/v1', $make(' https://pay.example.com:8443 '));
        $this->assertSame('http://localhost:3400/api/v1', $make('http://localhost:3400'));
        $this->assertSame('http://host.docker.internal:3400/api/v1', $make('http://host.docker.internal:3400', true));

        foreach (['', 'pay.example.com', 'https://pay.example.com/api', 'https://pay.example.com/api/v2', 'https://pay.example.com/?x=1', 'https://u:p@pay.example.com', 'ftp://pay.example.com', 'http://host.docker.internal:3400'] as $url) {
            try {
                $make($url);
                $this->fail("accepted $url");
            } catch (InvalidArgumentException $error) {
                $this->assertInstanceOf(ExceptionInterface::class, $error);
            }
        }
    }

    public function test_key_is_required_and_never_shown()
    {
        $client = new Client('cpk_test_super_secret', 'https://x.example');
        $this->assertTrue($client->isTestMode());
        $this->assertFalse((new Client('cpk_live', 'https://x.example'))->isTestMode());
        ob_start();
        var_dump($client);
        print_r($client);
        $dump = ob_get_clean();
        $this->assertStringNotContainsString('super_secret', $dump);

        $this->expectException(InvalidArgumentException::class);
        new Client("cpk_a\nb", 'https://x.example');
    }

    public function test_sends_key_version_and_json()
    {
        list($client, $http) = Vectors::client([self::ok(['invoice' => ['id' => self::ID, 'status' => 'open', 'unknown_new_field' => 1]], 201)]);
        $invoice = $client->invoices->create(['currency' => 'USD', 'amount' => '49.90', 'title' => 'Заказ ✓', 'return_url' => 'https://shop.example/a/b']);
        $request = $http->requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertSame('/invoices', $request['path']);
        $this->assertSame('Bearer cpk_test_secret_key_value', $request['headers']['authorization']);
        $this->assertSame('application/json', $request['headers']['content-type']);
        $this->assertMatchesRegularExpression('#^coinspay-php/' . preg_quote(Client::VERSION) . ' php/\d#', $request['headers']['user-agent']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $request['headers']['idempotency-key']);
        $this->assertSame('{"currency":"USD","amount":"49.90","title":"Заказ ✓","return_url":"https://shop.example/a/b"}', $request['body']);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(InvoiceStatus::OPEN, $invoice->status);
        $this->assertSame(1, $invoice->toArray()['unknown_new_field']);
        $this->assertSame('{"id":"' . self::ID . '","status":"open","unknown_new_field":1}', json_encode($invoice));
        $this->assertSame(201, $client->getLastResponse()->status);
        $this->assertFalse($client->getLastResponse()->replayed);
    }

    public function test_payout_needs_an_idempotency_key()
    {
        list($client) = Vectors::client([]);
        $params = ['address_id' => self::ID, 'asset' => 'USDT', 'recipients' => [['address' => 'T1', 'percent' => '100']]];
        foreach ([[], ['idempotency_key' => 'short'], ['idempotency_key' => 'has space inside']] as $options) {
            try {
                $client->payouts->create($params, $options);
                $this->fail('accepted ' . json_encode($options));
            } catch (InvalidArgumentException $error) {
                $this->assertNotEmpty($error->getMessage());
            }
        }
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', Client::newIdempotencyKey());
    }

    public function test_cancel_sends_an_empty_json_object()
    {
        list($client, $http) = Vectors::client([self::ok(['invoice' => ['id' => self::ID, 'status' => 'cancelled']])]);
        $client->invoices->cancel(strtoupper(self::ID));
        $this->assertSame('/invoices/' . self::ID . '/cancel', $http->requests[0]['path']);
        $this->assertSame('{}', $http->requests[0]['body']);
    }

    public function test_ids_are_checked_before_sending()
    {
        list($client, $http) = Vectors::client([]);
        foreach (['..', '../payouts', '', 'not-a-uuid', self::ID . '/cancel', null, 42] as $id) {
            try {
                $client->invoices->get($id);
                $this->fail('accepted ' . var_export($id, true));
            } catch (InvalidArgumentException $error) {
                $this->assertNotEmpty($error->getMessage());
            } catch (\TypeError $error) {
                $this->assertNotEmpty($error->getMessage());
            }
        }
        $this->assertCount(0, $http->requests);
    }

    public function test_amounts_must_be_strings()
    {
        list($client, $http) = Vectors::client([]);
        $cases = [
            'amount' => ['currency' => 'USD', 'amount' => 49.9],
            'items[0].price' => ['currency' => 'USD', 'items' => [['name' => 'A', 'quantity' => '1', 'price' => 1e-7]]],
            'items[1].quantity' => ['currency' => 'USD', 'items' => [['name' => 'A', 'quantity' => '1', 'price' => '1'], ['name' => 'B', 'quantity' => 2, 'price' => '1']]],
        ];
        foreach ($cases as $where => $params) {
            try {
                $client->invoices->create($params);
                $this->fail("accepted a number in $where");
            } catch (InvalidArgumentException $error) {
                $this->assertStringContainsString("$where must be a decimal string", $error->getMessage());
            }
        }
        try {
            $client->addresses->create(['chain' => 'tron', 'payout' => [['address' => 'T1', 'percent' => 100]]]);
            $this->fail('accepted a number as percent');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString('payout[0].percent', $error->getMessage());
        }
        $this->assertCount(0, $http->requests);
    }

    public function test_unknown_parameters_are_typos_unless_extra()
    {
        list($client, $http) = Vectors::client([self::ok(['invoice' => ['id' => self::ID]], 201)]);
        foreach ([['currency' => 'USD', 'ammount' => '1'], ['currency' => 'USD', 'items' => [['name' => 'A', 'qty' => '1', 'price' => '1']]]] as $params) {
            try {
                $client->invoices->create($params);
                $this->fail('accepted ' . json_encode($params));
            } catch (InvalidArgumentException $error) {
                $this->assertStringContainsString('Unknown parameter', $error->getMessage());
            }
        }
        $client->invoices->create(['currency' => 'USD', 'amount' => '1'], ['extra' => ['brand_new' => true]]);
        $this->assertSame('{"currency":"USD","amount":"1","brand_new":true}', $http->requests[0]['body']);
    }

    public function test_query_and_pages()
    {
        list($client, $http) = Vectors::client([
            self::ok(['invoices' => [['id' => 'a'], ['id' => 'b']], 'next_cursor' => 'b0000000-0000-4000-8000-000000000000']),
            self::ok(['invoices' => [['id' => 'c']], 'next_cursor' => null]),
            self::ok(['invoices' => [], 'next_cursor' => null]),
        ]);
        $ids = [];
        foreach ($client->invoices->iterate(['status' => 'paid', 'limit' => 2]) as $invoice) {
            $ids[] = $invoice->id;
        }
        $this->assertSame(['a', 'b', 'c'], $ids);
        $this->assertSame('status=paid&limit=2', $http->requests[0]['query']);
        $this->assertSame('status=paid&limit=2&cursor=b0000000-0000-4000-8000-000000000000', $http->requests[1]['query']);

        $page = $client->invoices->list(['reference' => 'order 1&2']);
        $this->assertInstanceOf(Page::class, $page);
        $this->assertSame('reference=order%201%262', $http->requests[2]['query']);
        $this->assertCount(0, $page);
        $this->assertFalse($page->hasMore());
    }

    public function test_balances_shop_and_raw_requests()
    {
        list($client, $http) = Vectors::client([
            self::ok(['balances' => [['chain' => 'tron', 'asset' => 'USDT', 'decimals' => 6, 'confirmed' => '1', 'pending' => '0', 'seen' => '0']]]),
            self::ok(['shop' => ['id' => 's', 'name' => 'Shop', 'created_at' => '2026-10-29T00:00:00.000Z'], 'key' => ['test' => true, 'access' => 'invoices']]),
            self::ok(['anything' => true]),
        ]);
        $balances = $client->balances->list();
        $this->assertSame('USDT', $balances[0]->asset);
        $shop = $client->shop->get();
        $this->assertSame('invoices', $shop->key->access);
        $this->assertSame('Shop', $shop->shop->name);
        $this->assertSame(['anything' => true], $client->request('GET', '/something/new', ['query' => ['a' => 1]]));
        $this->assertSame('a=1', $http->requests[2]['query']);
        foreach (['../admin', '/a/../b', 'invoices'] as $path) {
            try {
                $client->request('GET', $path);
                $this->fail("accepted $path");
            } catch (InvalidArgumentException $error) {
                $this->assertNotEmpty($error->getMessage());
            }
        }
    }

    public function test_error_codes_by_status()
    {
        $this->assertContains(ErrorCode::KEY_ACCESS_DENIED, ErrorCode::BY_STATUS[403]);
    }

    public function test_webhook_event_types()
    {
        $vector = Vectors::load('signatures')['signHeader'];
        $this->assertSame($vector['header'], Webhook::signatureHeader($vector['body'], $vector['secret'], $vector['timestamp']));

        $event = Webhook::constructEvent($vector['body'], [$vector['header']], $vector['secret'], 300, $vector['timestamp']);
        $this->assertInstanceOf(InvoiceEvent::class, $event);
        $this->assertSame('invoice.paid', $event->type);
        $this->assertSame('paid', $event->data->invoice->status);

        $future = '{"id":"e","type":"refund.created","created_at":"2026-10-29T00:00:00.000Z","shop_id":"s","test":false,"data":{"refund":{}}}';
        $unknown = Webhook::constructEvent($future, Webhook::signatureHeader($future, 's', 100), 's', 300, 100);
        $this->assertInstanceOf(UnknownEvent::class, $unknown);
        $this->assertSame(['refund' => []], $unknown->data);

        $this->expectException(InvalidArgumentException::class);
        Webhook::constructEvent($vector['body'], $vector['header'], '', 300, $vector['timestamp']);
    }
}
