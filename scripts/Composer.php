<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/31
 * Time: 下午7:41
 */

namespace lanzhi\ddd\scripts;


use Composer\Script\Event;
use Symfony\Component\Process\Process;

class Composer
{
    /**
     * Handle the post-autoload-dump Composer event.
     *
     * @param  \Composer\Script\Event  $event
     * @return void
     */
    public static function postAutoloadDump(Event $event)
    {
        $command = PHP_BINARY." artisan package:discover";
        $process = new Process($command);
        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty') && is_writable('/dev/tty')) {
//            $process->setTty(true);
        }

        $process->setTty(false);
        $process->run(function ($type, $line) {
            echo $line, "\n";
        });
    }

}
