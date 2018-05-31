<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 下午5:08
 */

namespace lanzhi\ddd;


use Illuminate\Container\Container;
use lanzhi\ddd\base\Data;
use lanzhi\ddd\base\who\Who;
use lanzhi\ddd\contracts\EventDispatcherInterface;
use lanzhi\ddd\Exceptions\Error;
use lanzhi\ddd\mockers\EventDispatcherMocker;

/**
 * Class Event
 * @package lanzhi\ddd
 *
 * 领域事件抽象类
 * 通常用于业务解耦合，将事件以文本消息的方式缓存至消息队列，由订阅程序根据事件的类型等信息再执行其它业务逻辑
 *
 * @property-read string $id   事件的唯一标示，任何一个事件的 id 都不相同
 * @property-read string $name 事件名称，默认与事件类型一样，可用于区分事件类型
 * @property-read string $type 事件类型 即 className
 * @property      Who    $user 事件所属人
 * @property-read string $time
 * @property      array  $properties
 */
abstract class Event extends Data
{
    protected static $eventName;

    /**
     * @return EventDispatcherMocker
     */
    private static function getEventDispatcher()
    {
        $container = Container::getInstance();
        if($container->has(EventDispatcherInterface::class)){
            return $container->get(EventDispatcherInterface::class);
        }else{
            return new EventDispatcherMocker();
        }
    }

    /**
     * @param self $event
     * @throws Error
     */
    public static function trigger(self $event)
    {
        self::getEventDispatcher()->dispatch($event);
    }

    public function init()
    {

    }

    /**
     * @param Who $who
     * @return $this
     * @throws \Exception
     */
    public function setUser(Who $who)
    {
        $this->setOneAttribute('user', $who);
        return $this;
    }

    public function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    public function addProperty(string $name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }

    public function removeProperty(string $name)
    {
        foreach ($this->properties as $key=>$property){
            if($key==$name){
                unset($this->properties[$key]);
            }
        }
    }

    public static function types(): array
    {
        return [
            'id'        => 'string',
            'name'      => 'string',
            'type'      => 'string',
            'user'      => Who::class,
            'time'      => 'string',
            'properties'=> 'array'
        ];
    }

    public static function labels(): array
    {
        return [
            'id'         => 'ID',
            'name'       => '事件名称',
            'type'       => '事件类型',
            'user'       => '事件归属用户',
            'time'       => '事件发生事件',
            'properties' => '事件属性'
        ];
    }

    public static function defaults(): array
    {
        return [
            'id'     => uniqid(),
            'name'   => self::$eventName,
            'type'   => get_called_class(),
            'user'   => Process::whoAmI(),
            'time'   => date('Y-m-d H:i:s'),
            'properties' => []
        ];
    }

    public static function readonly(): array
    {
        return ['id', 'type', 'time'];
    }

    /**
     * @param self $data
     * @return bool
     */
    public function equalTo(Data $data): bool
    {
        if(
            get_class($data) === get_called_class() &&
            $data->id===$this->id
        ){
            return true;
        }else{
            return false;
        }
    }

}
