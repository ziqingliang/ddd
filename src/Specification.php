<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 下午12:00
 */

namespace lanzhi\ddd;


use lanzhi\ddd\base\NewInstanceTrait;

/**
 * Class Specification
 * @package lanzhi\ddd
 *
 * 领域规约
 * 用于显式定义一些重要的领域规则，这些规则通常由多个布尔判断通过逻辑谓词组合而成
 */
abstract class Specification
{
    use NewInstanceTrait;

}