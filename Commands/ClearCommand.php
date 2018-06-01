<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/31
 * Time: 上午11:45
 */

namespace lanzhi\ddd\installer;


use Illuminate\Console\Command;
use lanzhi\ddd\installer\traits\GetBasePathTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ClearCommand extends Command
{
    use GetBasePathTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:clear
                                    {--base-path= : 清理代码的基准目录，默认当前目录}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clear an existing project';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->basePath = $this->getBasePath();
        
        $list = $this->getTopStructure();
        foreach ($list as list($filename, $isDir)){
            $filename = $this->basePath.DIRECTORY_SEPARATOR.$filename;
            $name = pathinfo($filename, PATHINFO_FILENAME);

            if($isDir && is_dir($filename)){
                $this->clearDir($filename);
            }elseif(file_exists($filename) && $name!='composer' && $name!='composer' && basename($filename)!='.gitignore'){
                @unlink($filename);
                $this->warn("file:$filename is deleted.");
            }
        }

        //恢复原有的 composer 配置
        @rename($this->basePath.'/.composer.json.bak', $this->basePath.'/composer.json');
        @rename($this->basePath.'/.composer.lock.bak', $this->basePath.'/composer.lock');
        @rename($this->basePath.'/.gitignore.bak',     $this->basePath.'/.gitignore');

        //更新 composer
        $composer = $this->findComposer();
        $process = new Process($composer.' update --no-scripts', $this->basePath, null, null, null);
        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $this->info($line);
        });
    }

    private function getTopStructure()
    {
        $resourceDir = __DIR__."/../resource";
        $handle = opendir($resourceDir);

        $list = [];
        while($dir = readdir($handle)){
            if($dir!=='.' && $dir!=='..'){
                $full = $resourceDir.DIRECTORY_SEPARATOR.$dir;
                $list[] = [$dir, is_dir($full)];
            }
        }
        return $list;
    }

    private function clearDir($root)
    {
        $root   = realpath($root);
        $prefix = $this->basePath;

        if(substr_compare($root, $prefix, 0, strlen($prefix))!==0){
            $this->error("Operation forbidden, directory:{$root} not belongs to the currently project.");
            die;
        }
        $handle = opendir($root);
        while($dir = readdir($handle)){
            if($dir!=='.' && $dir!=='..'){
                $filename = $root.DIRECTORY_SEPARATOR.$dir;
                if(is_dir($filename)){
                    $this->clearDir($filename);
                }else{
                    @unlink($filename);
                    $this->warn("file:$filename is deleted.");
                }
            }
        }
        closedir($handle);
        rmdir($root);
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }

        return 'composer';
    }
}
