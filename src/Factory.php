<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午11:50
 */

namespace ziqing\ddd;

use ziqing\ddd\base\NewInstanceTrait;

/**
 * Class Factory
 * @package ziqing\ddd
 *
 * 领域工厂
 * 通常负责构造复杂的实体类，其输出为一个实体
 */
abstract class Factory
{
    use NewInstanceTrait;

    public static function getInstance()
    {
        return NewInstanceTrait::getInstance(true);
    }
}
