<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Event data.
 *
 * Unknown fields of newer API versions are kept in toArray().
 */
final class TestEventData extends Model
{
    /**
     * Test event text.
     *
     * @var string
     */
    public $message;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->message = self::value($data, 'message');
        return $model;
    }
}
