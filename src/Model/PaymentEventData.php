<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Event data.
 *
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PaymentEventData extends Model
{
    /**
     * The payment, as in `GET /deposits/{id}`.
     *
     * @var Deposit
     */
    public $deposit;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->deposit = self::one($data, 'deposit', Deposit::class);
        return $model;
    }
}
