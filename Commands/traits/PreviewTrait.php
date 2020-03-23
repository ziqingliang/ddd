<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/29
 * Time: 下午6:19
 */

namespace ziqing\ddd\tool\traits;


trait PreviewTrait
{
    protected function previewOrWriteNow($filename, $content)
    {
        if($this->option('preview')){
            echo $content;
            $this->info("There nothing has been done");
            return false;
        }else{
            $dir = dirname($filename);
            if(!is_dir($dir) && !mkdir($dir, 0777, true)){
                $this->alert("Can't make dir:{$dir}");
            }
            file_put_contents($filename, $content);
            $this->info("Successfully. File:$filename has been made.");
            return true;
        }
    }

    protected function doConfirmWhenFileExists($filename)
    {
        if($this->hasOption('preview') && $this->option('preview')){
            return false;
        }
        if($this->hasOption('force') && $this->option('force')){
            return false;
        }
        if(!file_exists($filename)){
            return false;
        }
        $this->warn("File:$filename already exists.");
        if(!$this->confirm("You will override it?")){
            $this->info("Nothing has done.");
            die ;
        }else{
            $this->warn("File:$filename will be override!!!");
            return true;
        }
    }
}
