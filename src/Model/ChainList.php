<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class ChainList extends Model
{
    /**
     * `mainnet` for real money, `testnet` for test networks. One of NetworkMode::ALL.
     *
     * @var string
     */
    public $network_mode;

    /**
     * Networks of the platform.
     *
     * @var Chain[]
     */
    public $chains;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->network_mode = self::value($data, 'network_mode');
        $model->chains = self::listOf($data, 'chains', Chain::class);
        return $model;
    }
}
