<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/29
 * Time: 下午5:45
 */

namespace lanzhi\ddd\tool\traits;


trait DealClassFileNameTrait
{
    private $namespace;
    private $package;
    private $className;

    protected function setPackage($package)
    {
        $this->package = ucfirst(strtolower($package));
        return $this;
    }

    protected function setClassName($className)
    {
        $className = str_replace('/', '\\', $className);
        $className = trim($className, '\\');
        $list = explode('\\', $className);
        $className = array_pop($list);

        if($list){
            $namespace = '\\' . implode('\\', $list);
        }else{
            $namespace = '';
        }

        $this->namespace = sprintf("domains\\%s\\%s%s", $this->package, $this->generatorType, $namespace);

        if(
            !empty($this->classNameSuffix) &&
            substr_compare(strtolower($className), strtolower($this->classNameSuffix), -strlen($this->classNameSuffix))!==0
        ){
            $className = $className . ucfirst($this->classNameSuffix);
        }

        $this->className = $className;
        return $this;
    }

    private function getNamespace():string
    {
        return $this->namespace ?? '';
    }

    protected function getPackage():string
    {
        return $this->package ?? '';
    }

    protected function getClassName():string
    {
        return $this->className ?? '';
    }

    protected function getFilename()
    {
        $filename = sprintf("%s\\%s\\%s.php", BASE_PATH, $this->getNamespace(), $this->getClassName());
        $filename = str_replace("\\", "/", $filename);

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