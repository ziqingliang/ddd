<?php

namespace {{namespace}};

use ziqing\ddd\Value;

/**
 * Class {{className}}
 * @package {{package}}
 *
{{properties}}
 *
 */
class {{className}} extends Value
{
{{propertie-defines}}
    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: ddd regen:value {value-file-path}
     *
     * configure like this:
     * ```php
     * [
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
        return [
{{static-types}}
        ];
    }

    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: ddd regen:value {value-file-path}
     *
     * 设置实体属性的默认值
     * @return array
     */
    public static function defaults(): array
    {
        return [
            //you cann add defaults here
        ];
    }
}
