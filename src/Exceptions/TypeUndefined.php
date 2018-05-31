<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2017/8/13
 * Time: 下午5:16
 */

namespace lanzhi\ddd\Exceptions;



class TypeUndefined extends Error
{
    private $format = 'access attribute %s::%s without type definition';

    public function __construct($class, $attribute)
    {
        $message = sprintf($this->format, $class, $attribute);
        parent::__construct($message, -1, null);
    }
}