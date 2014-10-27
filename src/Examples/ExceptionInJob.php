<?php

namespace Brainfit\Examples;

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;

class ExceptionInJob implements DaemonizerInterface
{
    public function getSchedule()
    {
        return 1;
    }

    public function bootstrap(LoopInterface $loop)
    {
        $this->demoLogger('Bootstrap ExceptionFromJob');
    }

    public function run()
    {
        $this->demoLogger('ExceptionFromJob uncaught exception.. child died, terminate method executed');
        
        throw new \Exception('Exception test', 123);
    }

    public function terminate()
    {
        $this->demoLogger('Terminate ExceptionFromJob');
    }

    private function demoLogger($message)
    {
        file_put_contents(getcwd() . '/log.log', $message . PHP_EOL, FILE_APPEND + LOCK_EX);
    }
}