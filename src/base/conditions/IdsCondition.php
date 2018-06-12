<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/6/12
 * Time: 下午7:38
 */

namespace lanzhi\ddd\base\conditions;


use lanzhi\ddd\base\Condition;

class IdsCondition extends Condition
{
    private $ids;

    /**
     * IdsCondition constructor.
     * @param int[] $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * @return int[]
     */
    public function getIds():array
    {
        return $this->ids;
    }

}
