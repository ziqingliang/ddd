<?php

namespace tests\values;

use ziqing\ddd\Value;

/**
 * Class ScalarValue
 * @package tests\values
 *
 * @property int $age
 * @property float $height
 * @property float $weight
 * @property string $name
 * @property bool $isAduit
 */
class ScalarValue extends Value
{
    public $name;
    public $isAduit;
    public $age;
    public $height;
    public $weight;

    public static function types(): array
    {
        return [
            'name' => 'string',
            'isAduit' => 'bool',
            'age' => 'int',
            'height' => 'float',
            'weight' => 'float'
        ];
    }
}
