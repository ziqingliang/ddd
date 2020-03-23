<?php

namespace {{namespace}};


use ziqing\ddd\Event;

/**
 * Class {{className}}
 * @package {{package}}
 *
 * 领域事件类，通常用于业务解耦合，将事件以文本消息的方式缓存至消息队列，由订阅程序
 * 根据事件的类型等信息再执行其它业务逻辑，如将事件交给分析系统，以便做用户行为分析
 * 
 * todo: define your explanation here
 */
class {{className}} extends Event
{
    /**
     * you can define your Event name here
     * @var string
     */
    protected static $eventName = '{{className}}';

    //normally, you don't need write anything here.
}
