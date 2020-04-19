<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/6/1
 * Time: 下午2:57
 */

namespace ziqing\ddd\tool\helps;

use ziqing\ddd\Entity;
use ziqing\ddd\Value;

class DataType
{
    private $entityTypes = [];
    private $entityLabels = [];

    private $templates = [
        'int'    => '$table->integer("{{name}}")->comment("{{label}}")->nullable();',
        'bool'   => '$table->tinyInteger("{{name}}", false, true)->comment("{{label}}")->nullable();',
        'float'  => '$table->float("{{name}}")->comment("{{label}}")->nullable();',
        'string' => '$table->string("{{name}}", 255)->comment("{{label}}")->nullable();',
//        'json'   => '$table->json("{{name}}")->comment("{{label}}")->nullable();',
        'json'   => '$table->text("{{name}}", 10000)->comment("{{label}}")->nullable();',
    ];

    private $repositoryTemplates = [
        'int'     => '$entity->{propertyName} ? $entity->{propertyName} : 0;',
        'bool'    => '$entity->{propertyName} ? 1 : 0;',
        'float'   => '$entity->{propertyName} ? $entity->{propertyName} : 0.0;',
        'string'  => '$entity->{propertyName} ? $entity->{propertyName} : "";',
        'array'   => '$entity->{propertyName} ? json_encode($entity->{propertyName}, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : "";',
        'value'   => '$entity->{propertyName} ? json_encode($entity->{propertyName}->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES): "";',
        'values'  => '$entity->{propertyName} ? self::encodeValues($entity->{propertyName}) : "";',
        'entity'  => '$entity->{propertyName} ? $entity->{propertyName}->id : 0;',
        'entities' => '$entity->{propertyName} ? self::encodeEntities($entity->{propertyName}) : "";'
    ];

    public function __construct(array $entityTypes, $entityLabels)
    {
        $this->entityTypes  = $entityTypes;
        $this->entityLabels = $entityLabels;
    }

    private function isInternal($name)
    {
        $set = ['id', 'createdAt', 'updatedAt', 'deletedAt', 'createdBy', 'updatedBy', 'deletedBy', 'creator', 'updater', 'deleter'];
        return in_array($name, $set);
    }

