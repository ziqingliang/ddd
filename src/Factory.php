<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午11:50
 */

namespace lanzhi\ddd;


use lanzhi\ddd\base\NewInstanceTrait;


/**
 * Class Factory
 * @package lanzhi\ddd
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

    abstract public function build():Entity;
}