<?php

namespace Brainfit\Examples;

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;

class TickExample2 implements DaemonizerInterface
{
    public function getSchedule()
    {
        //seconds
        return 5;
    }

    public function bootstrap(LoopInterface $loop)
    {
        $this->demoLogger('Bootstrap TickExample 2');
    }

    public function run()
    {
        $this->demoLogger('Run TickExample 2  (every 5s)');
    }

    public function terminate()
    {
        $this->demoLogger('Terminate TickExample 2');
    }

    private function demoLogger($message)
    {
        file_put_contents(getcwd() . '/log.log', $message . PHP_EOL, FILE_APPEND + LOCK_EX);
    }
}