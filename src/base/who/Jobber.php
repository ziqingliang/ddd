<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午11:15
 */

namespace lanzhi\ddd\base\who;


final class Jobber extends Who
{

    public function __construct()
    {
        parent::__construct([]);
    }

    public static function defaults(): array
    {
        return [
            'id'          => -3,
            'name'        => 'jobber',
            'description' => 'temporary job'
        ];
    }

}