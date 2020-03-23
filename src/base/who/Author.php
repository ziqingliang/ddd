<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午11:15
 */

namespace ziqing\ddd\base\who;


/**
 * Class Author
 * @package ziqing\ddd\base\who
 *
 * 表示已经登陆用户
 */
class Author extends Who
{
    public static function defaults(): array
    {
        return [
            'description' => 'authenticated user'
        ];
    }
}
