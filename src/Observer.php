<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 下午12:04
 */

namespace lanzhi\ddd;

use lanzhi\ddd\base\NewInstanceTrait;


/**
 * Class Observer
 * @package lanzhi\ddd
 *
 * 用于监控追踪实体的变化
 * 观察者需要注册到要观察的实体中，最终由资源库来触发执行
 */
abstract class Observer
{
    use NewInstanceTrait;

    public static function getInstance()
    {
        return NewInstanceTrait::getInstance(true);
    }

    /**
     * 当实体创建完毕之后触发
     * @return void
     */
    abstract public function whenCreated(Entity $now):void;

    /**
     * 当实体更新完毕之后触发
     * 要追踪实体的新旧变化，需要实体相应的资源库支持，需要资源库缓存旧实体
     * @param Entity $now
     * @param Entity|null $old
     */
    abstract public function whenUpdated(Entity $now, Entity $old=null):void;

    /**
     * 当删除一个实体之后触发
     * @param Entity $now
     */
    abstract public function whenDeleted(Entity $now):void;
}