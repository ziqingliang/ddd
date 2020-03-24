<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2017/8/10
 * Time: 下午10:19
 */

namespace ziqing\ddd\base;

use Illuminate\Container\Container;
use ziqing\ddd\Exceptions\TypeMatchFailed;
use ziqing\ddd\Exceptions\TypeUndefined;
use ziqing\ddd\Exceptions\AttributeReadonly;
use ziqing\ddd\Exceptions\AttributeUndefined;
use ziqing\ddd\Exceptions\UnSupported;


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
 * 定义依赖
 * @method void __dependency()
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
     * @throws
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

        //使用容器处理依赖
        if(
            method_exists($this, '__dependency') &&
            (new \ReflectionMethod($this, '__dependency'))->isPublic()
        ){
            Container::getInstance()->call([$this, '__dependency']);
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
     * @throws
     */
    public function __set(string $attribute, $value)
    {
        //如果没有在 labels 内显式声明，则报错
        if(!$this->hasAttribute($attribute)){
            throw new AttributeUndefined(AttributeUndefined::ACCESS_SET, [get_called_class(), $attribute]);
        }

        if(self::isReadonly($attribute)){
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
     * 禁止在 getter 方法内再获取本属性，会造成递归
     * @param $attribute
     * @return mixed
     * @throws
     */
    public function &__get($attribute)
    {
        if(!$this->hasAttribute($attribute)){
            throw new AttributeUndefined(AttributeUndefined::ACCESS_GET, [get_called_class(), $attribute]);
        }

        $setter = 'get' . $attribute;
        if($this->hasMethod($setter)){
            call_user_func([$this, $setter]);
        }else{
            return $this->mandate->$attribute;
        }
    }

    /**
     * 内部使用，设置一个属性值，不经过setter方法
     * @param string $attribute
     * @param $value
     * @throws
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
            $setter = 'get' . $attribute;
            if($this->hasMethod($setter)){
                $data[$attribute] = call_user_func([$this, $setter]);
                continue;
            }

            if($this->isEmpty($this->mandate->$attribute)){
                if($filterNull){
                }else{
                    $data[$attribute] = null;
                }
            }else{
                $type = $this->attributeType($attribute);
                if($this->isNormalType($type)){//常规类型
                    $data[$attribute] = $this->mandate->$attribute;
                }elseif(is_string($type)){//对象类型
                    $data[$attribute] = $this->mandate->$attribute->toArray($filterNull);
                }elseif($this->isNormalType(reset($type))){//常规数组
                    $temp = [];
                    foreach ($this->mandate->$attribute as $key=>$value){
                        $temp[$key] = $value;
                    }
                    $data[$attribute] = $temp;
                }else{//对象数组
                    $temp = [];
                    /**
                     * @var Data $value
                     */
                    foreach ($this->mandate->$attribute as $key=>$value){

                        $temp[$key] = $value->toArray($filterNull);
                    }
                    $data[$attribute] = $temp;
                }
            }
        }

        return $data;
    }

    public function __clone()
    {
        $this->mandate = clone $this->mandate;
        $data = [];
        foreach ($this->attributes() as $attribute){
            if($this->isEmpty($this->mandate->$attribute)){
                continue;
            }
            $type = $this->attributeType($attribute);
            if($this->isNormalType($type)){//常规类型
            }elseif(is_string($type)){//对象类型
                $this->mandate->$attribute = clone $this->mandate->$attribute;
            }elseif($this->isNormalType(reset($type))){//常规数组
            }else{//对象数组
                foreach ($this->mandate->$attribute as $key=>$value){
                    $this->mandate->$attribute[$key] = clone $value;
                }
            }
        }
    }

    /**
     * @param bool $filterNull
     * @return string
     */
    public function toJson($filterNull=true)
    {
        $json = $this->toArray($filterNull);

        return json_encode($json, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
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
            $data = $this->castFromArray($type, $json);
        }elseif($json!==false){
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
                if($this->isNormalType($type)){
                    $data[$key] = $this->castToNormal($type, $value);
                }elseif($value instanceof $type){
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
    public static function isReadonly($attribute)
    {
        return in_array($attribute, static::readonly());
    }

    public static function isInt(string $attribute)
    {
        $types = static::types();
        $type  = $types[$attribute] ?? null;
        switch ($type){
            case 'int':
            case 'integer':
                return true;
            default:
                return false;
        }
    }

    public static function isFloat(string $attribute)
    {
        $types = static::types();
        $type  = $types[$attribute] ?? null;
        switch ($type){
            case 'float':
            case 'double':
                return true;
            default:
                return false;
        }
    }

    public static function isBool(string $attribute)
    {
        $types = static::types();
        $type  = $types[$attribute] ?? null;
        switch ($type){
            case 'bool':
            case 'boolean':
                return true;
            default:
                return false;
        }
    }

    public static function isString(string $attribute)
    {
        $types = static::types();
        $type  = $types[$attribute] ?? null;
        if($type==='string'){
            return true;
        }else{
            return false;
        }
    }

    public static function isArray(string $attribute)
    {
        $types = static::types();
        $type  = $types[$attribute] ?? null;
        if($type==='array'){
            return true;
        }elseif(is_array($type)){
            return true;
        }else{
            return false;
        }
    }

    public static function isScalar(string $attribute)
    {
        $types = static::types();
        $type  = $types[$attribute] ?? null;
        switch ($type){
            case 'int':
            case 'integer':
            case 'float':
            case 'double':
            case 'bool':
            case 'boolean':
            case 'string':
                return true;
            default:
                return false;
        }
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

        $type = $this->attributeType($attribute);
        if(is_array($type)){//此时属性类型为对象数组或者常规类型数组
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
