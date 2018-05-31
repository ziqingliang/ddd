<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/31
 * Time: 上午12:41
 */

namespace lanzhi\ddd\mockers;


use lanzhi\ddd\contracts\EventDispatcherInterface;
use lanzhi\ddd\Event;

class EventDispatcherMocker implements EventDispatcherInterface
{
    public function dispatch(Event $event):void
    {
        //nothing here
    }
}
