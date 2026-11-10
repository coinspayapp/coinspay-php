<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class CryptoCurrency extends Model
{
    /**
     * Code: the `currency` value in `POST /invoices`.
     *
     * @var string
     */
    public $code;

    /**
     * Decimal places of the invoice prices and amount.
     *
     * @var int
     */
    public $scale;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->code = self::value($data, 'code');
        $model->scale = self::value($data, 'scale');
        return $model;
    }
}
