<?php

namespace {{namespace}};


use lanzhi\ddd\Value;

/**
 * Class {{className}}
 * @package {{package}}
 *
{{properties}}
 *
{{defaults}}
 */
class {{className}} extends Value
{
    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:value {value-filename} --sub-domain={sub-domain}
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
     * 不在该列表内的属性将被忽略
     *
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:value {value-filename} --sub-domain={sub-domain}
     *
     * @return array
     * ```php
     * [
     *     'name'=>'名称'
     * ]
     * ```
     */
    public static function labels(): array
    {
        return [
{{static-labels}}
        ];
    }

    /**
     * 可以由命令行工具根据类注解生成
     *
     * Command: domain regen:value {value-filename} --sub-domain={sub-domain}
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
