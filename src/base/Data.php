<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2017/8/10
 * Time: 下午10:19
 */

namespace lanzhi\ddd\base;

use lanzhi\ddd\Exceptions\TypeMatchFailed;
use lanzhi\ddd\Exceptions\TypeUndefined;
use lanzhi\ddd\Exceptions\AttributeReadonly;
use lanzhi\ddd\Exceptions\AttributeUndefined;
use lanzhi\ddd\Exceptions\UnSupported;


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
 */
abstract class Data
{
    /**
     * @var Mandate
     */
    private $mandate;

    /**
     * Data constructor.
     * @param array $data
     * @throws \Exception
     */
    public function __construct(array $data=[])
    {
        $this->mandate = new Mandate();

        //设置默认值
        foreach ($this->attributes() as $attribute){
            if(!isset($data[$attribute]) || $this->isEmpty($data[$attribute])){
                $data[$attribute] = $this->defaultValue($attribute);
            }
        }

        foreach ($data as $attribute=>$value){
            //如果没有在 labels 内显式声明，则忽略
            if(!$this->hasAttribute($attribute)){
                continue;
            }

            $this->setOneAttribute($attribute, $value);
        }

        $this->init();
    }

    public function init()
    {

    }

    protected function getMandate()
    {
        return $this->mandate;
    }

    /**
     * @param array $data
     */
    public function load(array $data)
    {
        foreach ($data as $attribute=>$value){
            $this->$attribute = $value;
        }
    }

    /**
     * @param string $attribute
     * @param mixed  $value
     * @throws \Exception
     */
    public function __set(string $attribute, $value)
    {
        //如果没有在 labels 内显式声明，则报错
        if(!$this->hasAttribute($attribute)){
            throw new AttributeUndefined(AttributeUndefined::ACCESS_SET, [get_called_class(), $attribute]);
        }

        if($this->isReadonly($attribute)){
            throw new AttributeReadonly([get_called_class(), $attribute]);
        }

        $setter = 'set' . $attribute;
        if($this->hasMethod($setter)){
            call_user_func([$this, $setter], $value);
        }else{
            $this->setOneAttribute($attribute, $value);
        }
    }

    /**
     * 不再支持 getter 方法
     * @param $attribute
     * @return mixed
     * @throws \Exception
     */
    public function &__get($attribute)
    {
        if(!$this->hasAttribute($attribute)){
            throw new AttributeUndefined(AttributeUndefined::ACCESS_GET, [get_called_class(), $attribute]);
        }

        return $this->mandate->$attribute;
    }

    /**
     * 内部使用，设置一个属性值，不经过setter方法
     * @param string $attribute
     * @param $value
     * @throws \Exception
     */
    protected function setOneAttribute(string $attribute, $value)
    {
        $type = $this->attributeType($attribute);
        if($type===null) {
            throw new TypeUndefined(get_called_class(), $attribute);
        }

        if($this->isNormalType($type)){
            $value = $this->castToNormal($type, $value);
        }elseif(is_string($value)){
            $value = $this->castFromString($type, $value);
        }elseif(is_array($value)){
            $value = $this->castFromArray($type, $value);
        }elseif($value && !($value instanceof $type)){
            throw new TypeMatchFailed([get_called_class(), $attribute], $type, gettype($value));
        }

        $this->mandate->$attribute = $value;
    }

    /**
     * @param array|null $attributes 需要验证的属性，当该参数不为null时，$mustIntact参数无效
     * @throws UnSupported
     */
    public function validate(array $attributes=null)
    {
        throw new UnSupported("暂不支持校验功能");
    }

    /**
     * @param bool $filterNull
     * @return array
     */
    public function toArray($filterNull=true):array
    {
        $data = [];
        foreach ($this->attributes() as $attribute){
            if($this->isEmpty($this->mandate->$attribute)){
                if($filterNull){
                }else{
                    $data[$attribute] = null;
                }
            }else{
                $type = $this->attributeType($attribute);
                if($this->isNormalType($type)){
                    $data[$attribute] = $this->mandate->$attribute;
                }elseif(is_array($this->mandate->$attribute)){
                    $temp = [];
                    /**
                     * @var Data $value
                     */
                    foreach ($this->mandate->$attribute as $key=>$value){
                        $temp[$key] = $value->toArray($filterNull);
                    }
                    $data[$attribute] = $temp;
                }else{
                    $data[$attribute] = $this->mandate->$attribute->toArray($filterNull);
                }
            }
        }

        return $data;
    }

    //----------------------------------- cast from string or array -----------------------------------------------------
    /**
     * @var array
     */
    private $normalTypes = [
        'int'   =>true,
        'float' =>true,
        'double'=>true,
        'bool'  =>true,
        'boolean'=>true,
        'string'=>true,
        'array' =>true
    ];

    /**
     * @param $type
     * @return bool
     */
    private function isNormalType($type)
    {
        return is_string($type) && isset($this->normalTypes[$type]);
    }

