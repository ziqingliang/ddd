<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/30
 * Time: 下午4:08
 */

namespace ziqing\ddd\tool\traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait SimpleBuildFileContentTrait
{
    use GetInputFromConsoleTrait;
    use DealClassFileNameTrait;
    use PreviewTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $className = $this->className ?? $this->getClassNameFromConsole();
        $subDomain = $this->subDomain ?? $this->getSubDomainFromConsole();

        $this->setPackage($subDomain);
        $this->setClassName($className);

        $filename = $this->getFilename();
        $this->doConfirmWhenFileExists($filename);

        $template = file_get_contents(__DIR__ . "/../../templates/{$this->classNameSuffix}.tpl");
        $content = $this->simpleBuildFileContent($template);

        $this->previewOrWriteNow($filename, $content);

        return 0;
    }
}
