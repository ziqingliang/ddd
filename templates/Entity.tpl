<?php

namespace {{namespace}};


use lanzhi\ddd\Entity;

/**
 * Class {{className}}
 * @package {{package}}
 *
 * @property-read int $id
{{properties}}
 *
{{readonly}}
 *
{{defaults}}
 */
class {{className}} extends Entity
{
    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:entity {entity-filename} --sub-domain={sub-domain}
     *
     * configure like this:
     * ```php
     * [
     *     'id'     => 'int',
     *     'name'   => 'string',
     *     'notice' => Notice::class,
     *     'groups' => [Group::class],
     *     'isPass' => 'bool'
     * ]
     * ```
     * @return array
     */
    public static function types(): array
    {
        return array_merge(parent::labels(), [
{{static-types}}
        ]);
    }

    /**
     * 不在该列表内的属性将被忽略
     *
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:entity {entity-filename} --sub-domain={sub-domain}
     *
     * @return array
     * ```php
     * [
     *     'id'  =>'Id',
     *     'name'=>'名称'
     * ]
     * ```
     */
    public static function labels(): array
    {
        return array_merge(parent::labels(), [
{{static-labels}}
        ]);
    }

    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:entity {entity-filename} --sub-domain={sub-domain}
     *
     * 限定某些属性只读，实体类的 ID 属性是只读的
     * @return array
     */
    public static function readonly(): array
    {
        return array_unique(array_merge(parent::readonly(), [
{{static-readonly}}
        ]));
    }

    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:entity {entity-filename} --sub-domain={sub-domain}
     *
     * 设置实体属性的默认值
     * @return array
     */
    public static function defaults(): array
    {
        return [
{{static-defaults}}
        ];
    }
}