    /**
     * @param $type
     * @param $value
     * @return array|bool|float|int|string
     */
    private function castToNormal($type, $value)
    {
        switch ($type){
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
                if(is_string($value)){
                    $json = json_decode($value, true);
                    $json && $value=$json;
                }
                $value = (array)$value;
        }

        return $value;
    }

    /**
     * @param string|array $type
     * @param string $value
     * @return array
     * @throws \Exception
     */
    private function castFromString($type, string $value)
    {
        $json = json_decode($value, true);
        if(is_array($type)){
            $data = $this->castFromArray(reset($type), $json);
        }elseif($json){
            $data = new $type($json);
        }else{
            throw new \Exception(sprintf(
                "invalid string[%s] for type:%s", $value,
                is_array($type) ? reset($type) : $type
            ));
        }

        return $data;
    }

    /**
     * @param string|array $type
     * @param array $values
     * @return array
     * @throws \Exception
     */
    private function castFromArray($type, array $values)
    {
        $data = [];
        if(is_string($type)){
            $data = new $type($values);
        }else{
            $type = reset($type);
            foreach ($values as $key=>$value){
                if($value instanceof $type){
                    $data[$key] = $value;
                }elseif(is_string($value)){
                    $json = json_decode($value, true);
                    if($json){
                        $data[$key] = new $type($json);
                    }else{
                        throw new \Exception(sprintf(
                            "invalid json string:%s for type:%s", $value, $type
                        ));
                    }
                }elseif(is_array($value)){
                    $data[$key] = new $type($value);
                }else{
                    throw new \Exception(sprintf(
                        "invalid value:%s for type:%s", json_encode($value), $type
                    ));
                }
            }
        }

        return $data;
    }

    //----------------------------------- assist methods -----------------------------------------------------
    /**
     * @return array
     */
    private function attributes()
    {
        return array_keys(static::labels());
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function hasAttribute($attribute)
    {
        return isset(static::labels()[$attribute]);
    }

    /**
     * @param $method
     * @return bool
     * @throws \ReflectionException
     */
    public function hasMethod($method)
    {
        if(!method_exists($this, $method)){
            return false;
        }

        $reflection = new \ReflectionMethod($this, $method);
        return $reflection->isPublic() && !$reflection->isStatic();
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function isReadonly($attribute)
    {
        return in_array($attribute, static::readonly());
    }

    /**
     * @param $value
     * @return bool
     */
    private function isEmpty($value)
    {
        return $value==='' || $value===[] || $value===null;
    }

    /**
     * @param $attribute
     * @return mixed|null
     */
    private function attributeType($attribute)
    {
        return isset(static::types()[$attribute]) ? static::types()[$attribute] : null;
    }

    /**
     * @param $attribute
     * @return mixed|null
     */
    private function defaultValue($attribute)
    {
        $default = isset(static::defaults()[$attribute]) ? static::defaults()[$attribute] : null;
        if($default!==null){
            return $default;
        }

        $type = $this->getAttributeLabel($attribute);
        if(is_array($type)){//此时属性类型为对象数组
            $type = 'array';
        }elseif(!$this->isNormalType($type)){
            $type = 'object';
        }

        $defaults = [
            'int'    => 0,
            'integer'=> 0,
            'float'  => 0.0,
            'double' => 0.0,
            'bool'   => false,
            'string' => '',
            'array'  => [],
            'object' => null
        ];

        return $defaults[$type];
    }

    /**
     * Validator 使用
     * @param $attribute
     * @return mixed
     */
    public function getAttributeLabel($attribute)
    {
        return static::labels()[$attribute];
    }

    abstract public function equalTo(Data $data): bool;

    /**
     * @param string $name
     * @param $value
     * @return Data
     * @throws \Exception
     */
    final public function with(string $name, $value)
    {
        $arr = $this->toArray();
        $arr[$name] = $value;

        return new static($arr);
    }

    //----------------------------------- config -----------------------------------------------------

    /**
     * 由自动化工具生成
     * 该配置在初始化时判断属性正确性时使用
     * @return array
     *
     * Entity or Value with this property:
     * @property int     $id
     * @property string  $name
     * @property Notice  $notice
     * @property Group[] $groups
     * @property bool    $isPass
     *
     * configure like this:
     * ```php
     * [
     *     'id'    =>'int',
     *     'name'  =>'string',
     *     'notice'=>Notice::class,
     *     'groups'=>[Group::class],
     *     'isPass'=>'bool'
     * ]
     * ```
     *
     */
    abstract public static function types():array;

    /**
     * 由自动化工具生成
     * 实体或值对象所有属性以及其友好显示
     * 不在该列表内的属性将被忽略
     * @return array
     * ```php
     * [
     *     'id'  =>'Id',
     *     'name'=>'名称'
     * ]
     * ```
     */
    abstract public static function labels():array;

    /**
     * 由自动化工具生成
     * @return array
     */
    public static function defaults():array
    {
        return [];
    }

    /**
     * 由自动化工具生成
     * 对于值对象而言，所有的属性都是只读的
     * @return array
     */
    public static function readonly():array
    {
        return [];
    }

}