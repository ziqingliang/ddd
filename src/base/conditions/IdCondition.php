<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/23
 * Time: 下午12:00
 */

namespace ziqing\ddd\base\conditions;


use ziqing\ddd\base\Condition;

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
