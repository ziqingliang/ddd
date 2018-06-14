<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 下午6:23
 */

namespace lanzhi\ddd;


use Illuminate\Container\Container;
use lanzhi\ddd\base\Condition;
use lanzhi\ddd\base\conditions\IdCondition;
use lanzhi\ddd\base\NewInstanceTrait;
use lanzhi\ddd\contracts\TransactionHandlerInterface;
use lanzhi\ddd\Exceptions\AddFailed;
use lanzhi\ddd\Exceptions\EntityNotFound;
use lanzhi\ddd\Exceptions\Error;
use lanzhi\ddd\Exceptions\LookupFailed;
use lanzhi\ddd\Exceptions\RemoveFailed;
use lanzhi\ddd\Exceptions\TransactionFailed;
use lanzhi\ddd\Exceptions\UpdateFailed;
use lanzhi\ddd\mockers\TransactionHandlerMocker;

abstract class Repository
{
    use NewInstanceTrait;

    private const GC_INTERVAL = 60; //实体最长缓存时间为60秒
    protected const ORDER_DESC = 'desc';
    protected const ORDER_ASC  = 'asc';
    /**
     * @var array [Entity, timestamp] 从实体类中
     */
    private static $entities;
    /**
     * @var bool 当前是否在一个事务中
     */
    private static $isInTransaction = false;
    /**
     * @var string 当前事物标识
     */
    private static $mark;
    /**
     * @var Condition[]
     */
    private $conditions=[];
    /**
     * @var int
     */
    private $from = 0;
    /**
     * @var int
     */
    private $length = 5000;
    /**
     * @var string[]
     */
    private $groupBys = [];
    /**
     * @var array [field, asc|desc]
     */
    private $orderBy  = [];

    public static function getInstance()
    {
        return NewInstanceTrait::getInstance(true);
    }

    /**
     * @return TransactionHandlerInterface
     */
    private static function getTransaction()
    {
        $container = Container::getInstance();
        if($container->has(TransactionHandlerInterface::class)){
            return $container->get(TransactionHandlerInterface::class);
        }else{
            return new TransactionHandlerMocker();
        }
    }

    /**
     * @return bool
     */
    final public static function isInTransaction()
    {
        return self::$isInTransaction;
    }

    /**
     * 开启事务，同时做批次标记
     * 如果当前已经在事务中，则直接返回false
     * @return bool
     */
    final public static function transactionBegin(string $mark):bool
    {
        if(self::isInTransaction()){
            return false;
        }
        try{
            self::getTransaction()->begin();
            self::$isInTransaction = true;
            self::$mark = $mark;
            return true;
        }catch (TransactionFailed $exception){
            return false;
        }
    }

    /**
     * 只提交同批次开启的事务
     * @return bool
     */
    final public static function transactionCommit(string $mark):bool
    {
        try{
            if(self::$mark==$mark){
                self::getTransaction()->commit();
                self::$isInTransaction = false;
                return true;
            }else{
                return false;
            }
        }catch (TransactionFailed $exception){
            return false;
        }
    }

    /**
     * 只回滚同批次开启的事务
     * @return bool
     */
    final public static function transactionRollback(string $mark):bool
    {
        try{
            if(self::$mark==$mark){
                self::getTransaction()->rollback();
                self::$isInTransaction = false;
                return true;
            }else{
                return false;
            }
        }catch (TransactionFailed $exception){
            return false;
        }
    }

    /**
     * 清理缓存的实体类
     */
    private function clear()
    {
        $now = time();
        foreach (self::$entities as $id=>list($entity, $timestamp)){
            if($now-$timestamp > self::GC_INTERVAL){
                unset(self::$entities[$id]);
            }
        }
    }

    /**
     * @param Condition $condition
     * @return $this
     */
    final public function filter(Condition $condition)
    {
        $this->conditions[] = $condition;
        return $this;
    }

    /**
     * @return Condition[]
     */
    public function getConditions():array
    {
        return $this->conditions;
    }

    /**
     * @param int $from
     * @param int $length
     * @return $this
     */
    final public function slice(int $length, int $from=0)
    {
        assert($from>=0 && $length>0 && $length<=1000);
        $this->from   = $from;
        $this->length = $length;
        return $this;
    }

    /**
     * @param string ...$groupBys
     * @return $this
     */
    final public function groupBy(string ...$groupBys)
    {
        $this->groupBys = $groupBys;
        return $this;
    }

    final public function orderBy(string $name, $order=self::ORDER_ASC)
    {
        $this->orderBy = [$name, $order];
        return $this;
    }

    public function getFrom():int
    {
        return $this->from;
    }

    public function getLength():int
    {
        return $this->length;
    }

