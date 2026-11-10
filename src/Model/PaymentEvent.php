<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class PaymentEvent extends Model
{
    /**
     * Event id: a repeated delivery of the same event has the same id; use it to drop
     * duplicates.
     *
     * @var string
     */
    public $id;

    /**
     * Event type. One of PaymentEventType::ALL.
     *
     * @var string
     */
    public $type;

    /**
     * Event time, UTC. ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * Public id of the shop.
     *
     * @var string
     */
    public $shop_id;

    /**
     * `true` for a test event: sent from the dashboard or about a test invoice. No money
     * arrived, do not touch real orders. The field is signed.
     *
     * @var bool
     */
    public $test;

    /**
     * Event data.
     *
     * @var PaymentEventData
     */
    public $data;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->type = self::value($data, 'type');
        $model->created_at = self::value($data, 'created_at');
        $model->shop_id = self::value($data, 'shop_id');
        $model->test = self::value($data, 'test');
        $model->data = self::one($data, 'data', PaymentEventData::class);
        return $model;
    }
}
