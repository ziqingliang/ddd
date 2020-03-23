<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/31
 * Time: 下午9:43
 */

namespace ziqing\ddd\tool\traits;


trait GetBasePathTrait
{
    protected $basePath;

    private function getBasePath()
    {
        if($this->hasOption('base-path')){
            $basePath = $this->option('base-path');
        }
        $basePath = $basePath ?? getcwd();

        if(!is_dir($basePath)){
            $this->error("Invalid path:{$basePath}");
            die;
        }

        if(!is_writable($basePath)){
            $this->error("Not writable path:{$basePath}");
            die;
        }
        $this->basePath = $basePath;
        return $basePath;
    }
}
