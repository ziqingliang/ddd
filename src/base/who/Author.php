<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午11:15
 */

namespace lanzhi\ddd\base\who;


/**
 * Class Author
 * @package lanzhi\ddd\base\who
 *
 * 表示已经登陆用户
 */
final class Author extends Who
{
    public static function defaults(): array
    {
        return [
            'description' => 'authenticated user'
        ];
    }
}