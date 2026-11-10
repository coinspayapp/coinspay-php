<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Event data.
 *
 * Unknown fields of newer API versions are kept in toArray().
 */
final class InvoiceEventData extends Model
{
    /**
     * The invoice, as in `GET /invoices/{id}`.
     *
     * @var Invoice
     */
    public $invoice;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->invoice = self::one($data, 'invoice', Invoice::class);
        return $model;
    }
}
