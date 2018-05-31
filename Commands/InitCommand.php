<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/31
 * Time: 上午11:45
 */

namespace lanzhi\ddd\tool;


use Illuminate\Console\Command;
use lanzhi\ddd\tool\traits\GetBasePathTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class InitCommand extends Command
{
    use GetBasePathTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:init 
                                    {--base-path= : 初始化基准目录，默认当前目录}
                                    {--force : 如果项目已存初始化过，则清除后重新初始化}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or init a laravel DDD application';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->basePath = $this->getBasePath();
        if (! $input->getOption('force')) {
            $this->verifyApplicationExistence();
        }

        $this->info("Application initializing ... ...");
        $composerContent = $this->mergeComposerContent();

        $this->copyFilesAndReplaceComposer($composerContent);

        $composer = $this->findComposer();
        $commands = [
            $composer.' install --no-scripts',
            $composer.' run-script post-autoload-dump',
            PHP_BINARY." -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            PHP_BINARY." -r \"file_exists('.domain.env') || copy('.domain.env.example', '.domain.env');\"",
            PHP_BINARY." artisan key:generate"
        ];

        if ($input->getOption('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value.' --no-ansi';
            }, $commands);
        }

        $process = new Process(implode(' && ', $commands), $this->basePath, null, null, null);
        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $this->info($line);
        });

        $this->info('Application has been ready!');
    }

    private function copyFilesAndReplaceComposer($composerContent)
    {
        //首先备份原有的 composer.json .gitignore 等文件
        @copy($this->basePath.'/composer.json', $this->basePath.'/.composer.json.bak');
        @copy($this->basePath.'/composer.lock', $this->basePath.'/.composer.lock.bak');
        @unlink($this->basePath.'/composer.lock');
        @copy($this->basePath.'/.gitignore', $this->basePath.'/.gitignore.bak');

        $resourcePath = realpath(__DIR__."/../resource");
        $files = $this->getAllFiles($resourcePath);
        foreach ($files as $file){
            $destination = str_replace($resourcePath, $this->basePath, $file);
            $this->copyFile($file, $destination);
        }

        file_put_contents($this->basePath.'/composer.json', $composerContent);
    }

    private function getAllFiles($dir)
    {
        assert(is_dir($dir));
        $dir = realpath($dir);
        $handle = opendir($dir);
        $files = [];
        while($name = readdir($handle)){
            if($name=='.' || $name=='..'){
                continue;
            }
            $file = $dir . DIRECTORY_SEPARATOR . $name;
            if(is_dir($file)){
                $files = array_merge($files, $this->getAllFiles($file));
            }elseif(is_file($file)){
                $files[] = $file;
            }
        }
        return $files;
    }

    private function copyFile($target, $destination)
    {
        $this->makeDir(dirname($destination));
        if(!copy($target, $destination)){
            $this->error("Copy file failed");
            die;
        }
    }

    private function makeDir($dir)
    {
        if(is_dir($dir)){
            return ;
        }
        if(!mkdir($dir, 0777, true)){
            $this->error("Create directory:{$dir} failed");
            die;
        }
    }

    private function mergeComposerContent()
    {
        $initComposer = file_get_contents(__DIR__."/../resource/composer.json");
        $rootComposer = file_exists($this->basePath.'/composer.json') ? file_get_contents($this->basePath.'/composer.json') : null;
        $initJson = json_decode(trim($initComposer), true);
        $rootJson = $rootComposer ? json_decode(trim($rootComposer), true) : [];

        $json = $this->mergeJson($initJson, $rootJson);
        $json['require']['lanzhi/laravel-ddd'] = "^{$this->getApplication()->getVersion()}";

        $json = json_encode($json, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        return str_replace("@php", '@'.PHP_BINARY, $json);
    }

    private function mergeJson($jsonA, $jsonB)
    {
        foreach ($jsonB as $key=>$value){
            if(is_int($key) && !in_array($value, $jsonA)){
                $jsonA[] = $value;
            }
            if(!is_int($key)){
                if(isset($jsonA[$key]) && is_array($jsonA[$key]) && is_array($jsonB[$key])){
                    $jsonA[$key] = $this->mergeJson($jsonA[$key], $jsonB[$key]);
                }else{
                    $jsonA[$key] = $jsonB[$key];
                }
            }
        }

        return $jsonA;
    }

    /**
     * Verify that the application does not already exist.
     *
     * @param  string  $directory
     * @return void
     */
    private function verifyApplicationExistence()
    {
        $mark = false;
        $list = $this->getTopStructure();
        foreach ($list as list($filename, $isDir)){
            $fullName = $this->basePath . DIRECTORY_SEPARATOR . $filename;
            if($isDir && is_dir($fullName)){
                $mark = true;
                break;
            }
        }
        if($mark){
            $this->error('Application already exists!');
            die;
        }
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

    /**
     * Make sure the storage and bootstrap cache directories are writable.
     *
     * @param  string  $appDirectory
     * @return $this
     */
    protected function prepareWritableDirectories($appDirectory)
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->chmod($appDirectory.DIRECTORY_SEPARATOR."bootstrap/cache", 0755, 0000, true);
            $filesystem->chmod($appDirectory.DIRECTORY_SEPARATOR."storage", 0755, 0000, true);
        } catch (IOExceptionInterface $e) {
            $this->warn('You should verify that the "storage" and "bootstrap/cache" directories are writable.');
        }

        return $this;
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