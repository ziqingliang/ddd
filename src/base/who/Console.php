<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午11:15
 */

namespace ziqing\ddd\base\who;

final class Console extends Who
{
    public static function defaults(): array
    {
        return [
            'id'          => self::ID_CONSOLE,
            'name'        => 'console',
            'description' => 'console shell'
        ];
    }
}
