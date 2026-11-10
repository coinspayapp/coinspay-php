<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PayoutRecipient extends Model
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
     * Amount actually sent. Decimal string.
     *
     * @var string|null
     */
    public $sent;

    /**
     * Hash of the transfer to the recipient.
     *
     * @var string|null
     */
    public $tx_hash;

    /**
     * Transfer state: `pending`, `signed`, `broadcast`, `confirmed`, `failed`.
     *
     * @var string|null
     */
    public $status;

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
        $model->sent = self::value($data, 'sent');
        $model->tx_hash = self::value($data, 'tx_hash');
        $model->status = self::value($data, 'status');
        return $model;
    }
}
