<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Payout extends Model
{
    /**
     * Public id of the payout.
     *
     * @var string
     */
    public $id;

    /**
     * `pending`: created; `submitted`: handed over for execution; `broadcast`: transfers
     * are in the network; `confirmed`: done; `waiting_maturity`: funds are not deep enough
     * to withdraw yet, the payout waits; `failed`: not executed; `partially_completed`:
     * some recipients were paid; `rejected`: refused before start, funds stay on the
     * address. One of PayoutStatus::ALL.
     *
     * @var string
     */
    public $status;

    /**
     * `api`: created by an API request; `auto`: automatic payout by the rule when a
     * payment was credited. One of PayoutOrigin::ALL.
     *
     * @var string
     */
    public $origin;

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
     * Decimal places of the asset: how many base units make one coin.
     *
     * @var int
     */
    public $decimals;

    /**
     * Amount leaving the address: recipients and the platform together. Decimal string.
     *
     * @var string
     */
    public $amount;

    /**
     * The same amount in base units of the asset (satoshi, wei, sun) as an integer string.
     * Integer string in base units of the asset.
     *
     * @var string
     */
    public $amount_base;

    /**
     * Shop address the payout is sent from.
     *
     * @var string
     */
    public $address;

    /**
     * id of the shop receiving address.
     *
     * @var string
     */
    public $address_id;

    /**
     * Payment paid out automatically; `null` for an API payout.
     *
     * @var string|null
     */
    public $deposit_id;

    /**
     * Recipients with the calculation and the actual result.
     *
     * @var PayoutRecipient[]
     */
    public $recipients;

    /**
     * Reason of refusal or failure; `null` if there is none.
     *
     * @var PayoutError|null
     */
    public $error;

    /**
     * Creation time, UTC (ISO 8601). ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * When the payout was handed over for execution. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $submitted_at;

    /**
     * When the payout finished, successfully or not. ISO 8601 date-time in UTC.
     *
     * @var string|null
     */
    public $completed_at;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->status = self::value($data, 'status');
        $model->origin = self::value($data, 'origin');
        $model->chain = self::value($data, 'chain');
        $model->asset = self::value($data, 'asset');
        $model->decimals = self::value($data, 'decimals');
        $model->amount = self::value($data, 'amount');
        $model->amount_base = self::value($data, 'amount_base');
        $model->address = self::value($data, 'address');
        $model->address_id = self::value($data, 'address_id');
        $model->deposit_id = self::value($data, 'deposit_id');
        $model->recipients = self::listOf($data, 'recipients', PayoutRecipient::class);
        $model->error = self::one($data, 'error', PayoutError::class);
        $model->created_at = self::value($data, 'created_at');
        $model->submitted_at = self::value($data, 'submitted_at');
        $model->completed_at = self::value($data, 'completed_at');
        return $model;
    }
}
