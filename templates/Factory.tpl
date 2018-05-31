<?php

namespace {{namespace}};


use lanzhi\ddd\Entity;
use lanzhi\ddd\Factory;
use {{entityFullClassName}};

/**
 * Class {{className}}
 * @package {{package}}
 * @internal
 *
 * 用于构造实体 {{entityClassName}} 的类实例
 *
 */
class {{className}} extends Factory
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
     * @return {{entityClassName}}
     */
    public function build(): Entity
    {
        //todo: write your building logic here
    }
}
