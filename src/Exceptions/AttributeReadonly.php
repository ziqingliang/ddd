<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2017/8/13
 * Time: 下午5:36
 */

namespace lanzhi\ddd\Exceptions;



class AttributeReadonly extends Error
{
    private $format = 'attribute %s readonly; can\'t set it;';

    /**
     * SetReadonlyAttribute constructor.
     * @param string|array $attribute
     */
    public function __construct($attribute)
    {
        if(is_array($attribute)){
            $attribute = implode("::", $attribute);
        }
        $message = sprintf($this->format, $attribute);
        parent::__construct($message, -1, null);
    }


}