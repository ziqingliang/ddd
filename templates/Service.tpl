<?php

namespace {{namespace}};


use lanzhi\ddd\Service;

/**
 * Class {{className}}
 * @package {{package}}
 *
 * 领域服务类，是业务逻辑的主要承担着
 * 根据业务的复杂程度，必要时使用命名空间，即目录，对业务逻辑进行分层分级
 * 领域服务应该是无状态的，可重入的
 */
class {{className}} extends Service
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

    //todo: define your business logic here
    //... ...
}
