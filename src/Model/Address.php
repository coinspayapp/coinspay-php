<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Address extends Model
{
    /**
     * Public id of the address.
     *
     * @var string
     */
    public $id;

    /**
     * Network: `tron`, `ethereum`, `bsc`… All values are in [Networks and
     * coins](/docs/networks) and in the `GET /chains` response.
     *
     * @var string
     */
    public $chain;

    /**
     * Address in the network: the buyer sends the payment here.
     *
     * @var string
     */
    public $address;

    /**
     * Your order number: comes back in payments and events. Up to 190 characters.
     *
     * @var string|null
     */
    public $reference;

    /**
     * Payout recipients of this address; `null` means the shop payout rule applies.
     *
     * @var PayoutShare[]|null
     */
    public $payout;

    /**
     * Payment page for the buyer: address, QR code and payments. For an invoice address it
     * is the invoice page.
     *
     * @var string
     */
    public $pay_url;

    /**
     * Invoice the address was issued for; `null` for a standalone address.
     *
     * @var string|null
     */
    public $invoice_id;

    /**
     * Creation time, UTC (ISO 8601). ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->chain = self::value($data, 'chain');
        $model->address = self::value($data, 'address');
        $model->reference = self::value($data, 'reference');
        $model->payout = self::listOf($data, 'payout', PayoutShare::class);
        $model->pay_url = self::value($data, 'pay_url');
        $model->invoice_id = self::value($data, 'invoice_id');
        $model->created_at = self::value($data, 'created_at');
        return $model;
    }
}
