<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/31
 * Time: 上午10:25
 */

namespace ziqing\ddd;

use ziqing\ddd\base\NewInstanceTrait;

/**
 * Class Dependency
 * @package ziqing\ddd
 *
 * 定义系统的外部依赖
 * 如接口封装，依赖服务封装等
 */
abstract class Dependency
{
    use NewInstanceTrait;
}
