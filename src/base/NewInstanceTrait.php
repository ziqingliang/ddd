<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午10:25
 */

namespace lanzhi\ddd\base;


use Illuminate\Container\Container;

/**
 * Class NewInstanceTrait
 * @package lanzhi\ddd\base
 *
 * 通过 laravel 框架的容器获取当前类的实例
 * 帮助类实例化时自动处理其通过构造函数定义的依赖
 */
trait NewInstanceTrait
{
    public static function getInstance(bool $isNew=false)
    {
        $class = get_called_class();
        $container = Container::getInstance();
        if($isNew){
            $instance = $container->make($class);
        }elseif(!$container->has($class)){
            $container->singleton($class, $class);
        }

        $instance = $instance ?? $container->get($class);
        $instance->init();

        return $instance;
    }

    public function init()
    {

    }
}