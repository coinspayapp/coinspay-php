<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayoutError extends Model
{
    /**
     * Reason code: `BELOW_MINIMUM`, `INSUFFICIENT_BALANCE`, `FUNDS_NOT_MATURE`,
     * `COSTS_EXCEED_AMOUNT`, `PAYOUT_NOT_VIABLE`, `PAYOUT_FAILED`.
     *
     * @var string
     */
    public $code;

    /**
     * Explanation in English.
     *
     * @var string|null
     */
    public $message;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->code = self::value($data, 'code');
        $model->message = self::value($data, 'message');
        return $model;
    }
}
