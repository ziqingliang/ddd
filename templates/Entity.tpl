<?php

namespace {{namespace}};


use ziqing\ddd\Entity;

/**
 * Class {{className}}
 * @package {{package}}
 *
{{properties}}
 *
{{defaults}}
 */
class {{className}} extends Entity
{
{{propertie-defines}}

    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: ddd regen:entity {entity-filename} --sub-domain={sub-domain}
     *
     * configure like this:
     * ```php
     * [
     *     'id'     => 'int',
     *     'name'   => 'string',
     *     'notice' => DateTime::class,
     *     'groups' => [DateTime::class],
     *     'isPass' => 'bool'
     * ]
     * ```
     * @return array
     */
    public static function types(): array
    {
        return [
{{static-types}}
        ];
    }

    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: ddd regen:entity {entity-filename} --sub-domain={sub-domain}
     *
     * 设置实体属性的默认值
     * @return array
     */
    public static function defaults(): array
    {
        return array_merge(parent::defaults(), [
{{static-defaults}}
        ]);
    }
}
