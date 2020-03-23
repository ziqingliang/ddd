<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/28
 * Time: 下午2:53
 */

namespace ziqing\ddd\tool;


use Illuminate\Console\Command;
use ziqing\ddd\tool\traits\DataGeneratorTrait;
use ziqing\ddd\tool\traits\DealClassFileNameTrait;
use ziqing\ddd\tool\traits\PreviewTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateEntityCommand extends Command
{
    use DataGeneratorTrait;
    use DealClassFileNameTrait;
    use PreviewTrait;

    protected $generatorType = 'entities';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'regen:entity 
                                {entity-filename}
                                {--preview : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'regenerate the Domain Entity class from an existing Entity file; ';

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $this->argument('entity-filename');

        if(!is_file($filename)){
            $this->error("File:$filename not exists!");
            die;
        }

        $this->cacheLines($filename);
        $this->parseFile($filename);

        $template = $this->getTemplate();
        $content  = $this->buildFileContent($template);
        $this->previewOrWriteNow($filename, $content);

        return 0;
    }


}
