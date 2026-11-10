<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Balance extends Model
{
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
     * Balance: credited funds minus payouts and fees. Decimal string.
     *
     * @var string
     */
    public $confirmed;

    /**
     * Payments in a block waiting for confirmations. Decimal string.
     *
     * @var string
     */
    public $pending;

    /**
     * Payments in the mempool, not credited yet. Decimal string.
     *
     * @var string
     */
    public $seen;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->chain = self::value($data, 'chain');
        $model->asset = self::value($data, 'asset');
        $model->decimals = self::value($data, 'decimals');
        $model->confirmed = self::value($data, 'confirmed');
        $model->pending = self::value($data, 'pending');
        $model->seen = self::value($data, 'seen');
        return $model;
    }
}
