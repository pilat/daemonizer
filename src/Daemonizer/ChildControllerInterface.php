<?php
namespace Brainfit\Daemonizer;

use React\EventLoop\LoopInterface;

interface ChildControllerInterface
{
    public function init(LoopInterface $loop = null);
    public function attach(DaemonizerInterface $daemon);
    public function run();
}