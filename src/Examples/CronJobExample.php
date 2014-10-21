<?php

namespace Brainfit\Examples;

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;

class CronJobExample implements DaemonizerInterface
{
    public function getSchedule()
    {
        //hours
        return array(1, 2, 3, 4, 5, 10, 12, 15, 19, 23);
    }

    public function bootstrap(LoopInterface $loop)
    {
        $this->demoLogger('Bootstrap CronJobExample');
    }

    public function run()
    {
        $this->demoLogger('Run CronJobExample (hourly)');
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