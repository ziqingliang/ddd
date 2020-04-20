<?php

/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/30
 * Time: 下午12:29
 */

namespace ziqing\ddd\tool;

use ziqing\ddd\tool\traits\SimpleBuildFileContentTrait;

class MakeServiceCommand extends BaseCommand
{
    use SimpleBuildFileContentTrait;

    protected $generatorType = 'services';
    protected $classNameSuffix = 'Service';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service 
                                {className : 指定领域服务类名称}
                                {--sub-domain=Core : 指定所属子域(首字母大写，默认核心子域)} 
                                {--preview : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Domain Service class';
}
