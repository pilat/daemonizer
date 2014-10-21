<?php
namespace Brainfit\Daemonizer;

use React\EventLoop\LoopInterface;

class ChildController
{
    /** @var DaemonizerInterface */
    private $daemon;

    /** @var LoopInterface */
    private $loop;

    private $schedule;

    private $prevExecuteTime;

    public function __construct(DaemonizerInterface $daemon)
    {
        $this->daemon = $daemon;

        $this->loop = \React\EventLoop\Factory::create();

        $this->schedule = $this->daemon->getSchedule();
        $this->daemon->bootstrap($this->loop);

        $this->loop->addPeriodicTimer(0.1, array($this, 'checkSchedule'));

        $this->bindSignals();
        $this->loop->addPeriodicTimer(1, function ()
        {
            pcntl_signal_dispatch();
        });

        $this->loop->run();
    }

    private function bindSignals()
    {
        pcntl_signal(SIGTERM, array($this, "sigHandler"));
        pcntl_signal(SIGINT, array($this, "sigHandler"));
    }

    public function sigHandler()
    {
        $this->daemon->terminate();
        exit;
    }


    public function checkSchedule()
    {
        $iTime = intval(microtime(true));
        $iHour = date('G');

        if(is_numeric($this->schedule))
        {
            //If you need to perform at intervals, then check to see whether early to perform
            if(isset($this->prevExecuteTime) && $this->prevExecuteTime + $this->schedule > $iTime)
                return;

            $this->prevExecuteTime = $iTime;
        }
        else if(is_array($this->schedule))
        {
            //If you need to perform on the clock, then check whether it is time to perform
            if(isset($this->prevExecuteTime) && ($this->prevExecuteTime == $iHour
                    || !in_array($iHour, $this->schedule))
            )
                return;

            $this->prevExecuteTime = $iHour;
        }
        else
        {
            //once
            if(isset($this->prevExecuteTime))
                return;

            $this->prevExecuteTime = true;
        }


        $this->daemon->run();
    }
}