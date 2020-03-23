<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/6/1
 * Time: 下午2:30
 */

namespace ziqing\ddd\tool\helps;


use ziqing\ddd\Entity;

class EntityFile
{
    private $filename;
    /**
     * @var \ReflectionClass
     */
    private $reflection;

    private $package;
    private $namespace;
    private $className;

    /**
     * EntityFileParser constructor.
     * @param $filename
     * @throws \Exception
     */
    public function __construct($filename)
    {
        if(!file_exists($filename)){
            throw new \Exception("File:$filename not exists.");
        }
        $this->filename = $filename;
        $this->staticParse();
        $this->dynamicParse();
    }

    /**
     * @throws \Exception
     */
    private function staticParse()
    {
        $namespace = '';
        foreach (file($this->filename) as $line){
            $line = trim($line);
            if($line && substr_compare($line, 'namespace ', 0, 10)==0){
                $list = preg_split("/ +/", $line);
                $namespace = trim($list[1], " ;");
            }
            if($line && substr_compare($line, 'class ', 0, 6)==0){
                $list = preg_split("/ +/", $line);
                $class = trim($list[1]);
                $this->className = $class;
            }
            $line = trim($line, " *");
            if($line && substr_compare($line, '@package ', 0, 8)==0){
                $list = preg_split("/ +/", $line);
                $package = trim($list[1]);
            }
        }
        if(empty($class)){
            throw new \Exception("File:{$this->filename} isn't a valid class file.");
        }

        $this->package   = $package;
        $this->namespace = $namespace;
        $this->className = $class;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function dynamicParse()
    {
        include $this->filename;

        //验证是否为有效的实体类文件
        $reflection = new \ReflectionClass($this->getFullClassName());
        if(!$reflection->isSubclassOf(Entity::class)){
            throw new \Exception("File:{$this->filename} is not an Entity class file.");
        }

        $this->reflection = $reflection;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function getSubDomain()
    {
        return $this->package;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getFullClassName()
    {
        return $this->namespace . '\\' . $this->className;
    }

    public function getModelNamespace()
    {
        return "infra\\models\\{$this->package}";
    }

    public function getModelClassName()
    {
        return $this->className."Model";
    }

    public function getModelFullClassName()
    {
        return $this->getModelNamespace() . "\\" . $this->getModelClassName();
    }

    public function getModelFilename()
    {
        $filename = sprintf("%s\\%s.php", BASE_PATH, $this->getModelFullClassName());
        $filename = str_replace("\\", "/", $filename);
        $filename = str_replace("//", '/', $filename);
        return $filename;
    }

    public function getFactoryNamespace()
    {
        return "domains\\{$this->package}\\factories";
    }

    public function getFactoryClassName()
    {
        return $this->className."Factory";
    }

    public function getFactoryFullClassName()
    {
        return $this->getFactoryNamespace() . "\\" . $this->getFactoryClassName();
    }

    public function getFactoryFilename()
    {
        $filename = sprintf("%s\\%s.php", BASE_PATH, $this->getFactoryFullClassName());
        $filename = str_replace("\\", "/", $filename);
        $filename = str_replace("//", '/', $filename);
        return $filename;
    }

    public function getRepositoryNamespace()
    {
        return "domains\\{$this->package}\\repositories";
    }

    public function getRepositoryClassName()
    {
        return $this->className."Repository";
    }

    public function getRepositoryFullClassName()
    {
        return $this->getRepositoryNamespace() . "\\" . $this->getRepositoryClassName();
    }

    public function getRepositoryFilename()
    {
        $filename = sprintf("%s\\%s.php", BASE_PATH, $this->getRepositoryFullClassName());
        $filename = str_replace("\\", "/", $filename);
        $filename = str_replace("//", '/', $filename);
        return $filename;
    }

    public function getTableName()
    {
        return $this->getPackage() . $this->getClassName();
    }

    public function getTypes()
    {
        return call_user_func([$this->getFullClassName(), 'types']);
    }

    public function getLabels()
    {
        return call_user_func("{$this->getFullClassName()}::labels");
    }
}
