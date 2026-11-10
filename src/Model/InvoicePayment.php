<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class InvoicePayment extends Model
{
    /**
     * Payment id: details in `GET /deposits/{id}`.
     *
     * @var string
     */
    public $deposit_id;

    /**
     * Payment state, as in `Deposit.status`. One of DepositStatus::ALL.
     *
     * @var string
     */
    public $status;

    /**
     * Network: `tron`, `ethereum`, `bsc`… All values are in [Networks and
     * coins](/docs/networks) and in the `GET /chains` response.
     *
     * @var string
     */
    public $chain;

    /**
     * Asset: the network coin (`ETH`, `TRX`, `BTC`…) or a token (`USDT`, `USDC`…). The
     * assets of each network are in [Networks and coins](/docs/networks).
     *
     * @var string
     */
    public $asset;

    /**
     * Payment amount in the coin. Decimal string.
     *
     * @var string
     */
    public $amount;

    /**
     * Transaction hash in the network.
     *
     * @var string
     */
    public $tx_hash;

    /**
     * Part of the invoice covered by the payment, in the invoice currency. Decimal string.
     *
     * @var string
     */
    public $value;

    /**
     * Counted at the rate at detection: it came after the quote expired or in another
     * coin.
     *
     * @var bool
     */
    public $late;

    /**
     * When the payment was detected. ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * When the payment was credited. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $confirmed_at;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->deposit_id = self::value($data, 'deposit_id');
        $model->status = self::value($data, 'status');
        $model->chain = self::value($data, 'chain');
        $model->asset = self::value($data, 'asset');
        $model->amount = self::value($data, 'amount');
        $model->tx_hash = self::value($data, 'tx_hash');
        $model->value = self::value($data, 'value');
        $model->late = self::value($data, 'late');
        $model->created_at = self::value($data, 'created_at');
        $model->confirmed_at = self::value($data, 'confirmed_at');
        return $model;
    }
}
