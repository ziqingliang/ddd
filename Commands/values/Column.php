<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/29
 * Time: 下午3:05
 */

namespace lanzhi\ddd\tool\values;

/**
 * Class Column
 * @package lanzhi\ddd\tool\values
 */
class Column
{
    public $name;
    public $type;
    public $length;//字符串类型才有
    public $default;
    public $notNull;
    public $comment;
    public $precision;

    public static function getHeader()
    {
        return [
            'name', 'type', 'length', 'default', 'not null', 'comment'
        ];
    }

    public function toArray()
    {
        return [
            $this->name,
            $this->type,
            $this->length,
            $this->default,
            $this->notNull,
            $this->comment
        ];
    }
}