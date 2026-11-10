<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Chain extends Model
{
    /**
     * Network id used in requests.
     *
     * @var string
     */
    public $id;

    /**
     * Human-readable network name.
     *
     * @var string
     */
    public $name;

    /**
     * Network family: the address format. An EVM address is the same in every EVM network.
     * Solana addresses are case-sensitive. One of ChainFamily::ALL.
     *
     * @var string
     */
    public $family;

    /**
     * Native coin of the network: fees are paid in it.
     *
     * @var string
     */
    public $native_symbol;

    /**
     * Depth at which a payment is credited (`payment.confirmed`).
     *
     * @var int
     */
    public $confirmations;

    /**
     * Average block time, ms.
     *
     * @var int
     */
    public $block_time_ms;

    /**
     * `false`: the network is disabled by the administrator; no new addresses, issued ones
     * keep working.
     *
     * @var bool
     */
    public $enabled;

    /**
     * Assets of the network: the coin and tokens.
     *
     * @var ChainAsset[]
     */
    public $assets;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->name = self::value($data, 'name');
        $model->family = self::value($data, 'family');
        $model->native_symbol = self::value($data, 'native_symbol');
        $model->confirmations = self::value($data, 'confirmations');
        $model->block_time_ms = self::value($data, 'block_time_ms');
        $model->enabled = self::value($data, 'enabled');
        $model->assets = self::listOf($data, 'assets', ChainAsset::class);
        return $model;
    }
}
