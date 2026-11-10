<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Event data.
 *
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayoutEventData extends Model
{
    /**
     * The payout, as in `GET /payouts/{id}`.
     *
     * @var Payout
     */
    public $payout;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->payout = self::one($data, 'payout', Payout::class);
        return $model;
    }
}
