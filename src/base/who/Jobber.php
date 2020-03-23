<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午11:15
 */

namespace ziqing\ddd\base\who;


final class Jobber extends Who
{

    public function __construct()
    {
        parent::__construct([]);
    }

    public static function defaults(): array
    {
        return [
            'id'          => self::ID_JOBBER,
            'name'        => 'jobber',
            'description' => 'temporary job'
        ];
    }

}
