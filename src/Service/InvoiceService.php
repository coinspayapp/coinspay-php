<?php

namespace CoinsPay\Service;

use CoinsPay\Internal\Requestor;
use CoinsPay\Model\Invoice;
use CoinsPay\Model\InvoiceSummary;
use CoinsPay\Page;

/**
 * Invoices: a payment page for an amount, paid in any coin the shop accepts.
 */
final class InvoiceService extends AbstractService
{
    /**
     * Creates an invoice; send the buyer to its pay_url.
     *
     * @param array<string, mixed> $params currency, amount or items, reference, return_url, title and others
     * @param array<string, mixed> $options idempotency_key (your order number is a good one; without it
     *        the library makes a random key for its own retries), extra, timeout, max_retries, headers
     * @return Invoice
     */
    public function create(array $params, array $options = [])
    {
        $key = isset($options['idempotency_key'])
            ? Requestor::checkIdempotencyKey($options['idempotency_key'])
            : Requestor::newIdempotencyKey();
        return $this->client->call('createInvoice', ['params' => $params, 'idempotency_key' => $key, 'options' => $options]);
    }

    /**
     * One page, newest first.
     *
     * @param array<string, mixed> $params status, reference, cursor, limit
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Page<InvoiceSummary>
     */
    public function list(array $params = [], array $options = [])
    {
        return $this->client->call('listInvoices', ['params' => $params, 'options' => $options]);
    }

    /**
     * All records that match, page after page: foreach ($client->invoices->iterate() as $invoice).
     *
     * @param array<string, mixed> $params status, reference, cursor, limit
     * @param array<string, mixed> $options
     * @return \Generator<int, InvoiceSummary>
     */
    public function iterate(array $params = [], array $options = [])
    {
        return $this->iterateList('listInvoices', $params, $options);
    }

    /**
     * @param string $id
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Invoice
     */
    public function get($id, array $options = [])
    {
        return $this->client->call('getInvoice', ['id' => $id, 'options' => $options]);
    }

    /**
     * Cancels an open invoice; one that is no longer open gives ConflictException with code INVOICE_NOT_OPEN.
     *
     * @param string $id
     * @param array<string, mixed> $options timeout, max_retries, headers
     * @return Invoice
     */
    public function cancel($id, array $options = [])
    {
        return $this->client->call('cancelInvoice', ['id' => $id, 'options' => $options]);
    }
}
