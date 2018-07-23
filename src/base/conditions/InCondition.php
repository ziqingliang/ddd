<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/7/23
 * Time: 上午11:32
 */

namespace lanzhi\ddd\base\conditions;


use lanzhi\ddd\base\Condition;

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