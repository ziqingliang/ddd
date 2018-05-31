<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2017/8/13
 * Time: 下午5:29
 */

namespace lanzhi\ddd\Exceptions;



class AttributeUndefined extends Error
{
    private $format = 'attribute %s undefined; can\'t %s it; ';

    const ACCESS_SET = 'set';
    const ACCESS_GET = 'get';

    /**
     * AccessUndefinedAttribute constructor.
     * @param string $accessWay
     * @param string|array $attribute
     */
    public function __construct($accessWay, $attribute)
    {
        if(is_array($attribute)){
            $attribute = implode("::", $attribute);
        }

        $message = sprintf($this->format, $attribute, $accessWay);
        parent::__construct($message, -1, null);
    }

}