<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2017/8/10
 * Time: 下午8:42
 */

namespace ziqing\ddd;

use Exception;
use ziqing\ddd\base\Data;

/**
 * Class Value
 * @package ziqing\ddd
 *
 * 值对象，没有唯一标示，一旦创建，禁止修改，只能复制
 * 如果一个值对象所有属性都为 null，则该值对象没有意义
 * 用于表示实体的复杂属性，以及部分方法等的复杂参数
 */
abstract class Value extends Data
{
    /**
     * 判断两个值对象是否相等
     * 仅当两个值对象所属类型相同，且所有属性完全相等时，也认为两个值对象相等
     * @param Data $value
     * @param bool $strict
     * @return bool
     * @throws Exception
     */
    final public function equalTo(Data $value, bool $strict = true): bool
    {
        if (get_class($value) !== get_called_class()) {
            return false;
        }
        $a = $this->toArray();
        $b = $value->toArray();
        if ($strict) {
            return $a === $b;
        } else {
            return $a == $b;
        }
    }
}
