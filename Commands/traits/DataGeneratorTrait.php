<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/29
 * Time: 上午10:04
 */

namespace ziqing\ddd\tool\traits;


use ziqing\ddd\Entity;
use ziqing\ddd\tool\values\Property;
use ziqing\ddd\Value;

trait DataGeneratorTrait
{
    use DataGenerateTrait;

    private $lines = [];

    protected function cacheLines($filename)
    {
        foreach (file($filename) as $index=>$line){
            $this->lines[$index+1] = $line;
        }
    }

    protected function parseFile($filename)
    {
        include $filename;
        $class = $this->getClassNameFromFile();

        $parent = $this->generatorType=='values' ? Value::class : Entity::class;

        try{
            $reflection = new \ReflectionClass($class);
            if(!$reflection->isSubclassOf($parent)){
                $this->error("File:$filename is not a $parent file.");
                die;
            }

            //parse class comment
            $classStartLineNo = $reflection->getStartLine();
            $this->parseClassDocComment($classStartLineNo);

            //replace static method's body
            $types = ['types', 'labels', 'readonly', 'defaults'];
            foreach ($types as $type){
                if($reflection->hasMethod($type)){
                    $method = $reflection->getMethod($type);
                    $start  = $method->getStartLine();
                    $end    = $method->getEndLine();
                    if($this->isMethodDefinedLocal($type, $start)){
                        $this->replaceMethodContents($type, $start, $end);
                    }
                }
            }
        }catch (\Throwable $exception){
            $error = sprintf("Error:%s; file:%s:%s", $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $this->error($error);
            die;
        }
    }

    protected function getTemplate()
    {
        ksort($this->lines);
        return implode('', $this->lines);
    }

    private function parseClassDocComment(int $classStartLineNo)
    {
        $mark = false;
        foreach ($this->lines as $index=>$line){
            if($index>=$classStartLineNo-1){
                break;
            }

            $line = trim($line, " \n*");
            if($this->stringBeginWith($line, '@property')){
                $mark = true;
                $this->parseOnePropertyFromLine($line);
            }elseif($this->stringBeginWith($line, '@readonly')){
                $this->parseOneReadonlyPropertyFromLine($line);
            }elseif($this->stringBeginWith($line, '@default')){
                $this->parseOneDefaultFromLine($line);
            }
            if($mark){
                unset($this->lines[$index]);
            }
        }
        if($this->generatorType=='values'){
            $this->lines[$index-1] = <<<'TXT'
{{properties}}
 *
{{readonly}}
 *
{{defaults}}

TXT;
        }else{
            $this->lines[$index-1] = <<<'TXT'
 * @property-read int $id
{{properties}}
 *
{{readonly}}
 *
{{defaults}}

TXT;
        }
    }

    private function replaceMethodContents($type, $start, $end)
    {
        $map = [
            'types'    => "{{static-types}}\n",
            'labels'   => "{{static-labels}}\n",
            'readonly' => "{{static-readonly}}\n",
            'defaults' => "{{static-defaults}}\n"
        ];
        $mark = false;
        foreach ($this->lines as $index=>$line){
            if($index<$start || $index>$end-2){
                continue;
            }

            $line = trim($line);
            if($mark===false && $this->stringEndWith($line, "[")){
                $mark = true;
            }elseif($mark){
                unset($this->lines[$index]);
            }
        }
        $this->lines[$end-2] = $map[$type];
    }

    private function isMethodDefinedLocal($type, $start)
    {
        if(empty($this->lines[$start])){
            return false;
        }
        $line = $this->lines[$start];
        $line = preg_replace("/ +/", " ", trim($line));
        $define = "public static function {$type}()";

        return $this->stringBeginWith($line, $define);
    }

    private function stringEndWith($string, $end)
    {
        $a = substr($string, -strlen($end));
        return $a===$end;
    }

    private function stringBeginWith($string, $begin)
    {
        return $string && substr_compare($string, $begin, 0, strlen($begin))===0;
    }

    private function getClassNameFromFile()
    {
        $namespace = '';
        foreach ($this->lines as $line){
            $line = trim($line);
            if($line && substr_compare($line, 'namespace ', 0, 10)==0){
                $list = explode(" ", $line);
                $namespace = trim($list[1], " ;");
            }
            if($line && substr_compare($line, 'class ', 0, 6)==0){
                $list = explode(" ", $line);
                $class = trim($list[1]);
                foreach ($list as $index=>$item){
                    if($item=='extends'){
                        $parent = $list[$index+1];
                    }
                }

                if(empty($parent) || ($parent!='Entity' && $parent!='Value')){
                    $type = $this->generatorType=='values' ? 'Value' : 'Entity';
                    $this->error("This isn't a valid {$type} class file.");
                    die;
                }
                break;
            }
        }
        if(empty($class)){
            $this->error("This isn't a valid class file. ");
            die;
        }
        return $namespace . '\\' . $class;
    }

    private function parseOnePropertyFromLine($line)
    {
        $line = trim($line, " \n*");
        $list = preg_split("/ +/", $line, 5);
        if(count($list)<3){
            $this->error("Invalid @property definition line:$line");
            return ;
        }

        $property = new Property();
        $property->type = $list[1];
        $property->name = trim($list[2], '$');
        $property->label = $list[3] ?? null;
        $property->description = $list[4] ?? null;

        if($list[0]=='@property-read'){
            $property->isReadonly = true;
        }

        $this->addOneProperty($property);
    }

    private function parseOneReadonlyPropertyFromLine($line)
    {
        $line = trim($line, '* ');
        $list = preg_split("/ +/", $line);
        if(count($list)<2){
            $this->error("Invalid readonly definition line:$line");
            return;
        }
        $name = trim($list[1], '$');
        $property = $this->getOneProperty($name);
        if(empty($property)){
            $this->error("Unknown property name:$name in @readonly definition; line:$line");
            return ;
        }else{
            $property->isReadonly = true;
            $this->addOneProperty($property);
        }
    }

    private function parseOneDefaultFromLine($line)
    {
        $line = trim($line, '* ');
        $list = preg_split("/ +/", $line);
        if(count($list)<3){
            $this->error("Invalid readonly definition line:$line");
            return;
        }
        $name = trim($list[1], '$');
        $property = $this->getOneProperty($name);
        if(empty($property)){
            $this->error("Unknown property name:$name in @default definition; line:$line");
            return ;
        }else{
            $property->default = $list[2];
            $this->addOneProperty($property);
        }

    }

}
