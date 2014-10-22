<?php

namespace Brainfit\Examples;

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;

class HourlyJobExample implements DaemonizerInterface
{
    public function getSchedule()
    {
        //hours
        return array(1, 2, 3, 4, 5, 10, 12, 15, 19, 23);
    }

    public function bootstrap(LoopInterface $loop)
    {
        $this->demoLogger('Bootstrap HourlyJobExample');
    }

    public function run()
    {
        $this->demoLogger('Run HourlyJobExample (hourly)');
    }

    public function terminate()
    {
        $this->demoLogger('Terminate HourlyJobExample');
    }

    private function demoLogger($message)
    {
        file_put_contents(getcwd() . '/log.log', $message . PHP_EOL, FILE_APPEND + LOCK_EX);
    }
}