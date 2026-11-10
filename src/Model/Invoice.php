<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Invoice extends Model
{
    /**
     * Public id of the invoice.
     *
     * @var string
     */
    public $id;

    /**
     * Invoice number within the shop: 1, 2, 3…
     *
     * @var int
     */
    public $number;

    /**
     * Custom invoice title; with `null` the dashboard names the invoice by its number, and
     * the buyer sees "Order <reference>" or "Payment".
     *
     * @var string|null
     */
    public $title;

    /**
     * Public id of the shop.
     *
     * @var string
     */
    public $shop_id;

    /**
     * `true` for a test invoice issued with a test key `cpk_test_…`. It has no address; a
     * button on the payment page simulates the payment, no money moves.
     *
     * @var bool
     */
    public $test;

    /**
     * `open`: waiting for payment; `paid`: paid; `expired`: expired; `cancelled`:
     * cancelled. One of InvoiceStatus::ALL.
     *
     * @var string
     */
    public $status;

    /**
     * Price currency: fiat (`USD`, `EUR`…) or crypto (`USDT`, `BTC`…).
     *
     * @var string
     */
    public $currency;

    /**
     * `fiat` or `crypto`. One of CurrencyKind::ALL.
     *
     * @var string
     */
    public $currency_kind;

    /**
     * Invoice total in its currency. Decimal string.
     *
     * @var string
     */
    public $amount;

    /**
     * Paid by confirmed payments, in the invoice currency. Decimal string.
     *
     * @var string
     */
    public $amount_paid;

    /**
     * Payments in transit: in a block without enough depth or in the mempool. Decimal
     * string.
     *
     * @var string
     */
    public $amount_pending;

    /**
     * Amount still due: total minus paid and in transit. Decimal string.
     *
     * @var string
     */
    public $amount_due;

    /**
     * Paid above the total; `null` if there is no overpayment. Decimal string.
     *
     * @var string|null
     */
    public $amount_overpaid;

    /**
     * Your order number: comes back in payments and events. Up to 190 characters.
     *
     * @var string|null
     */
    public $reference;

    /**
     * Payment page: send the buyer here.
     *
     * @var string
     */
    public $pay_url;

    /**
     * Paid after the due time.
     *
     * @var bool
     */
    public $paid_late;

    /**
     * Marked as paid manually in the dashboard.
     *
     * @var bool
     */
    public $paid_manually;

    /**
     * Payment deadline, UTC. ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $expires_at;

    /**
     * When the invoice was paid. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $paid_at;

    /**
     * Creation time, UTC (ISO 8601). ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * Items; empty for an amount-only invoice.
     *
     * @var InvoiceItem[]
     */
    public $items;

    /**
     * Accepted coins; `null` means any enabled coin.
     *
     * @var PayOption[]|null
     */
    public $pay_options;

    /**
     * The buyer pays the service fee: it is added to the amount.
     *
     * @var bool
     */
    public $buyer_pays_fee;

    /**
     * The buyer pays the network fee of the future payout.
     *
     * @var bool
     */
    public $buyer_pays_network;

    /**
     * Description for the buyer.
     *
     * @var string|null
     */
    public $description;

    /**
     * Where to return the buyer: the "Back to the shop" button on the payment page after
     * the invoice is paid, expires or is cancelled.
     *
     * @var string|null
     */
    public $return_url;

    /**
     * After payment the page takes the buyer to `return_url` by itself in 5 seconds.
     *
     * @var bool
     */
    public $auto_return;

    /**
     * Where it was created: `api` or `cabinet`. One of InvoiceOrigin::ALL.
     *
     * @var string
     */
    public $origin;

    /**
     * When the invoice expired. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $expired_at;

    /**
     * When the invoice was cancelled. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $cancelled_at;

    /**
     * Payments for the invoice.
     *
     * @var InvoicePayment[]
     */
    public $payments;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->number = self::value($data, 'number');
        $model->title = self::value($data, 'title');
        $model->shop_id = self::value($data, 'shop_id');
        $model->test = self::value($data, 'test');
        $model->status = self::value($data, 'status');
        $model->currency = self::value($data, 'currency');
        $model->currency_kind = self::value($data, 'currency_kind');
        $model->amount = self::value($data, 'amount');
        $model->amount_paid = self::value($data, 'amount_paid');
        $model->amount_pending = self::value($data, 'amount_pending');
        $model->amount_due = self::value($data, 'amount_due');
        $model->amount_overpaid = self::value($data, 'amount_overpaid');
        $model->reference = self::value($data, 'reference');
        $model->pay_url = self::value($data, 'pay_url');
        $model->paid_late = self::value($data, 'paid_late');
        $model->paid_manually = self::value($data, 'paid_manually');
        $model->expires_at = self::value($data, 'expires_at');
        $model->paid_at = self::value($data, 'paid_at');
        $model->created_at = self::value($data, 'created_at');
        $model->items = self::listOf($data, 'items', InvoiceItem::class);
        $model->pay_options = self::listOf($data, 'pay_options', PayOption::class);
        $model->buyer_pays_fee = self::value($data, 'buyer_pays_fee');
        $model->buyer_pays_network = self::value($data, 'buyer_pays_network');
        $model->description = self::value($data, 'description');
        $model->return_url = self::value($data, 'return_url');
        $model->auto_return = self::value($data, 'auto_return');
        $model->origin = self::value($data, 'origin');
        $model->expired_at = self::value($data, 'expired_at');
        $model->cancelled_at = self::value($data, 'cancelled_at');
        $model->payments = self::listOf($data, 'payments', InvoicePayment::class);
        return $model;
    }
}
