<?php

namespace CoinsPay\Model;

/**
 * A webhook event of a type this version of the library does not know. Answer
 * 2xx and ignore it, or read $data yourself.
 */
final class UnknownEvent extends Model
{
    /** @var string */
    public $id;

    /** @var string */
    public $type;

    /** @var string */
    public $created_at;

    /** @var string */
    public $shop_id;

    /** @var bool */
    public $test;

    /** @var array<string, mixed>|null */
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
        $model->data = self::value($data, 'data');
        return $model;
    }
}
