<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/25
 * Time: 下午4:09
 */

namespace ziqing\ddd\tool\traits;

use ziqing\ddd\tool\values\Property;

trait DataGenerateTrait
{
    /**
     * @var Property[]
     */
    private $properties = [];
    private $noteProperties;
    private $types;
    private $labels;
    private $readonly;
    private $defaults;

//    protected $generatorType = '';

    protected function setProperties(array $properties)
    {
        $this->hasBuild = false;
        $this->properties = $properties;
        return $this;
    }

    protected function addOneProperty(Property $property)
    {
        $this->hasBuild = false;
        $this->properties[$property->name] = $property;
        return $this;
    }

    protected function getOneProperty($name)
    {
        return $this->properties[$name] ?? null;
    }

    protected function getNoteProperties():string
    {
        $list = $this->noteProperties;
        $list = $this->paddingArray($list);
        $lines = [];
        foreach ($list as list($modifier, $type, $name, $label, $description)){
            if(trim($description)!='' && trim($label)==''){
                $label = str_pad(ucfirst(trim($name)), strlen($label));
            }
            $line = "{$modifier} {$type} \${$name} {$label} {$description}";
            $line = trim($line);
            $line = " * @{$line}";
            $lines[] = $line;
        }

        if($lines){
            return implode("\n", $lines);
        }else{
            return " * todo: define other properties here";
        }
    }

    private function getNoteReadonly():string
    {
        $lines = [];
        foreach ($this->readonly as $name){
            $line = " * @readonly \${$name}";
            $lines[] = $line;
        }

        if($lines){
            return implode("\n", $lines);
        }else{
            return " * ";
        }
    }

    private function getNoteDefaults():string
    {
        $lines = [];
        $list = $this->defaults;
        foreach ($list as $key=>list($name, $value)){
            if($value===true){
                $list[$key][1] = 'true';
            }
        }
        $list = $this->paddingArray($list);

        foreach ($list as list($name, $value)){
            $line = " * @default \${$name} {$value}";
            $lines[] = $line;
        }

        if($lines){
            return implode("\n", $lines);
        }else{
            return " * ";
        }
    }

    private function getStaticTypes():string
    {
        $list = $this->types;
        $lines = [];
        foreach ($list as $index=>list($name, $type)){
            $list[$index][0] = "'{$name}'";

            $type = trim($type);
            if(substr_compare($type, '[]', -2)==0){
                $type = trim($type, '[]');
                if($this->isScalar($type) || $type=='array' || $type=='object'){
                    $type = "['$type']";
                }else{
                    $type = "[{$type}::class]";
                }
            }elseif($this->isScalar($type) || $type=='array' || $type=='object'){
                $type = "'{$type}'";
            }else{
                $type = "{$type}::class";
            }
            $list[$index][1] = $type;
        }

        $padding = str_pad("", 12);
        $list = $this->paddingArray($list);
        foreach ($list as list($name, $type)){
            $type  = trim($type);

            $line  = "{$padding}{$name} => {$type},";
            $lines[] = $line;
        }

        if($lines){
            return implode("\n", $lines);
        }else{
            return "{$padding}//todo: ...";
        }
    }

    private function getStaticLabels():string
    {
        $list = $this->labels;
        foreach ($list as $index=>list($name, $label)) {
            $list[$index][0] = "'{$name}'";
            $list[$index][1] = "'{$label}'";
        }
        $list = $this->paddingArray($list);

        $lines = [];
        $padding = str_pad("", 12);
        foreach ($list as list($name, $label)){
            $label = trim($label);
            $line  = "{$padding}{$name} => {$label},";
            $lines[] = $line;
        }

        if($lines){
            return implode("\n", $lines);
        }else{
            return "{$padding}//todo: ...";
        }
    }

    private function getStaticReadonly():string
    {
        $list = $this->readonly;
        $lines = [];
        foreach ($list as $name){
            $lines[] = "'{$name}'";
        }

        $padding = str_pad("", 12);
        if($lines){
            return $padding . implode(",", $lines);
        }else{
            return "{$padding}//here no readonly property";
        }
    }

