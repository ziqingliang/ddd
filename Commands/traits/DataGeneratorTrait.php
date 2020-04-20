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
        foreach (file($filename) as $index => $line) {
            $this->lines[$index + 1] = $line;
        }
    }

    protected function parseFile($filename)
    {
        include $filename;
        $class = $this->getClassNameFromFile();

        $parent = $this->generatorType == 'values' ? Value::class : Entity::class;

        try {
            $reflection = new \ReflectionClass($class);
            if (!$reflection->isSubclassOf($parent)) {
                $this->error("File:$filename is not a $parent file.");
                die;
            }

            //parse class comment
            $classStartLineNo = $reflection->getStartLine();
            $this->parseClassDocComment($classStartLineNo);

//            print_r($this->lines);die;
            $this->replacePublicProperty();
            //replace static method's body
            $types = ['types'];
            foreach ($types as $type) {
                if ($reflection->hasMethod($type)) {
                    $method = $reflection->getMethod($type);
                    $start = $method->getStartLine();
                    $end = $method->getEndLine();
                    if ($this->isMethodDefinedLocal($type, $start)) {
                        $this->replaceMethodContents($type, $start, $end);
                    }
                }
            }
        } catch (\Throwable $exception) {
            $error = sprintf("Error:%s; file:%s:%s", $exception->getMessage(), $exception->getFile(),
                $exception->getLine());
            $this->error($error);
            die;
        }
    }

    protected function getTemplate()
    {
        ksort($this->lines);
        foreach ($this->lines as &$line) {
            $line = rtrim($line) . "\n";
        }
        return implode('', $this->lines);
    }

    private function parseClassDocComment(int $classStartLineNo)
    {
        $mark = false;
        foreach ($this->lines as $index => $line) {
            if ($index >= $classStartLineNo - 1) {
                break;
            }

            $line = trim($line, " \n*");
            if ($this->stringBeginWith($line, '@property')) {
                $mark = true;
                $this->parseOnePropertyFromLine($line);
            } elseif ($this->stringBeginWith($line, '@default')) {
                $this->parseOneDefaultFromLine($line);
            }
            if ($mark) {
                unset($this->lines[$index]);
            }
        }

        if ($this->generatorType == 'values') {
            $this->lines[$index - 1] = <<<'TXT'
{{properties}}
 *
TXT;
        } else {
            $this->lines[$index - 1] = <<<'TXT'
{{properties}}
 *
TXT;
        }
    }

    private function getPropertyDefines($asArray = false)
    {
        $list = $this->properties;
        $lines = [];
        foreach ($list as $name => $property) {
            if ($this->generatorType !== 'values' && $name == 'id') {
                continue;
            }
            $lines[] = sprintf('    public $%s;', $name);
        }

        if ($asArray) {
            return $lines;
        }

        return implode("\n", $lines);
    }

    private function replaceMethodContents($type, $start, $end)
    {
        $map = [
            'types' => "{{static-types}}\n",
        ];
        $mark = false;
        foreach ($this->lines as $index => $line) {
            if ($index < $start || $index > $end - 2) {
                continue;
            }

            $line = trim($line);
            if ($mark === false && $this->stringEndWith($line, "[")) {
                $mark = true;
            } elseif ($mark) {
                unset($this->lines[$index]);
            }
        }
        $this->lines[$end - 2] = $map[$type];
    }

    private function replacePublicProperty()
    {
        $text = implode("\n", $this->lines);
        $defines = $this->getPropertyDefines(true);

        foreach ($defines as $key => $define) {
            if (strpos($text, $define) !== false) {
                unset($defines[$key]);
            }
        }
        $last = 0;
        foreach ($this->lines as $index => $line) {
            $last = $index;
            if (strpos($line, 'function') !== false) {
                break;
            }
        }

        if (empty($defines)) {
            return;
        }

        $defines = implode("\n", $defines);

        do {
            $last--;
            $line = $this->lines[$last];
            $line = trim($line);
            if (strlen($line) >= 2 && $line[0]=='/' && $line[1]=='*') {
                $line = trim($this->lines[$last-1]);
                $line2 = trim($this->lines[$last-2]);
                if (empty($line)) {
                    $this->lines[$last-1] = $defines . "\n";
                } else {
                    $this->lines[$last-1] .= $defines . "\n";
                }
                break;
            }
        } while(true);
    }

    /**
     * 找到属性定义的后一行
     */
    private function getPropertyLastLine()
    {
    }

    private function isMethodDefinedLocal($type, $start)
    {
        if (empty($this->lines[$start])) {
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
        return $a === $end;
    }

    private function stringBeginWith($string, $begin)
    {
        return $string && substr_compare($string, $begin, 0, strlen($begin)) === 0;
    }

    private function getClassNameFromFile()
    {
        $namespace = '';
        foreach ($this->lines as $line) {
            $line = trim($line);
            if ($line && substr_compare($line, 'namespace ', 0, 10) == 0) {
                $list = explode(" ", $line);
                $namespace = trim($list[1], " ;");
            }
            if ($line && substr_compare($line, 'class ', 0, 6) == 0) {
                $list = explode(" ", $line);
                $class = trim($list[1]);
                foreach ($list as $index => $item) {
                    if ($item == 'extends') {
                        $parent = $list[$index + 1];
                    }
                }

                if (empty($parent) || ($parent != 'Entity' && $parent != 'Value')) {
                    $type = $this->generatorType == 'values' ? 'Value' : 'Entity';
                    $this->error("This isn't a valid {$type} class file.");
                    die;
                }
                break;
            }
        }
        if (empty($class)) {
            $this->error("This isn't a valid class file. ");
            die;
        }
        return $namespace . '\\' . $class;
    }

    private function parseOnePropertyFromLine($line)
    {
        $line = trim($line, " \n*");
        $list = preg_split("/ +/", $line, 5);
        if (count($list) < 3) {
            $this->error("Invalid @property definition line:$line");
            return;
        }

        $property = new Property();
        $property->type = $list[1];
        $property->name = trim($list[2], '$');
        $property->description = implode(" ", array_slice($list, 3));

        $this->addOneProperty($property);
    }

    private function parseOneReadonlyPropertyFromLine($line)
    {
        $line = trim($line, '* ');
        $list = preg_split("/ +/", $line);
        if (count($list) < 2) {
            $this->error("Invalid readonly definition line:$line");
            return;
        }
        $name = trim($list[1], '$');
        $property = $this->getOneProperty($name);
        if (empty($property)) {
            $this->error("Unknown property name:$name in @readonly definition; line:$line");
            return;
        } else {
            $property->isReadonly = true;
            $this->addOneProperty($property);
        }
    }

    private function parseOneDefaultFromLine($line)
    {
        $line = trim($line, '* ');
        $list = preg_split("/ +/", $line);
        if (count($list) < 3) {
            $this->error("Invalid readonly definition line:$line");
            return;
        }
        $name = trim($list[1], '$');
        $property = $this->getOneProperty($name);
        if (empty($property)) {
            $this->error("Unknown property name:$name in @default definition; line:$line");
            return;
        } else {
            $property->default = $list[2];
            $this->addOneProperty($property);
        }
    }
}
