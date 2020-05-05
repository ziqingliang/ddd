<?php

namespace tests\values;

use ziqing\ddd\Value;

/**
 * Class ComponentValue
 * @package tests\values
 *
 * @property string $name
 * @property int[] $ages
 * @property ScalarValue[] $scalarValues
 */
class ComponentValue3 extends Value
{
    public $name;
    public $ages;
    public $scalarValues;

    public static function types(): array
    {
        return [
            'name' => 'string',
            'ages' => ['int'],
            'scalarValues' => [ScalarValue::class],
        ];
    }
}
