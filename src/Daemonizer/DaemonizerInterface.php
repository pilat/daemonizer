<?php

namespace Brainfit\Daemonizer;

use React\EventLoop\LoopInterface;

interface DaemonizerInterface
{
    public function getSchedule();

    public function bootstrap(LoopInterface $loop);
    public function run();
    public function terminate();
}