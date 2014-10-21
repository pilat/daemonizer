<?php
namespace Brainfit\Daemonizer;

use SplObjectStorage;

class MasterController
{
    private $daemons;

    public function __construct()
    {
        $this->daemons = new SplObjectStorage;
    }

    public function attach(DaemonizerInterface $daemon)
    {
        $this->daemons->attach($daemon);
    }

    function newInstance(DaemonizerInterface $daemon)
    {
        $pid = pcntl_fork();

        if($pid == 0)
        {
            //we're in the slave now
            new ChildController($daemon);
            exit;
        }
        elseif($pid > 0)
            return $pid;

        throw new \Exception('Fork is unfortunately failed');
    }

    public function run()
    {
        $loop = \React\EventLoop\Factory::create();

        $this->bindSignals();
        $loop->addPeriodicTimer(1, function ()
        {
            pcntl_signal_dispatch();
        });

        foreach ($this->daemons as $daemon)
            $this->daemons[$daemon] = $this->newInstance($daemon);

        //inf
        $loop->run();
    }

    private function bindSignals()
    {
        pcntl_signal(SIGTERM, array($this, "sigHandler"));
        pcntl_signal(SIGINT, array($this, "sigHandler"));
    }

    public function sigHandler()
    {
        if(!$this->daemons)
            exit;

        $this->daemons->rewind();
        while ($this->daemons->valid())
        {
            if($pid = (int)$this->daemons->getInfo())
            {
                //echo 'Send kill to ' . $pid . PHP_EOL;

                posix_kill($pid, SIGTERM);
                $this->daemons->detach($this->daemons->current());
            }

            $this->daemons->next();
        }

        sleep(5);
        exit;
    }
}