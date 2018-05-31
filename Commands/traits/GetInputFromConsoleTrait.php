<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/29
 * Time: 下午6:51
 */

namespace lanzhi\ddd\tool\traits;


trait GetInputFromConsoleTrait
{
    protected function getSubDomainFromConsole()
    {
        $subDomain = $this->option('sub-domain');
        $pattern = '/[a-zA-Z]+/';
        if (preg_match($pattern, $subDomain) !== false) {
            return ucfirst(strtolower($subDomain));
        }
        $this->error("Invalid sub-domain:{$subDomain}; Sub-domain name must match pattern:{$pattern}");
        die;
    }

    protected function getClassNameFromConsole()
    {
        $className = $this->argument('className');
        $pattern = '/[a-zA-Z][a-zA-Z\/\\\]*/';
        if(preg_match($pattern, $className)===false){
            $this->error("Invalid class name:{$className}; Class name must match pattern:{$pattern}");
            die;
        }
        $className = str_replace('\\', '/', $className);
        $className = trim($className, '/');
        return $className;
    }
}