    /**
     * @return string[]
     */
    public function getGroupBys()
    {
        return $this->groupBys;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return Entity
     * @throws LookupFailed
     * @throws EntityNotFound
     */
    final public function first():Entity
    {
        $this->from   = 0;
        $this->length = 1;

        $entities = $this->slice(1)->get();
        if(empty($entities)){
            throw new EntityNotFound();
        }
        return array_shift($entities);
    }

    /**
     * @return Entity
     * @throws EntityNotFound
     * @throws LookupFailed
     */
    public function last():Entity
    {
        $size = $this->size();
        if($size==0){
            throw new EntityNotFound();
        }
        $entities = $this->slice(1, $size-1)->get();
        return array_pop($entities);
    }

    /**
     * @param int $id
     * @return Entity
     * @throws EntityNotFound
     * @throws LookupFailed
     */
    public function getById(int $id):Entity
    {
        $condition = new IdCondition($id);
        $entity = $this->filter($condition)->first();
        if(!$entity){
            throw new EntityNotFound("id:$id");
        }
        return $entity;
    }

    /**
     * 可能为空数组
     * @return Entity[]
     * @throws LookupFailed
     */
    public function get():array
    {
        $this->clear();

        $entities = $this->_get();
        $now = time();
        foreach ($entities as $entity){
            self::$entities[$entity->id] = [$entity, $now];
        }

        return $entities;
    }

    /**
     * @return int
     * @throws LookupFailed
     */
    public function size():int
    {
        return $this->_size();
    }

    /**
     * 返回经过持久化后的实体
     * @param Entity $entity
     * @return Entity
     * @throws AddFailed
     */
    public function add(Entity $entity):Entity
    {
        assert(!$entity->isValid());

        $id   = $this->_add($entity);
        $data = $entity->toArray();
        $data['id'] = $id;
        $class  = get_class($entity);
        $entity = new $class($data);

        /**
         * @var Entity $entity
         */
        $observers = $entity->getObservers();
        foreach ($observers as $observer){
            $observer->whenCreated($entity);
        }
        return $entity;
    }

    /**
     * @param Entity $entity
     * @return true
     * @throws UpdateFailed
     */
    public function update(Entity $entity):bool
    {
        assert($entity->isValid());

        $this->_update($entity);
        $old = self::$entities[$entity->id] ?? null;

        $observers = $entity->getObservers();
        foreach ($observers as $observer){
            $observer->whenUpdated($entity, $old);
        }
        return true;
    }

    /**
     * @param Entity $entity
     * @throws RemoveFailed
     */
    public function remove(Entity $entity)
    {
        if(!$entity->isValid()){
            return ;
        }

        try{
            $this->_remove($entity);
            $observers = $entity->getObservers();
            foreach ($observers as $observer){
                $observer->whenDeleted($entity);
            }
        }catch (RemoveFailed $exception){
            throw $exception;
        }
    }

    /**
     * 单次添加多个实体，返回多个实体的数字id
     * 只能同时成功或者同时失败
     * 此时无法通过观察者模式追踪实体变化
     * @param Entity[] $entities
     * @return array
     * @throws AddFailed
     */
    public function addMany(array $entities)
    {
        try{
            self::transactionBegin(__METHOD__);
            $ids = $this->_addMany($entities);
            self::transactionCommit(__METHOD__);

            /**
             * @var Entity $entity
             */
            $list = [];
            foreach ($entities as $index=>$entity){
                $data = $entity->toArray();
                $data['id'] = $ids[$index];
                $class = get_class($entity);
                $list[] = new $class($data);
            }
            return $list;
        }catch (\Exception $exception){
            self::transactionRollback(__METHOD__);
            throw $exception;
        }
    }

    /**
     * 根据筛选条件单次更新多个实体
     * 与 filter 一同起作用，如果未曾调用 filter 则直接抛出异常
     * 此时无法通过观察者模式追踪实体变化
     * @param array $data
     * @throws Error
     * @throws UpdateFailed
     */
    public function updateManyWithCondition(array $data)
    {
        if(!$this->conditions){
            throw new Error("禁止针对所有数据执行 update");
        }

        $this->_updateManyWithCondition($data);
    }

    /**
     * 单次更新多个实体
     * 此时无法通过观察者模式追踪实体变化
     * @param Entity[] $entities
     * @throws UpdateFailed
     */
    public function updateMany(array $entities)
    {
        try{
            self::transactionBegin(__METHOD__);
            foreach ($entities as $entity){
                $this->_update($entity);
            }
            self::transactionCommit(__METHOD__);
        }catch (\Exception $exception){
            self::transactionRollback(__METHOD__);
            throw $exception;
        }
    }

    /**
     * 根据筛选条件单次更新多个实体
     * 与 filter 一同起作用，如果未曾调用 filter 则直接抛出异常
     * 此时无法通过观察者模式追踪实体变化
     * @throws Error
     * @throws RemoveFailed
     */
    public function removeMany()
    {
        if(!$this->conditions){
            throw new Error("禁止针对所有数据执行 delete");
        }

        $this->_removeMany();
    }

    /**
     * 具体实现从存储中得到一个实体
     * @return Entity[] 当找不到记录的时候返回空数组
     * @throws LookupFailed
     */
    abstract protected function _get():array;

    /**
     * 具体实现从存储中计算当前条件下实体的个数
     * @return int
     * @throws LookupFailed
     */
    abstract protected function _size():int;

    /**
     * 具体实现持久化一个新实体
     * @param Entity $entity
     * @return int 返回自增ID
     * @throws AddFailed
     */
    abstract protected function _add(Entity $entity):int;

    /**
     * 具体实现持久化一个已存在实体
     * @param Entity $entity
     * @return true
     * @throws UpdateFailed
     */
    abstract protected function _update(Entity $entity):bool;

    /**
     * 具体实现从持久化中移除一个已存在实体
     * 如果一个实体本身是未被持久化即无效的，则直接忽略，此类情况不应该抛出异常
     * @param Entity $entity
     * @return true
     * @throws RemoveFailed
     */
    abstract protected function _remove(Entity $entity):bool;

    /**
     * 具体实现方法
     * @param array $entity
     * @return array
     * @throws AddFailed
     */
    abstract protected function _addMany(array $entity):array;

//    /**
//     * 具体实现方法
//     * @param Entity[] $entities
//     * @return void
//     * @throws UpdateFailed
//     */
//    abstract protected function _updateMany(array $entities);

    /**
     * @param array $data
     * @return void
     * @throws UpdateFailed
     */
    abstract protected function _updateManyWithCondition(array $data);

    /**
     * 具体实现方法
     * @return void
     * @throws RemoveFailed
     */
    abstract protected function _removeMany();

}