    /**
     * @param bool $withId
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getEntityToModelLogicCode($withId = false)
    {
        $list = [];
        foreach ($this->entityTypes as $propertyName => $entityType) {
            if ($this->isInternal($propertyName)) {
                continue;
            }
            $fieldName = $propertyName;
            if (is_string($entityType)) {
                switch ($entityType) {
                    case 'int':
                    case 'integer':
                        $type = 'int';
                        break;
                    case 'bool':
                    case 'boolean':
                        $type = 'bool';
                        break;
                    case 'float':
                    case 'double':
                        $type = 'float';
                        break;
                    case 'string':
                        $type = 'string';
                        break;
                    case 'array':
                        $type = 'array';
                        break;
                    default:
                        if ($this->isEntity($entityType)) {
                            $fieldName = lcfirst($this->getClassName($entityType) . "Id");
                            $type = 'entity';
                        } elseif ($this->isValue($entityType)) {
                            $type = 'value';
                        } else {
                            throw new \Exception("Unknown Entity property type:{$entityType}");
                        }
                        break;
                }
            } elseif (is_array($entityType)) {
                $entityType = reset($entityType);
                switch ($entityType) {
                    case 'int':
                    case 'integer':
                    case 'float':
                    case 'double':
                    case 'bool':
                    case 'boolean':
                    case 'string':
                    case 'array':
                        $type = 'array';
                        break;
                    default:
                        if ($this->isEntity($entityType)) {
                            $fieldName = lcfirst($this->getClassName($entityType) . "Ids");
                            $type = 'entities';
                        } elseif ($this->isValue($entityType)) {
                            $type = 'values';
                        } else {
                            throw new \Exception("Unknown Entity property type:{$entityType}");
                        }
                }
            }
            $template = $this->repositoryTemplates[$type];
            $list[] = ['$model->' . $fieldName, str_replace('{propertyName}', $propertyName, $template)];
        }
        $list = $this->paddingArray($list);
        $lines = [];
        $padding = str_pad("", 8, " ");
        foreach ($list as list($left, $right)) {
            $right = trim($right);
            $lines[] = "{$padding}{$left} = {$right}";
        }
        return implode("\n", $lines);
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
                $list[$key][$index] = str_pad($item, $length);
            }
        }

        return $list;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getFieldsDefinition(int $padLength)
    {
        $definitions = [];
        foreach ($this->entityTypes as $name => $type) {
            $label = $this->entityLabels[$name];
            if ($this->isInternal($name)) {
                continue;
            }
            if (is_string($type)) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $template = $this->templates['int'];
                        break;
                    case 'bool':
                    case 'boolean':
                        $template = $this->templates['bool'];
                        break;
                    case 'float':
                    case 'double':
                        $template = $this->templates['float'];
                        break;
                    case 'string':
                        $template = $this->templates['string'];
                        break;
                    case 'array':
                        $template = $this->templates['json'];
                        break;
                    default:
                        if ($this->isEntity($type)) {
                            $name = lcfirst($this->getClassName($type) . "Id");
                            $template = $this->templates['int'];
                        } elseif ($this->isValue($type)) {
                            $template = $this->templates['json'];
                        } else {
                            throw new \Exception("Unknown Entity property type:{$type}");
                        }
                        break;
                }
            } elseif (is_array($type)) {
                $type = reset($type);
                if (!$this->isNormal($type) && !$this->isValue($type) && $this->isEntity($type)) {
                    $name = lcfirst($this->getClassName($type) . "Ids");
                    $template = $this->templates['json'];
                }
            }
            $padding = str_repeat(" ", $padLength);
            $definitions[] = $padding . str_replace(["{{name}}", "{{label}}"], [$name, $label], $template);
        }
        return implode("\n", $definitions);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getFields()
    {
        $entityFields   = [];
        $noEntityFields = [];

        foreach ($this->entityTypes as $name => $type) {
            if ($this->isInternal($name)) {
                continue;
            }
            togo:
            if (is_string($type)) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                    case 'bool':
                    case 'boolean':
                    case 'float':
                    case 'double':
                    case 'string':
                    case 'array':
                        $noEntityFields[] = $name;
                        break;
                    default:
                        if ($this->isEntity($type)) {
                            $entityFields[] = $name;
                        } elseif ($this->isValue($type)) {
                            $noEntityFields[] = $name;
                        } else {
                            throw new \Exception("Unknown Entity property type:{$type}");
                        }
                        break;
                }
            }
            if (is_array($type)) {
                $type = reset($type);
                goto togo;
            }
        }
        return [$noEntityFields, $entityFields];
    }

    private function getClassName($class)
    {
        return substr($class, strrpos($class, '\\') + 1);
    }

    /**
     * @param $class
     * @return bool
     * @throws \ReflectionException
     */
    private function isEntity($class)
    {
        $reflection = new \ReflectionClass($class);
        if ($reflection->isSubclassOf(Entity::class)) {
            return true;
        } else {
            false;
        }
    }

    /**
     * @param $class
     * @return bool
     * @throws \ReflectionException
     */
    private function isValue($class)
    {
        $reflection = new \ReflectionClass($class);
        if ($reflection->isSubclassOf(Value::class)) {
            return true;
        } else {
            false;
        }
    }

    private function isNormal($type)
    {
        switch ($type) {
            case 'int':
            case 'integer':
            case 'bool':
            case 'boolean':
            case 'float':
            case 'double':
            case 'string':
            case 'array':
                return true;
                break;
            default:
                return false;
        }
    }
}
