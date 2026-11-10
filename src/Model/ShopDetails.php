<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class ShopDetails extends Model
{
    /**
     *
     * @var Shop
     */
    public $shop;

    /**
     *
     * @var ShopKey
     */
    public $key;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->shop = self::one($data, 'shop', Shop::class);
        $model->key = self::one($data, 'key', ShopKey::class);
        return $model;
    }
}
