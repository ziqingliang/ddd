<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/29
 * Time: 下午2:22
 */

namespace ziqing\ddd\tool;

use ziqing\ddd\tool\traits\DealClassFileNameTrait;
use ziqing\ddd\tool\traits\PreviewTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeDependencyCommand extends BaseCommand
{
    use DealClassFileNameTrait;
    use PreviewTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dependency 
                                {className : 指定依赖类名称}
                                {--preview : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Dependency class';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $this->argument('className');
        $this->setClassName($className);

        $filename = $this->getFilename();
        $this->doConfirmWhenFileExists($filename);

        $template = file_get_contents(__DIR__ . "/../templates/Dependency.tpl");
        $content = $this->simpleBuildFileContent($template);

        $this->previewOrWriteNow($filename, $content);
        return 0;
    }

    protected function setClassName($className)
    {
        list($namespace, $className) = $this->buildNamespaceAndClass($className);
        $this->namespace = sprintf("infra\\dependencies%s", $namespace);

        $suffix = 'Dependency';
        if (substr_compare(strtolower($className), strtolower($suffix), -strlen($suffix)) !== 0) {
            $className = $className . $suffix;
        }

        $this->className = $className;
        return $this;
    }
}
