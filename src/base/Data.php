<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2017/8/10
 * Time: 下午10:19
 */

namespace ziqing\ddd\base;

use DateTime;
use Exception;
use ReflectionException;
use ReflectionMethod;
use Illuminate\Container\Container;
use ziqing\ddd\Exceptions\TypeMatchFailed;
use ziqing\ddd\Exceptions\AttributeUndefined;

/**
 * Class Data
 * @package app\base
 *
 * 实体和值对象的抽象父类
 *
 * 属性声明
 * 属性类型配置
 * 属性标签
 *
 * 与数组互相转化
 * 属性完整性校验
 * 最小化属性校验
 *
 * 如果一个属性被定义为非 public，则必须有相对应的 setter 和 getter 方法，否则在严格模式下将会报错
 *
 * 在初始化方法中注入外部依赖
 * @method void init(): void
 */
abstract class Data
{
    private static $isStrict = false;
    /**
     * @var array
     */
    private static $normalTypes = [
        'int' => true,
        'float' => true,
        'double' => true,
        'bool' => true,
        'boolean' => true,
        'string' => true,
        'array' => true
    ];

    /**
     * @var array 默认值
     */
    private $defaultValues = [];
    private $attributeTypes = [];

    /**
     * 是否开启严格模式，严格模式将会在构造以及序列化时对属性进行类型检查
     * @param bool $start
     */
    final public static function setStrictMode(bool $start = false)
    {
        static::$isStrict = $start;
    }

    /**
     * Data constructor.
     * 如果不在 types 内定义，则直接忽略
     * 如果开启强类型检查，则对属性进行赋值时进行类型检查
     * @param array $data
     * @throws
     */
    final public function __construct(array $data = [])
    {
        $this->attributeTypes = static::types();
        $attributes = $this->attributeTypes;

        //此处构造属性
        foreach ($data as $attribute => $value) {
            if ($this->hasAttribute($attribute)) {
                unset($attributes[$attribute]);
                $this->setAttribute($attribute, $value);
            }
        }

        $null = null;
        //处理默认值
        foreach (array_keys($attributes) as $attribute) {
            $this->setAttribute($attribute, $null);
        }

        //使用容器处理依赖
        if (method_exists($this, 'init')) {
            Container::getInstance()->call([$this, 'init']);
        }
    }

    /**
     * 根据数组对已构造的对象属性重新赋值
     * @param array $data
     * @throws Exception
     */
    public function load(array $data)
    {
        foreach ($data as $attribute => $value) {
            if ($this->hasAttribute($attribute)) {
                $this->setAttribute($attribute, $value);
            }
        }
    }

    /**
     * 设置一个属性，如果严格模式开启，则进行类型检查
     * @param string $attribute
     * @param mixed|null $value
     * @throws Exception
     */
    private function setAttribute(string &$attribute, &$value = null)
    {
        if ($value === null) {
            $value = $this->defaultValue($attribute);
        }

        $this->validateAttribute($attribute, $value);
        $this->$attribute = $value;
    }

    /**
     * 校验属性值与属性类型是否相符
     * 如果严格模式未开启，则直接返回
     * 否则，对属性类型进行检查
     * 如果类型不符，首先尝试对其进行类型转换，如果无法转换则抛出异常
     * @param string $attribute
     * @param mixed $value
     * @throws Exception
     */
    private function validateAttribute(string &$attribute, &$value)
    {
        $type = &$this->attributeTypes[$attribute];
        if ($this->isNormalType($type)) {
            $value = $this->castToNormal($type, $value);
        } elseif (is_string($value)) {
            $value = $this->castFromString($type, $value);
        } elseif (is_array($value)) {
            $value = $this->castFromArray($type, $value);
        } elseif ($value && !($value instanceof $type)) {
            throw new TypeMatchFailed([get_called_class(), $attribute], $type, gettype($value));
        }
    }

    /**
     * 设置非 public 属性
     * 如果属性未配置，则直接抛异常
     * 如果没有定义 setter 则直接抛异常
     * @param string $attribute
     * @param mixed $value
     * @throws
     */
    public function __set(string $attribute, $value)
    {
        if (!$this->hasAttribute($attribute)) {
            throw new AttributeUndefined(AttributeUndefined::ACCESS_SET, [get_called_class(), $attribute]);
        }

        //如果没有 setter 方法，则直接报错
        $method = 'set' . $attribute;
        if ($this->hasMethod($method)) {
            $this->$method($value);
        } else {
            throw new Exception(sprintf("set attribute %s without setter", $attribute));
        }
    }

    /**
     * 获取非 public 属性
     * 如果属性未配置，则直接抛异常
     * 如果没有定义 getter 则直接抛异常
     * @param $attribute
     * @return mixed
     * @throws
     */
    public function __get($attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            throw new AttributeUndefined(AttributeUndefined::ACCESS_GET, [get_called_class(), $attribute]);
        }

