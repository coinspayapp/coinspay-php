<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayoutShare extends Model
{
    /**
     * Payout recipient address.
     *
     * @var string
     */
    public $address;

    /**
     * Recipient share in percent, up to hundredths. Shares add up to 100. Decimal string.
     *
     * @var string
     */
    public $percent;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->address = self::value($data, 'address');
        $model->percent = self::value($data, 'percent');
        return $model;
    }
}
