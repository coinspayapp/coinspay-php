<?php

// Generated from the CoinsPay OpenAPI document. Do not edit.

namespace CoinsPay\Model;

/**
 * Unknown fields of newer API versions are kept in toArray().
 */
final class InvoiceItem extends Model
{
    /**
     * Item name.
     *
     * @var string
     */
    public $name;

    /**
     * Quantity. Decimal string.
     *
     * @var string
     */
    public $quantity;

    /**
     * Unit: "pcs", "h"…
     *
     * @var string|null
     */
    public $unit;

    /**
     * Unit price in the invoice currency. Decimal string.
     *
     * @var string
     */
    public $price;

    /**
     * Item total: quantity × price. Decimal string.
     *
     * @var string
     */
    public $amount;

    /**
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        $model = new self($data);
        $model->name = self::value($data, 'name');
        $model->quantity = self::value($data, 'quantity');
        $model->unit = self::value($data, 'unit');
        $model->price = self::value($data, 'price');
        $model->amount = self::value($data, 'amount');
        return $model;
    }
}
