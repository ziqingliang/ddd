<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/23
 * Time: 下午12:00
 */

namespace lanzhi\ddd\base\conditions;


use lanzhi\ddd\base\Condition;

class IdCondition extends Condition
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId():int
    {
        return $this->id;
    }
}