<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午10:46
 */

namespace lanzhi\ddd\base\who;


use lanzhi\ddd\Value;

/**
 * Interface Who
 * @package lanzhi\ddd\who
 *
 * @property int    $id
 * @property string $name
 * @property string $description
 */
abstract class Who extends Value
{
    protected const ID_GUEST   = -1;

    protected const ID_CONSOLE = -12;
    protected const ID_TIMER   = -13;
    protected const ID_JOBBER  = -14;

    protected const ID_AUTHOR  = 0;

    /**
     * @param int $id
     * @param string|null $name
     * @return Author|Console|Guest|Jobber|Timer
     * @throws \Exception
     */
    public static function getById(int $id, string $name=null)
    {
        switch ($id){
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
                return new Author(['id'=>$id, 'name'=>$name]);
        }
    }

    public static function labels(): array
    {
        return [
            'id'          => 'ID',
            'name'        => '用户名',
            'description' => '用户描述'
        ];
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