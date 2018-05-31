<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2017/10/17
 * Time: 下午2:15
 */

namespace lanzhi\ddd\Exceptions;



class DependencyError extends Error
{
    public function __construct($message='', $url='', $params=[], $code = 0, Exception $previous = null)
    {
        $message = sprintf("%s; url:%s; params:%s", $message, $url, json_encode($params, JSON_UNESCAPED_UNICODE));
        parent::__construct($message, $code, $previous);
    }
}