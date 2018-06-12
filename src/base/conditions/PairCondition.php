<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/6/12
 * Time: 下午7:38
 */

namespace lanzhi\ddd\base\conditions;


use lanzhi\ddd\base\Condition;

class PairCondition extends Condition
{
    private $name;
    private $value;
    /**
     * PairCondition constructor.
     * @param string $name
     * @param string|number|bool $value
     */
    public function __construct(string $name, $value)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

}
