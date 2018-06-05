<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午11:20
 */

namespace lanzhi\ddd\base\who;


final class Timer extends Who
{
    public function __construct()
    {
        parent::__construct([]);
    }

    public static function defaults(): array
    {
        return [
            'id'          => self::ID_TIMER,
            'name'        => 'timer',
            'description' => 'cron tab timer'
        ];
    }
}