<?php

namespace Brainfit\Examples;

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;

class TickExample implements DaemonizerInterface
{
    public function getSchedule()
    {
        //seconds
        return 1;
    }

    public function bootstrap(LoopInterface $loop)
    {
        $this->demoLogger('Bootstrap TickExample');
    }

    public function run()
    {
        $this->demoLogger('Run TickExample (every 1s)');
    }

    public function terminate()
    {
        $this->demoLogger('Terminate TickExample');
    }

    private function demoLogger($message)
    {
        file_put_contents(getcwd() . '/log.log', $message . PHP_EOL, FILE_APPEND + LOCK_EX);
    }
}