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
use lanzhi\ddd\tool\traits\SimpleBuildFileContentTrait;

class MakeRepositoryCommand extends Command
{
    use ComplexBuildFileContentTrait;

    protected $generatorType = 'repositories';
    protected $classNameSuffix = 'Repository';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository 
                                {entity-filename : 指定实体类文件}
                                {--preview       : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Domain Repository class';


}