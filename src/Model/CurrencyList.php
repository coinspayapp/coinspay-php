<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class CurrencyList extends Model
{
    /**
     * Fiat currencies enabled on the platform.
     *
     * @var FiatCurrency[]
     */
    public $fiat;

    /**
     * Cryptocurrencies: the coin of any network. An invoice in a coin is not converted
     * when the buyer pays with the same coin.
     *
     * @var CryptoCurrency[]
     */
    public $crypto;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->fiat = self::listOf($data, 'fiat', FiatCurrency::class);
        $model->crypto = self::listOf($data, 'crypto', CryptoCurrency::class);
        return $model;
    }
}
