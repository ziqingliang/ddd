<?php

namespace {{namespace}};


use lanzhi\ddd\Entity;
use lanzhi\ddd\Observer;
use {{entityFullClassName}};

/**
 * Class {{className}}
 * @package {{package}}
 * @internal
 *
 * 用于监控追踪实体 {{entityClassName}} 的变化
 * 需要手动在 {{package}} 子域的 SubDomainProvider 类中注册到要观察的实体中
 * 最终由资源库来触发执行
 *
 */
class {{className}} extends Observer
{
    /**
     * you can define your dependency here
     * {{className}} constructor.
     */
    public function __construct()
    {
    }

    public function init()
    {
        //you can init something here
    }

    /**
     * 当实体创建完毕之后触发
     * @return void
     */
    public function whenCreated(Entity $now):void
    {
        //todo: write your logic after an Entity is created
    }

    /**
     * 当实体更新完毕之后触发
     * 要追踪实体的新旧变化，需要实体相应的资源库支持，需要资源库缓存旧实体
     * @param {{entityClassName}} $now
     * @param {{entityClassName}}|null $old
     */
    public function whenUpdated(Entity $now, Entity $old=null):void
    {
        //todo: write your logic after an Entity is updated
    }

    /**
     * 当删除一个实体之后触发
     * @param {{entityClassName}} $now
     */
    public function whenDeleted(Entity $now):void
    {
        //todo: write your logic after an Entity is deleted
    }
}
