<?php

namespace CoinsPay\Model;

/**
 * Base of the API objects. Fields are public properties named as in the API
 * (snake_case); toArray() and json_encode() give the object exactly as the API
 * sent it, including fields this version of the library does not know yet.
 */
abstract class Model implements \JsonSerializable
{
    /** @var array<string, mixed> */
    private $raw;

    /**
     * @param array<string, mixed> $raw
     */
    final protected function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return array<string, mixed> The object as the API sent it.
     */
    public function toArray()
    {
        return $this->raw;
    }

    /**
     * @return array<string, mixed>
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->raw;
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo()
    {
        $fields = get_object_vars($this);
        unset($fields['raw']);
        return $fields;
    }

    /**
     * @param array<string, mixed> $data
     * @param string $key
     * @return mixed
     */
    protected static function value(array $data, $key)
    {
        return array_key_exists($key, $data) ? $data[$key] : null;
    }

    /**
     * @param array<string, mixed> $data
     * @param string $key
     * @param string $class
     * @return Model|null
     */
    protected static function one(array $data, $key, $class)
    {
        return isset($data[$key]) && is_array($data[$key]) ? $class::fromArray($data[$key]) : null;
    }

    /**
     * @param array<string, mixed> $data
     * @param string $key
     * @param string $class
     * @return Model[]|null
     */
    protected static function listOf(array $data, $key, $class)
    {
        if (!isset($data[$key]) || !is_array($data[$key])) {
            return null;
        }
        $items = [];
        foreach ($data[$key] as $item) {
            if (is_array($item)) {
                $items[] = $class::fromArray($item);
            }
        }
        return $items;
    }
}
