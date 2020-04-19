<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午11:47
 */

namespace ziqing\ddd;

use ziqing\ddd\base\NewInstanceTrait;

/**
 * Class Service
 * @package ziqing\ddd
 *
 * 领域服务抽象类
 * 领域服务类，是业务逻辑的主要承担着
 * 根据业务的复杂程度，必要时使用命名空间，即目录，对业务逻辑进行分层分级
 * 领域服务应该是无状态的，可重入的
 */
abstract class Service
{
    use NewInstanceTrait;
}
