<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午11:20
 */

namespace ziqing\ddd\base\who;

final class Timer extends Who
{
    public static function defaults(): array
    {
        return [
            'id'          => self::ID_TIMER,
            'name'        => 'timer',
            'description' => 'cron tab timer'
        ];
    }
}
