<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayoutQuoteRecipient extends Model
{
    /**
     * Recipient address.
     *
     * @var string
     */
    public $address;

    /**
     * Share if recipients are set by percent. Decimal string.
     *
     * @var string|null
     */
    public $percent;

    /**
     * Requested amount if recipients are set by amount (or `MAX`).
     *
     * @var string|null
     */
    public $requested;

    /**
     * Amount for the recipient after deductions; `null` until calculated. Decimal string.
     *
     * @var string|null
     */
    public $amount;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->address = self::value($data, 'address');
        $model->percent = self::value($data, 'percent');
        $model->requested = self::value($data, 'requested');
        $model->amount = self::value($data, 'amount');
        return $model;
    }
}
