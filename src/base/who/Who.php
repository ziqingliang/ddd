<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/22
 * Time: 上午10:46
 */

namespace ziqing\ddd\base\who;

use ziqing\ddd\Value;

/**
 * Interface Who
 * @package ziqing\ddd\who
 *
 * @property int    $id
 * @property string $name
 * @property string $description
 */
class Who extends Value
{
    public $id;
    public $name;
    public $description;

    const ID_GUEST   = -11;
    const ID_CONSOLE = -12;
    const ID_TIMER   = -13;
    const ID_JOBBER  = -14;

    /**
     * @param int $id
     * @param string|null $name
     * @return Console|Guest|Jobber|Timer|self
     */
    public static function create(int $id, string $name = null)
    {
        switch ($id) {
            case self::ID_GUEST:
                return new Guest();
                break;
            case self::ID_CONSOLE:
                return new Console();
                break;
            case self::ID_TIMER:
                return new Timer();
                break;
            case self::ID_JOBBER:
                return new Jobber();
                break;
            default:
                return new self(['id' => $id, 'name' => $name]);
        }
    }

    public static function types(): array
    {
        return [
            'id'          => 'int',
            'name'        => 'string',
            'description' => 'string'
        ];
    }
}