        $method = 'get' . $attribute;
        if ($this->hasMethod($method)) {
            return $this->$method();
        } else {
            throw new Exception(sprintf("get attribute %s without getter", $attribute));
        }
    }

    /**
     * @param bool $filterNull
     * @return array
     * @throws Exception
     */
    public function toArray(bool $filterNull = true): array
    {
        $data = [];
        foreach ($this->attributeTypes as $attribute => $type) {
            $value = $this->$attribute;
            if (static::$isStrict) {
                $this->validateAttribute($attribute, $value);
            }

            if ($this->isEmpty($value)) {
                if (!$filterNull) {
                    $data[$attribute] = $value;
                }
            } else {
                $type = $this->attributeTypes[$attribute];
                if ($this->isNormalType($type)) {//常规类型
                    $data[$attribute] = $value;
                } elseif (is_string($type)) {//对象类型
                    $data[$attribute] = $value->toArray($filterNull);
                } elseif ($this->isNormalType($type[0])) {//常规数组
                    $data[$attribute] = $value;
                } else {//对象数组
                    $data[$attribute] = [];
                    /** @var self $item */
                    foreach ($value as $key => $item) {
                        $data[$attribute][$key] = $item->toArray($filterNull);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param bool $filterNull
     * @return string
     * @throws Exception
     */
    public function toJson(bool $filterNull = true): string
    {
        $option = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        return json_encode($this->toArray($filterNull), $option);
    }

    //----------------------------------- cast from string or array ----------------------------------------------------

    /**
     * @param mixed $type
     * @return bool
     */
    private function isNormalType($type)
    {
        return is_string($type) && isset(static::$normalTypes[$type]);
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return array|bool|float|int|string
     */
    private function castToNormal($type, $value)
    {
        switch ($type) {
            case 'int':
            case 'integer':
                $value = intval($value);
                break;
            case 'float':
            case 'double':
                $value = floatval($value);
                break;
            case 'bool':
            case 'boolean':
                $value = boolval($value);
                break;
            case 'string':
                $value = strval($value);
                break;
            case 'array':
                if (is_string($value)) {
                    $json = json_decode($value, true);
                    $json && $value = $json;
                }
                $value = (array)$value;
        }

        return $value;
    }

    /**
     * @param mixed $type
     * @param string $value
     * @return array
     * @throws Exception
     */
    private function castFromString($type, string $value)
    {
        $json = json_decode($value, true);
        if (is_array($type)) {
            $data = $this->castFromArray($type, $json);
        } elseif ($json !== false) {
            $data = new $type($json);
        } else {
            $tpl = "invalid string[%s] for type:%s";
            is_array($type) && $type = reset($type);
            throw new Exception(sprintf($tpl, $value, $type));
        }

        return $data;
    }

    /**
     * @param string|array $type
     * @param array $values
     * @return array
     * @throws Exception
     */
    private function castFromArray($type, array $values)
    {
        if (is_string($type)) {
            return new $type($values);
        }

        $data = [];
        $type = reset($type);
        foreach ($values as $key => &$value) {
            if ($this->isNormalType($type)) {
                $data[$key] = $this->castToNormal($type, $value);
            } elseif ($value instanceof $type) {
                $data[$key] = $value;
            } elseif (is_string($value)) {
                $json = json_decode($value, true);
                if ($json) {
                    $data[$key] = new $type($json);
                } else {
                    $tpl = "invalid json string:%s for type:%s";
                    throw new Exception(sprintf($tpl, $value, $type));
                }
            } elseif (is_array($value)) {
                $data[$key] = new $type($value);
            } else {
                $tpl = "invalid value:%s for type:%s";
                throw new Exception(sprintf($tpl, json_encode($value), $type));
            }
        }
        return $data;
    }

    //----------------------------------- assist methods -----------------------------------------------------
    /**
     * @param string $attribute
     * @return bool
     */
    public function hasAttribute(string &$attribute)
    {
        return isset($this->attributeTypes[$attribute]);
    }

    /**
     * @param string $method
     * @return bool
     * @throws ReflectionException
     */
    public function hasMethod(string &$method)
    {
        if (!method_exists($this, $method)) {
            return false;
        }

        $reflection = new ReflectionMethod($this, $method);
        return $reflection->isPublic() && !$reflection->isStatic();
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function isEmpty(&$value)
    {
        return $value === '' || $value === [] || $value === null;
    }

    /**
     * 根据默认值列表或者属性的类型返回属性默认值
     * @param string $attribute
     * @return mixed|null
     */
    private function defaultValue(string &$attribute)
    {
        if (empty($this->defaultValues)) {
            $this->defaultValues = static::defaults();
        }

        //如果默认值已被构造，直接返回
        //如果属性为对象，则 clone 一个返回，避免两次调用返回同一个对象实例导致意外结果
        if (isset($this->defaultValues[$attribute])) {
            $value = $this->defaultValues[$attribute];
            if (is_object($value)) {
                return clone $value;
            } else {
                return $value;
            }
        }

        $type = &$this->attributeTypes[$attribute];
        if (is_array($type)) {//此时属性类型为对象数组或者常规类型数组
            $type = 'array';
        } elseif (!$this->isNormalType($type)) {
            $type = 'object';
        }

        static $defaults = [
            'int' => 0,
            'integer' => 0,
            'float' => 0.0,
            'double' => 0.0,
            'bool' => false,
            'string' => '',
            'array' => [],
            'object' => null
        ];

        $this->defaultValues[$attribute] = $defaults[$type];
        return $this->defaultValues[$attribute];
    }

    abstract public function equalTo(self $data): bool;

    /**
     * 由自动化工具生成
     * 该配置在初始化时判断属性正确性时使用
     * @return array
     *
     * Entity or Value with this property:
     * @property int $id
     * @property string $name
     * @property DateTime $notice
     * @property DateTime[] $groups
     * @property bool $isPass
     *
     * configure like this:
     * ```php
     * [
     *     'id'    =>'int',
     *     'name'  =>'string',
     *     'notice'=>DateTime::class,
     *     'groups'=>[DateTime::class],
     *     'isPass'=>'bool'
     * ]
     * ```
     *
     */
    abstract public static function types(): array;

    /**
     * 由自动化工具生成
     * @return array
     */
    public static function defaults(): array
    {
        return [];
    }
}
