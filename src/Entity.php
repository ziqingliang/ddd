<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2017/8/10
 * Time: 下午8:26
 */

namespace ziqing\ddd;


use ziqing\ddd\base\Data;
use ziqing\ddd\base\who\Who;

/**
 * Class Entity
 * @package ziqing\ddd
 *
 * 实体抽象类
 *
 * 具有只读的唯一标示，判断两个实体是否相等，只需要判断两个实体是否为同一类，且 id 相等
 * 实体的 id 属性只能在实体创建时被设置，如果一个实体没有 id 属性或者该属性为 null，则表示该实体未曾被持久化过，为无名实体，无名实体与任何实体都不相等，除非其自身
 *
 * @property-read int $id
 *
 * @property-read string $createdAt
 * @property-read Who    $creator
 *
 * @property-read string $updatedAt
 * @property-read Who    $updater
 */
abstract class Entity extends Data
{
    /**
     * @var Observer[]
     */
    private $observers = [];

    /**
     * @param Observer $observer
     */
    public function registerObserver(Observer $observer)
    {
        $this->observers[] = $observer;
    }

    /**
     * @param Observer $observers
     */
    public function removeObserver(Observer $observer)
    {
        foreach ($this->observers as $key => $item){
            if($observer===$item){
                unset($this->observers[$key]);
            }
        }
    }

    /**
     * 如果一个实体还没有唯一标示，则认为此时的实体为无效的
     */
    public function isValid()
    {
        return (bool)$this->id;
    }

    /**
     * @return Observer[]
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * 只要两个实体的类型相同，且 ID 相同，则就认为两个实体为同一个实体
     * @param Entity $entity
     * @return bool
     */
    final public function equalTo(Data $entity):bool
    {
        if($this===$entity){
            return true;
        }

        if(get_class($entity)==get_called_class() && $this->id && $this->id===$entity->id){
            return true;
        }

        return false;
    }

    public function __set(string $attribute, $value)
    {
        parent::__set($attribute, $value);
        $this->setOneAttribute('updatedAt', date('Y-m-d H:i:s'));
        $this->setOneAttribute('updater', Process::whoAmI());
    }

    public static function types(): array
    {
        return [
            'id'        => 'int',
            'createdAt' => 'string',
            'updatedAt' => 'string',
            'creator'   => Who::class,
            'updater'   => Who::class
        ];
    }

    public static function labels(): array
    {
        return [
            'id'        => 'ID',
            'createdAt' => '创建时间',
            'updatedAt' => '最近更新时间',
            'creator'   => '创建人',
            'updater'   => '最近更新人'
        ];
    }

    /**
     * 由自动化工具生成
     * id 属性只读，只能在实体类实例化时赋值
     * @return array
     */
    public static function readonly():array
    {
        return ['id', 'createdAt', 'updatedAt', 'creator', 'updater'];
    }

    public static function defaults(): array
    {
        return [
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s'),
            'creator'   => Process::whoAmI(),
            'updater'   => Process::whoAmI()
        ];
    }

    /**
     * 克隆后的实体与先前的实体虽然已经不是一个对象，但是仍然相等
     * @return Entity
     * @throws \Exception
     */
    final public function __clone()
    {
        $arr = $this->toArray();
        return new static($arr);
    }

}
