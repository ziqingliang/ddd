<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/29
 * Time: 下午3:05
 */

namespace ziqing\ddd\tool\values;

/**
 * Class Column
 * @package ziqing\ddd\tool\values
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
