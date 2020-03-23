<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/6/12
 * Time: 下午7:38
 */

namespace ziqing\ddd\base\conditions;


class PairsCondition
{
    private $pairs;

    public function __construct(array $pairs)
    {
        $this->pairs = $pairs;
    }

    public function getPairs()
    {
        return $this->pairs;
    }

}
