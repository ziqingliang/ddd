<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/31
 * Time: 上午10:25
 */

namespace lanzhi\ddd;


use lanzhi\ddd\base\NewInstanceTrait;

/**
 * Class Dependency
 * @package lanzhi\ddd
 *
 * 定义系统的外部依赖
 * 如接口封装，依赖服务封装等
 */
abstract class Dependency
{
    use NewInstanceTrait;
}
