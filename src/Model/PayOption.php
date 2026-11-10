<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayOption extends Model
{
    /**
     * Network.
     *
     * @var string
     */
    public $chain;

    /**
     * Asset.
     *
     * @var string
     */
    public $asset;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->chain = self::value($data, 'chain');
        $model->asset = self::value($data, 'asset');
        return $model;
    }
}
