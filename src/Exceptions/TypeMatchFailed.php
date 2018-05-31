<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2017/8/13
 * Time: 下午5:21
 */

namespace lanzhi\ddd\Exceptions;



class TypeMatchFailed extends Error
{
    private $format = 'set var %s fail; expected type:%s; given type:%s;';

    /**
     * TypeNonMatched constructor.
     * @param string|array $varName
     * @param string $expected
     * @param string $given
     */
    public function __construct($varName, $expected, $given)
    {
        if(is_array($varName)){
            $varName = implode("::", $varName);
        }

        $message = sprintf($this->format, $varName, $expected, $given);
        parent::__construct($message, -1, null);
    }

}