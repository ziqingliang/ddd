<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/24
 * Time: 下午12:17
 */

namespace lanzhi\ddd\tool;


use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    private $templateDir;

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->templateDir = dirname(__DIR__);
        if($input->hasOption('templates-dir')){
            $dir = $input->getOption('templates-dir');
            if(!is_dir($dir)){
                throw new InvalidArgumentException("{$dir} not a dir");
            }

            $this->templateDir = $dir;
        }

        parent::initialize($input, $output);
    }

    protected function getTemplate($name)
    {
        $file = sprintf("%s/%s.tpl", $this->templateDir, $name);
        if(!file_exists($file)){
            throw new \InvalidArgumentException("file:{$file} not exists; name:{$name}");
        }
        return file_get_contents($file);
    }

}
