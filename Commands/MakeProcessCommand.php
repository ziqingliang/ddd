<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/30
 * Time: 下午12:29
 */

namespace lanzhi\ddd\tool;


use Illuminate\Console\Command;
use lanzhi\ddd\tool\traits\SimpleBuildFileContentTrait;

class MakeProcessCommand extends Command
{
    use SimpleBuildFileContentTrait;

    protected $generatorType = 'processes';
    protected $classNameSuffix = 'Process';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:process 
                                {className : 指定业务过程类名称}
                                {--sub-domain=Core : 指定所属子域(首字母大写，默认核心子域)} 
                                {--preview : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Business Process class';

}