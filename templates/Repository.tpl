<?php

namespace {{namespace}};


use lanzhi\ddd\base\conditions\IdCondition;
use lanzhi\ddd\Entity;
use lanzhi\ddd\Exceptions\AddFailed;
use lanzhi\ddd\Exceptions\LookupFailed;
use lanzhi\ddd\Exceptions\RemoveFailed;
use lanzhi\ddd\Exceptions\UnknownCondition;
use lanzhi\ddd\Exceptions\UpdateFailed;
use lanzhi\ddd\Repository;
use {{entityFullClassName}};

/**
 * Class {{className}}
 * @package {{package}}
 *
 * 实体 {{entityClassName}} 资源管理器
 */
class {{className}} extends Repository
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
     * 具体实现持久化一个新实体
     * @param {{entityClassName}} $entity
     * @return int 返回自增ID
     * @throws AddFailed
     */
    protected function _add(Entity $entity): int
    {
        //todo: write your persistence logic here
    }

    /**
     * 具体实现从持久化中移除一个已存在实体
     * 如果一个实体本身是未被持久化即无效的，则直接忽略，此类情况不应该抛出异常
     * @param {{entityClassName}} $entity
     * @return true
     * @throws RemoveFailed
     */
    protected function _remove(Entity $entity): bool
    {
        //todo: write your persistence logic here
    }

    /**
     * 具体实现持久化一个已存在实体
     * @param {{entityClassName}} $entity
     * @return true
     * @throws UpdateFailed
     */
    protected function _update(Entity $entity): bool
    {
        //todo: write your persistence logic here
    }

    /**
     * 具体实现从存储中得到一个实体
     * @return {{entityClassName}}[]
     * @throws UnknownCondition
     * @throws LookupFailed
     */
    protected function _get(): array
    {
        $condition = $this->getCondition();
        switch (get_class($condition)){
            //todo: case condition1
            //todo: case condition2
            //... ...
            //todo: case conditionN

            case IdCondition::class:
                //todo: get by Id
                break;
            default:
                throw new UnknownCondition("不支持过滤条件:".get_class($condition));
                break;

        }
        //todo: write other persistence logic here
    }

    /**
     * 具体实现方法
     * @param {{entityClassName}}[] $entity
     * @return {{entityClassName}}[]
     * @throws AddFailed
     */
    protected function _addMany(array $entity): array
    {
        //todo: write your persistence logic here
    }

    /**
     * 具体实现方法
     * 根据筛选条件单次更新多个实体
     * 此时无法通过观察者模式追踪实体变化
     * @param array $data
     * @return void
     * @throws UpdateFailed
     * @throws UnknownCondition
     */
    protected function _updateManyWithCondition(array $data)
    {
        $condition = $this->getCondition();
        switch (get_class($condition)){
            //todo: case condition1
            //todo: case condition2
            //... ...
            //todo: case conditionN
            default:
                throw new UnknownCondition("不支持过滤条件:".get_class($condition));
                break;
        }
        //todo: write other persistence logic here
    }

    /**
     * 具体实现方法
     * 根据筛选条件单次更新多个实体
     * 此时无法通过观察者模式追踪实体变化
     * @return void
     * @throws RemoveFailed
     * @throws UnknownCondition
     */
    protected function _removeMany()
    {
        $condition = $this->getCondition();
        switch (get_class($condition)){
            //todo: case condition1
            //todo: case condition2
            //... ...
            //todo: case conditionN
            default:
                throw new UnknownCondition("不支持过滤条件:".get_class($condition));
                break;
        }
        //todo: write other persistence logic here
    }

    /**
     * 具体实现从存储中计算当前条件下实体的个数
     * @return int
     * @throws LookupFailed
     * @throws UnknownCondition
     */
    protected function _size(): int
    {
        $condition = $this->getCondition();
        switch (get_class($condition)){
            //todo: case condition1
            //todo: case condition2
            //... ...
            //todo: case conditionN
            default:
                throw new UnknownCondition("不支持过滤条件:".get_class($condition));
                break;
        }
        //todo: write other persistence logic here
    }

}
