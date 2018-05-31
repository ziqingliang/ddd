<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/22
 * Time: 上午11:23
 */

namespace lanzhi\ddd;


use lanzhi\ddd\base\who\Guest;
use lanzhi\ddd\Exceptions\Error;
use lanzhi\ddd\base\who\Who;
use lanzhi\ddd\base\NewInstanceTrait;

/**
 * Class Process
 * @package lanzhi\ddd
 *
 * 领域业务过程
 * 表示一个完整的业务过程或者一个复杂业务过程的一个阶段
 *
 * 一般而言，如果一个业务过程比较简单，参与用户角色相对恒定，只是与用户之间的交互可能分作多个步骤，此时
 * 应该使用一个 Process 实例来表示整个业务过程，使用独立的 public 方法表示每个步骤，方法应该自上而下
 * 体现各个步骤发生的顺序
 *
 * 如果一个完整的业务过程比较复杂，参与其中的角色较多，根据参与其中的角色的变化，有必要对其进行阶段划分
 * 此时应该通过目录，即使用命名空间，表示该复杂业务过程，其中每个阶段应该归档在该目录之下，使用一个
 * Process 实例表示，此时每个阶段依然可能需要同用户进行多次交互，每次交互作为一个步骤，同上使用一个
 * Public 方法表示，方法定义顺序应体现步骤发生顺序
 */
abstract class Process
{
    use NewInstanceTrait;

    /**
     * @var Who
     */
    private static $who;

    /**
     * @throws Error
     */
    public function init()
    {
        if(!self::$who){
            throw new Error("必须在初始化之前设置当前用例的发起者");
        }
    }

    public static function Im(Who $who)
    {
        self::$who = $who;
    }

    /**
     * @return Who
     */
    public static function whoAmI()
    {
        if(self::$who){
            return self::$who;
        }else{
            return new Guest();
        }
    }
}
