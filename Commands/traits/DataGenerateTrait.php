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

    protected function getNoteProperties(): string
    {
        $list = $this->noteProperties;
        $list = $this->paddingArray($list);
        $lines = [];
        foreach ($list as list($modifier, $type, $name, $description)) {
            if ($this->generatorType !== 'values' && $name == 'id') {
                continue;
            }
            $line = "{$modifier} {$type} \${$name} {$description}";
            $line = trim($line);
            $line = " * @{$line}";
            $lines[] = $line;
        }

        if ($lines) {
            return implode("\n", $lines);
        } else {
            return " * todo: define other properties here";
        }
    }

    private function getStaticTypes(): string
    {
        $list = $this->types;
        $lines = [];
        foreach ($list as $index => list($name, $type)) {
            $list[$index][0] = "'{$name}'";

            $type = trim($type);
            if (substr_compare($type, '[]', -2) == 0) {
                $type = trim($type, '[]');
                if ($this->isScalar($type) || $type == 'array' || $type == 'object') {
                    $type = "['$type']";
                } else {
                    $type = "[{$type}::class]";
                }
            } elseif ($this->isScalar($type) || $type == 'array' || $type == 'object') {
                $type = "'{$type}'";
            } else {
                $type = "{$type}::class";
            }
            $list[$index][1] = $type;
        }

        $padding = str_pad("", 12);
        $list = $this->paddingArray($list);
        foreach ($list as list($name, $type)) {
            $type  = trim($type);

            $line  = "{$padding}{$name} => {$type},";
            $lines[] = $line;
        }

        if ($lines) {
            return implode("\n", $lines);
        } else {
            return "{$padding}//todo: ...";
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
            '{{propertie-defines}}',
            '{{static-types}}',
        ];

        $replaces = [
            $this->getNamespace(),
            $this->getClassName(),
            $this->getPackage(),
            $this->getNoteProperties(),
            $this->getPropertyDefines(),
            $this->getStaticTypes(),
        ];

        return str_replace($searches, $replaces, $template);
    }

    private function getPropertyDefines()
    {
        $list = $this->noteProperties;
        $list = $this->paddingArray($list);
        $lines = [];
        foreach ($list as list($modifier, $type, $name, $description)) {
            $lines[] = sprintf('    public $%s;', $name);
        }

        if ($lines) {
            return implode("\n", $lines);
        } else {
            return "";
        }
    }

    private $hasBuild = false;

    private function buildFromProperties()
    {
        if ($this->hasBuild) {
            return ;
        }

        $properties = [];
        $types = [];

        foreach ($this->properties as $property) {
            $name = $property->name;
            $types[]  = [$name, $property->type];

            $modifier = 'property';

            $properties[] = [$modifier, $property->type, $property->name, $property->description];
        }

        $this->noteProperties = $properties;
        $this->types    = $types;

        $this->hasBuild = true;
    }

    private function isScalar($type)
    {
        $scalars = [
            'int'    => 0,
            'integer' => 0,
            'float'  => 0.0,
            'double' => 0.0,
            'bool'   => false,
            'string' => '',
        ];

        return isset($scalars[$type]);
    }

    private function isDefaultValueValid($type, $value)
    {
        if ($value === '' || $value === null) {
            return false;
        }

        if (!$this->isScalar($type)) {
            return false;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                $value = (int)$value;
                if ($value === 0) {
                    return false;
                } else {
                    return $value;
                }
                break;
            case 'float':
            case 'double':
                $value = (float)$value;
                if ($value === 0.0) {
                    return false;
                } else {
                    return $value;
                }
                break;
            case 'string':
                return $value;
                break;
            case 'bool':
            case 'boolean':
                if ($value === 'true') {
                    return true;
                } else {
                    return false;
                }
        }
    }

    private function paddingArray(array $list)
    {
        $max = [];
        foreach ($list as $data) {
            foreach ($data as $index => $item) {
                $item = trim($item);
                $length = strlen($item);
                if (empty($max[$index])) {
                    $max[$index] = $length;
                } elseif ($max[$index] < $length) {
                    $max[$index] = $length;
                }
            }
        }

        foreach ($list as $key => $data) {
            foreach ($data as $index => $item) {
                $length = $max[$index];
                $list[$key][$index] = rtrim(str_pad($item, $length));
            }
        }

        return $list;
    }
}
