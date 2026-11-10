<?php

namespace CoinsPay\Tests;

use CoinsPay\Internal\Operations;
use CoinsPay\Model\Model;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class FixturesTest extends TestCase
{
    const DIR = __DIR__ . '/data/fixtures/';

    protected function set_up()
    {
        parent::set_up();
        if (!is_file(self::DIR . 'getShop.json')) {
            $this->markTestSkipped('no fixtures');
        }
    }

    private function strict(Model $model, array $data, $where)
    {
        $names = array_keys(get_object_vars($model));
        $unknown = array_diff(array_keys($data), $names);
        $this->assertSame([], array_values($unknown), "$where: not in " . get_class($model));
        foreach ($names as $name) {
            $value = $model->$name;
            if ($value instanceof Model) {
                $this->strict($value, $data[$name], "$where.$name");
            } elseif (is_array($value)) {
                foreach ($value as $index => $item) {
                    if ($item instanceof Model) {
                        $this->strict($item, $data[$name][$index], "$where.{$name}[$index]");
                    }
                }
            }
        }
    }

    public function test_answers_decode_strictly()
    {
        foreach (Operations::ALL as $operation => $spec) {
            $data = json_decode(file_get_contents(self::DIR . "$operation.json"), true);
            $class = $spec['returns'];
            if ($spec['page'] !== null) {
                $items = $data[$spec['page']];
            } elseif ($spec['many']) {
                $items = $data[$spec['unwrap']];
            } elseif ($spec['unwrap'] !== null) {
                $items = [$data[$spec['unwrap']]];
            } else {
                $items = [$data];
            }
            foreach ($items as $item) {
                $this->strict($class::fromArray($item), $item, $operation);
            }
        }
    }

    public function test_events_decode_strictly()
    {
        foreach (json_decode(file_get_contents(self::DIR . 'events.json'), true) as $event) {
            $class = Operations::EVENTS[$event['type']];
            $this->strict($class::fromArray($event), $event, $event['type']);
        }
    }
}
