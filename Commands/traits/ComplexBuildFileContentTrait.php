<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/30
 * Time: 下午6:07
 */

namespace ziqing\ddd\tool\traits;

use ziqing\ddd\Entity;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ComplexBuildFileContentTrait
{
    use SimpleBuildFileContentTrait {
        execute as simpleExecute;
        simpleBuildFileContent as xxBuildFileContent;
    }

    protected $subDomain;
    protected $entityClassName;
    protected $entityFullClassName;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $this->argument('entity-filename');
        if (!is_file($filename)) {
            $this->error("File:$filename not exists!");
            die;
        }

        $this->parseEntityFile($filename);
        $this->validEntityFile($filename);

        $this->simpleExecute($input, $output);
        return 0;
    }

    protected function validEntityFile($filename)
    {
        include $filename;
        try {
            $reflection = new \ReflectionClass($this->entityFullClassName);
            if (!$reflection->isSubclassOf(Entity::class)) {
                $this->error("File:$filename is not an Entity file.");
                die;
            }
        } catch (\Throwable $exception) {
            $error = sprintf("Error:%s; file:%s:%s", $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $this->error($error);
            die;
        }
    }

    private function parseEntityFile($filename)
    {
        $namespace = '';
        foreach (file($filename) as $line) {
            $line = trim($line);
            if ($line && substr_compare($line, 'namespace ', 0, 10) == 0) {
                $list = preg_split("/ +/", $line);
                $namespace = trim($list[1], " ;");
            }
            if ($line && substr_compare($line, 'class ', 0, 6) == 0) {
                $list = preg_split("/ +/", $line);
                $class = trim($list[1]);
                $this->className = $class;
            }
            $line = trim($line, " *");
            if ($line && substr_compare($line, '@package ', 0, 8) == 0) {
                $list = preg_split("/ +/", $line);
                $package = trim($list[1]);
            }
        }
        if (empty($class)) {
            $this->error("This isn't a valid class file. ");
            die;
        }

        $this->className = $this->hasOption('class-name') ? $this->option('class-name') : $class;
        $this->subDomain = $package;
        $this->entityClassName     = $class;
        $this->entityFullClassName = $namespace . '\\' . $class;
    }

    protected function simpleBuildFileContent($template)
    {
        $template = $this->xxBuildFileContent($template);
        $searches = [
            '{{entityClassName}}',
            '{{entityFullClassName}}'
        ];
        $replaces = [
            $this->entityClassName,
            $this->entityFullClassName
        ];
        return str_replace($searches, $replaces, $template);
    }
}
