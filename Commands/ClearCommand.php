<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/31
 * Time: 上午11:45
 */

namespace lanzhi\ddd\tool;


use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clear an existing project';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $list = $this->getTopStructure();
        foreach ($list as list($filename, $isDir)){
            $filename = BASE_PATH.DIRECTORY_SEPARATOR.$filename;
            $name = pathinfo($filename, PATHINFO_FILENAME);

            if($isDir && is_dir($filename)){
                $this->clearDir($filename);
            }elseif(file_exists($filename) && $name!='composer' && $name!='composer' && basename($filename)!='.gitignore'){
                @unlink($filename);
                $this->warn("file:$filename is deleted.");
            }
        }

        //恢复原有的 composer 配置
        @rename(BASE_PATH.'/.composer.json.bak', BASE_PATH.'/composer.json');
        @rename(BASE_PATH.'/.composer.lock.bak', BASE_PATH.'/composer.lock');
        @rename(BASE_PATH.'/.gitignore.bak',     BASE_PATH.'/.gitignore');

        //更新 composer
        $composer = $this->findComposer();
        $process = new Process($composer.' update --no-scripts', BASE_PATH, null, null, null);
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
        $prefix = BASE_PATH;

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
