<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/7/23
 * Time: 上午11:32
 */

namespace ziqing\ddd\base\conditions;


use ziqing\ddd\base\Condition;

class InCondition extends Condition
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $set;

    /**
     * InCondition constructor.
     * @param string $name
     * @param array $set
     */
    public function __construct(string $name, array $set)
    {
        $this->name = $name;
        $this->set  = $set;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function getSet():array
    {
        return $this->set;
    }
}
