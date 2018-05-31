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

class MakeSpecificationCommand extends Command
{
    use SimpleBuildFileContentTrait;

    protected $generatorType = 'specifications';
    protected $classNameSuffix = 'Specification';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:specification 
                                {className : 指定领域规格类名称}
                                {--sub-domain=Core : 指定所属子域(首字母大写，默认核心子域)} 
                                {--preview : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Domain Specification class';


}