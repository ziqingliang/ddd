<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2017/8/10
 * Time: 下午8:26
 */

namespace ziqing\ddd;

use Exception;
use ziqing\ddd\base\Data;

/**
 * Class Entity
 * @package ziqing\ddd
 *
 * 实体抽象类
 *
 * 具有只读的唯一标示，判断两个实体是否相等，只需要判断两个实体是否为同一类，且 id 相等
 * 实体的 id 属性只能在实体创建时被设置，如果一个实体没有 id 属性或者该属性为 null，则表示该实体未曾被持久化过，为无名实体，无名实体与任何实体都不相等，除非其自身
 *
 * @property-read int $id
 *
 */
abstract class Entity extends Data
{
    /**
     * @var int
     */
    private $id;

    /**
     * 如果一个实体还没有唯一标示，则认为此时的实体为无效的
     */
    public function isValid()
    {
        return (bool)$this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 只要两个实体的类型相同，且 ID 相同，则就认为两个实体为同一个实体
     * @param self $entity
     * @return bool
     */
    final public function equalTo($entity): bool
    {
        if ($this === $entity) {
            return true;
        }

        if (
            get_class($entity) == get_called_class() &&
            $this->id &&
            $this->id === $entity->id
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function types(): array
    {
        return [
            'id' => 'int'
        ];
    }

    /**
     * 克隆后的实体与先前的实体虽然已经不是一个对象，但是仍然相等
     * @return Entity
     * @throws Exception
     */
    final public function __clone()
    {
        $arr = $this->toArray();
        return new static($arr);
    }
}
