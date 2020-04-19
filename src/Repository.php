<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 下午6:23
 */

namespace ziqing\ddd;

use ziqing\ddd\base\NewInstanceTrait;

abstract class Repository
{
    use NewInstanceTrait;

    public static function getInstance()
    {
        return NewInstanceTrait::getInstance(true);
    }
}
