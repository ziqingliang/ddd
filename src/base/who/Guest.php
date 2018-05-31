<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午10:52
 */

namespace lanzhi\ddd\base\who;


final class Guest extends Who
{

    /**
     * Guest constructor
     */
    public function __construct()
    {
        parent::__construct([]);
    }

    public static function defaults(): array
    {
        return [
            'id'          => 0,
            'name'        => 'guest',
            'description' => 'guest, not authenticated'
        ];
    }
}