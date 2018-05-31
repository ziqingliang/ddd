<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午10:46
 */

namespace lanzhi\ddd\base\who;


use lanzhi\ddd\Value;

/**
 * Interface Who
 * @package lanzhi\ddd\who
 *
 * @property int    $id
 * @property string $name
 * @property string $description
 */
abstract class Who extends Value
{
    public static function labels(): array
    {
        return [
            'id'          => 'ID',
            'name'        => '用户名',
            'description' => '用户描述'
        ];
    }

    public static function types(): array
    {
        return [
            'id'          => 'int',
            'name'        => 'string',
            'description' => 'string'
        ];
    }
}