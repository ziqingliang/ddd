<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/25
 * Time: 下午4:11
 */

namespace lanzhi\ddd\tool\values;


/**
 * Class Property
 * @package lanzhi\ddd\tool\values
 *
 * @property string $name
 * @property string $type
 * @property string $label
 * @property string $description
 * @property bool   $isReadonly
 * @property mixed  $default
 */
class Property
{
    public $name;
    public $type;
    public $label;
    public $description;
    public $isReadonly;
    public $default;
}