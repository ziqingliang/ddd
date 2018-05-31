<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/29
 * Time: 下午2:22
 */

namespace lanzhi\ddd\tool;


use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager;
use lanzhi\ddd\tool\traits\CollectPropertiesFromConsoleTrait;
use lanzhi\ddd\tool\traits\DataGenerateTrait;
use lanzhi\ddd\tool\traits\DealClassFileNameTrait;
use lanzhi\ddd\tool\traits\PreviewTrait;
use lanzhi\ddd\tool\values\Column;
use lanzhi\ddd\tool\values\Property;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModelCommand extends Command
{
    use DataGenerateTrait;
    use CollectPropertiesFromConsoleTrait;
    use DealClassFileNameTrait;
    use PreviewTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model 
                                {className : 指定模型类名称}
                                {--table=  : 指定对应表名称}
                                {--sub-domain=Core : 指定实体所属子域(首字母大写，默认核心子域)} 
                                {--preview : 预览，不写入文件}
                                ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make a Model class';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->table = $this->option('table');
        $this->validateTableExists($this->table);

        $className = $this->argument('className');
        $subDomain = $this->option('sub-domain');
        $this->setPackage($subDomain);
        $this->setClassName($className);

        $filename = $this->getFilename();
        $this->doConfirmWhenFileExists($filename);

        $template = file_get_contents(__DIR__."/../templates/Model.tpl");
        $content = $this->buildFileContent($template);

        $this->previewOrWriteNow($filename, $content);
    }

    private function validateTableExists($table)
    {
        $this->initMysqlConnection();
        if(!Manager::schema()->hasTable($table)){
            $this->error("Table:$table not exists.");
            die;
        }
    }

    private $table;
    /**
     * @param string $template
     * @return string
     */
    protected function buildFileContent(string $template)
    {
        $this->getTableDefinition($this->table);

        $searches = [
            '{{namespace}}',
            '{{className}}',
            '{{package}}',
            '{{properties}}',
            '{{defaults}}',
            '{{table}}'
        ];

        $replaces = [
            $this->getNamespace(),
            $this->getClassName(),
            $this->getPackage(),
            $this->getProperties(),
            $this->getNoteDefaults(),
            $this->table
        ];

        return str_replace($searches, $replaces, $template);
    }

    private function initMysqlConnection()
    {
        $manager = new Manager();
        $manager->addConnection([
            'driver'    => 'mysql',
            'host'      => getenv('DB_HOST'),
            'database'  => getenv('DB_NAME'),
            'username'  => getenv('DB_USER'),
            'password'  => getenv('DB_PASS'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);
        $manager->setAsGlobal();
    }

    private function getTableDefinition($table)
    {
        $names = Manager::schema()->getColumnListing($table);

        $list = [];
        foreach ($names as $name){
            $col = Manager::connection()->getDoctrineColumn($table, $name);
            
            $column = new Column();
            $column->name = $col->getName();
            $column->type = $col->getType();
            $column->default = $col->getDefault();
            $column->notNull = $col->getNotnull();
            $column->length  = $col->getLength();
            $column->comment = $col->getComment();
            $this->collectColumn($column);
            $list[] = $column->toArray();
        }
//        $this->table(Column::getHeader(), $list);
    }

    /**
     * @var Column[]
     */
    private $columns = [];

    private function collectColumn(Column $column)
    {
        $this->columns[] = $column;
    }

    protected function getProperties():string
    {
        $map = [
            'datetime' => 'string',
            'date'     => 'string',
            'string'   => 'string',
            'integer'  => 'int',
        ];

        foreach ($this->columns as $column){
            $property = new Property();
            $property->name = $column->name;

            $type = strtolower($column->type);
            if(empty($map[$type])){
                $this->error("Unknown property type:{$type}");
                die;
            }
            $property->type = $map[$type];
            $property->default = $column->default;
            $property->label = $column->comment;
            $this->addOneProperty($property);
        }

        $this->buildFromProperties();
        return $this->getNoteProperties();
    }

    protected function setClassName($className)
    {
        $className = str_replace('/', '\\', $className);
        $className = trim($className, '\\');
        $list      = explode('\\', $className);
        $className = array_pop($list);

        if($list){
            $namespace = '\\' . implode('\\', $list);
        }else{
            $namespace = '';
        }

        $this->namespace = sprintf("infra\\models\\%s%s", $this->package, $namespace);

        $suffix = 'model';
        if(substr_compare(strtolower($className), $suffix, -strlen($suffix))!==0){
            $className = $className . "Model";
        }

        $this->className = $className;
        return $this;
    }



}

