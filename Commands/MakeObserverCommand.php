<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/30
 * Time: 下午12:29
 */

namespace lanzhi\ddd\tool;


use Illuminate\Console\Command;
use lanzhi\ddd\tool\traits\ComplexBuildFileContentTrait;

class MakeObserverCommand extends Command
{
    use ComplexBuildFileContentTrait;

    protected $generatorType = 'observers';
    protected $classNameSuffix = 'Observer';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:observer 
                                {entity-filename : 指定被观察的实体类文件}
                                {--class-name=   : 指定观察者类名称}
                                {--preview       : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Domain Observer class';


}