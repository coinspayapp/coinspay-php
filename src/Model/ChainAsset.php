<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class ChainAsset extends Model
{
    /**
     * Asset ticker.
     *
     * @var string
     */
    public $symbol;

    /**
     * Decimal places of the asset: how many base units make one coin.
     *
     * @var int
     */
    public $decimals;

    /**
     * `true` for the network coin, `false` for a token.
     *
     * @var bool
     */
    public $native;

    /**
     * Token contract address; `null` for the network coin.
     *
     * @var string|null
     */
    public $contract;

    /**
     * `true` for a stablecoin (USDT, USDC): an invoice with `stablecoins_only` accepts
     * these coins.
     *
     * @var bool
     */
    public $stablecoin;

    /**
     * Whether the asset is accepted: `false` if it or its network is disabled.
     *
     * @var bool
     */
    public $enabled;

    /**
     * Minimum payout amount; `null` means no minimum. Decimal string.
     *
     * @var string|null
     */
    public $payout_minimum;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->symbol = self::value($data, 'symbol');
        $model->decimals = self::value($data, 'decimals');
        $model->native = self::value($data, 'native');
        $model->contract = self::value($data, 'contract');
        $model->stablecoin = self::value($data, 'stablecoin');
        $model->enabled = self::value($data, 'enabled');
        $model->payout_minimum = self::value($data, 'payout_minimum');
        return $model;
    }
}
