<?php

namespace tests\values;

use ziqing\ddd\Value;

/**
 * Class ComponentValue
 * @package tests\values
 *
 * @property string[] $names
 * @property int[] $ages
 * @property float[] $weights
 * @property float[] $heights
 * @property bool[] $isAduits
 */
class ComponentValue extends Value
{
    public $names;
    public $ages;
    public $weights;
    public $heights;
    public $isAduits;

    public static function types(): array
    {
        return [
            'names' => ['string'],
            'ages' => ['int'],
            'weights' => ['float'],
            'heights' => ['float'],
            'isAduits' => ['bool']
        ];
    }
}
