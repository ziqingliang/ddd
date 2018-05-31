<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 下午5:59
 */

namespace lanzhi\ddd\contracts;


use lanzhi\ddd\Event;

interface EventDispatcherInterface
{
    public function dispatch(Event $event):void;
}