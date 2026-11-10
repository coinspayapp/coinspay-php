<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class ShopKey extends Model
{
    /**
     * `true` for a test key `cpk_test_…`: test invoices only.
     *
     * @var bool
     */
    public $test;

    /**
     * Key access: `invoices` covers invoices, the shop, networks and price currencies;
     * `full` adds addresses, payments, balances and payouts. One of KeyAccess::ALL.
     *
     * @var string
     */
    public $access;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->test = self::value($data, 'test');
        $model->access = self::value($data, 'access');
        return $model;
    }
}