    private function getStaticDefaults():string
    {
        $list = $this->defaults;
        foreach ($list as $index=>list($name, $default)){
            $list[$index][0] = "'{$name}'";
            if(is_bool($default)){
                $list[$index][1] = 'true';
            }elseif(!is_int($default) && !is_float($default)){
                $list[$index][1] = "'{$default}'";
            }
        }

        $padding = str_pad("", 12);
        $list = $this->paddingArray($list);
        $lines = [];
        foreach ($list as list($name, $default)){
            $default = trim($default);
            $line = "{$padding}{$name} => {$default},";
            $lines[] = $line;
        }

        if($lines){
            return implode("\n", $lines);
        }else{
            return "{$padding}//here no default value";
        }
    }

    /**
     * @param string $template
     * @return string
     */
    protected function buildFileContent(string $template)
    {
        $this->buildFromProperties();

        $searches = [
            '{{namespace}}',
            '{{className}}',
            '{{package}}',
            '{{properties}}',
            '{{readonly}}',
            '{{defaults}}',
            '{{static-types}}',
            '{{static-labels}}',
            '{{static-readonly}}',
            '{{static-defaults}}'
        ];

        $replaces = [
            $this->getNamespace(),
            $this->getClassName(),
            $this->getPackage(),
            $this->getNoteProperties(),
            $this->getNoteReadonly(),
            $this->getNoteDefaults(),
            $this->getStaticTypes(),
            $this->getStaticLabels(),
            $this->getStaticReadonly(),
            $this->getStaticDefaults()
        ];

        return str_replace($searches, $replaces, $template);
    }

    private $hasBuild = false;

    private function buildFromProperties($withId=false)
    {
        if($this->hasBuild){
            return ;
        }

        $properties = [];
        $types = [];
        $labels = [];
        $readonly = [];
        $defaults = [];

        foreach ($this->properties as $property){
            $name = $property->name;
            if($name=='id' && $withId===false){
                continue;//即使定义了该属性，自动化工具也不处理
            }

            $types[]  = [$name, $property->type];
            $labels[] = [$name, $property->label ? $property->label : ucfirst($name)];

            $modifier = 'property';
            if($property->isReadonly){
                $readonly[] = $name;
                $modifier = 'property-read';
            }

            $default = $this->isDefaultValueValid($property->type, $property->default);
            if($default!==false){
                $defaults[] = [$name, $default];
            }

            $properties[] = [$modifier, $property->type, $property->name, $property->label, $property->description];
        }

        $this->noteProperties = $properties;
        $this->types    = $types;
        $this->labels   = $labels;
        $this->readonly = $readonly;
        $this->defaults = $defaults;

        $this->hasBuild = true;
    }

    private function isScalar($type)
    {
        $scalars = [
            'int'    => 0,
            'integer'=> 0,
            'float'  => 0.0,
            'double' => 0.0,
            'bool'   => false,
            'string' => '',
        ];

        return isset($scalars[$type]);
    }

    private function isDefaultValueValid($type, $value)
    {
        if($value==='' || $value===null){
            return false;
        }

        if(!$this->isScalar($type)){
            return false;
        }

        switch ($type){
            case 'int':
            case 'integer':
                $value = (int)$value;
                if($value===0){
                    return false;
                }else{
                    return $value;
                }
                break;
            case 'float':
            case 'double':
                $value = (float)$value;
                if($value===0.0){
                    return false;
                }else{
                    return $value;
                }
                break;
            case 'string':
                return $value;
                break;
            case 'bool':
            case 'boolean':
                if($value==='true'){
                    return true;
                }else{
                    return false;
                }
        }
    }

    private function paddingArray(array $list)
    {
        $max = [];
        foreach ($list as $data){
            foreach ($data as $index=>$item){
                $item = trim($item);
                $length = strlen($item);
                if(empty($max[$index])){
                    $max[$index] = $length;
                }elseif($max[$index]<$length){
                    $max[$index] = $length;
                }
            }
        }

        foreach ($list as $key=>$data){
            foreach ($data as $index=>$item){
                $length = $max[$index];
                $list[$key][$index] = str_pad($item, $length);
            }
        }

        return $list;
    }
}
