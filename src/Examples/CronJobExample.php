<?php

namespace Brainfit\Examples;

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;

class CronJobExample implements DaemonizerInterface
{
    public function getSchedule()
    {
        //Cron-liked style: "m h dom mon dow"
        //See https://github.com/mtdowling/cron-expression

        return '* * * * *';
    }

    public function bootstrap(LoopInterface $loop)
    {
        $this->demoLogger('Bootstrap CronJobExample');
    }

    public function run()
    {
        $this->demoLogger('Run CronJobExample (every minute)');
    }

    public function terminate()
    {
        $this->demoLogger('Terminate CronJobExample');
    }

    private function demoLogger($message)
    {
        file_put_contents(getcwd() . '/log.log', $message . PHP_EOL, FILE_APPEND + LOCK_EX);
    }
}