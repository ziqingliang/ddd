<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/28
 * Time: 上午10:27
 */

namespace ziqing\ddd\tool\traits;

use ziqing\ddd\tool\values\Property;

trait CollectPropertiesFromConsoleTrait
{
    protected function collectPropertiesFromConsole()
    {
        $again = false;
        while (true) {
            if ($again && !$this->confirm("Add one more?", false)) {
                break;
            }

            $property = new Property();

            $property->name  = $this->addPropertyName();
            $property->type  = $this->addPropertyType();
            $property->description = $this->ask("description?");

            $this->addOneProperty($property);
            $again = true;
        }
    }

    protected function addPropertyName()
    {
        $pattern = '/[a-zA-z_][a-zA-Z_0-9]*/';
        $again = false;
        while (true) {
            if ($again) {
                $this->warn("You must assign a name of one property!");
            }

            $name = $this->ask("name");
            if (preg_match($pattern, $name) !== false) {
                return $name;
            }

            $this->warn("Invalid name:{$name}; The name must be a valid var name.");
            $again = true;
        }
    }

    protected function addPropertyType()
    {
        $pattern = '/[a-zA-z][a-zA-Z_0-9]*/';
        $again = false;
        while (true) {
            if ($again) {
                $this->warn("You must assign the type of one property!");
            }

            $type = $this->anticipate("type", ['int', 'float', 'bool', 'string', 'array']);
            if (preg_match($pattern, $type) !== false) {
                return $type;
            }
            $this->warn("Invalid type:{$type}; The type must be a valid data type.");
            $again = true;
        }
    }
}
