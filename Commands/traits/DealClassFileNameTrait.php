<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/29
 * Time: 下午5:45
 */

namespace ziqing\ddd\tool\traits;

trait DealClassFileNameTrait
{
    private $namespace;
    private $package;
    private $className;

    protected function setPackage($package)
    {
        $this->package = strtolower($package);
        return $this;
    }

    protected function setClassName($className)
    {
        list($namespace, $className) = $this->buildNamespaceAndClass($className);
        $this->namespace = sprintf("domains\\%s\\%s%s", $this->package, $this->generatorType, $namespace);

        $mark = substr_compare(
            strtolower($className),
            strtolower($this->classNameSuffix),
            -strlen($this->classNameSuffix)
        );
        if (!empty($this->classNameSuffix) && $mark !== 0) {
            $className = $className . ucfirst($this->classNameSuffix);
        }

        $this->className = ucfirst($className);
        return $this;
    }

    private function getNamespace(): string
    {
        return $this->namespace ?? '';
    }

    protected function getPackage(): string
    {
        return $this->package ?? '';
    }

    protected function getClassName(): string
    {
        return $this->className ?? '';
    }

    protected function getFilename()
    {
        $filename = sprintf("%s\\%s\\%s.php", BASE_PATH, $this->getNamespace(), $this->getClassName());
        $filename = str_replace("\\", "/", $filename);
        $filename = str_replace("//", '/', $filename);

        return $filename;
    }

    protected function simpleBuildFileContent($template)
    {
        $searches = [
            '{{namespace}}',
            '{{package}}',
            '{{className}}'
        ];
        $replaces = [
            $this->getNamespace(),
            $this->getPackage(),
            $this->getClassName()
        ];
        return str_replace($searches, $replaces, $template);
    }
}
