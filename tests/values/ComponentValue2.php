<?php

namespace tests\values;

use ziqing\ddd\Value;

/**
 * Class ComponentValue
 * @package tests\values
 *
 * @property string $name
 * @property string $name2
 * @property int[] $ages
 * @property ScalarValue $scalarValue
 */
class ComponentValue2 extends Value
{
    public $name;
    public $name2;
    public $ages;
    public $scalarValue;

    public static function types(): array
    {
        return [
            'name' => 'string',
            'name2' => 'string',
            'ages' => ['int'],
            'scalarValue' => ScalarValue::class,
        ];
    }
}
