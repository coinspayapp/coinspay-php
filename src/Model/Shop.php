<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class Shop extends Model
{
    /**
     * Public id of the shop.
     *
     * @var string
     */
    public $id;

    /**
     * Shop name.
     *
     * @var string
     */
    public $name;

    /**
     * Creation time, UTC (ISO 8601). ISO 8601 date-time in UTC.
     *
     * @var string
     */
    public $created_at;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->id = self::value($data, 'id');
        $model->name = self::value($data, 'name');
        $model->created_at = self::value($data, 'created_at');
        return $model;
    }
}
