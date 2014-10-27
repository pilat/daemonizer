<?php
namespace Brainfit\Daemonizer;

use Cron\CronExpression;
use React\EventLoop\LoopInterface;

class ChildController implements ChildControllerInterface
{
    /** @var DaemonizerInterface */
    private $daemon;

    /** @var LoopInterface */
    private $loop;

    private $schedule;

    private $previousTime;

    /** @var  CronExpression */
    private $cronExpression;

    public function init(LoopInterface $loop = null)
    {
        $this->loop = is_null($loop) ? \React\EventLoop\Factory::create() : $loop;

        $this->loop->addPeriodicTimer(0.1, array($this, 'checkSchedule'));

        $this->bindSignals();
        $this->loop->addPeriodicTimer(1, function ()
        {
            pcntl_signal_dispatch();
        });
    }

    public function attach(DaemonizerInterface $daemon)
    {
        $this->daemon = $daemon;

        $this->schedule = $this->daemon->getSchedule();
        $this->daemon->bootstrap($this->loop);
    }

    public function run()
    {
        $this->loop->run();
    }

    private function bindSignals()
    {
        pcntl_signal(SIGTERM, [$this, "terminate"]);
        pcntl_signal(SIGINT, [$this, "terminate"]);
    }

    public function terminate()
    {
        $this->loop->stop();
        $this->daemon->terminate();

        // @codeCoverageIgnoreStart
        if (!defined('PHPUNIT_TEST'))
            define('PHPUNIT_TEST', false);
        // @codeCoverageIgnoreEnd

        if (!PHPUNIT_TEST)
            die;
    }


    public function checkSchedule()
    {
        $timestamp = microtime(true);
        $hour = date('G');

        if (is_numeric($this->schedule))
        {
            //If you need to perform at intervals, then check to see whether early to perform
            if (isset($this->previousTime) && $timestamp <= $this->previousTime + $this->schedule)
                return;

            $this->previousTime = $timestamp;
        }
        else if (is_array($this->schedule))
        {
            if ((isset($this->previousTime) && $this->previousTime == $hour) || !in_array($hour, $this->schedule))
                return;

            $this->previousTime = $hour;
        }
        else if (is_string($this->schedule))
        {
            $nextRunDate = false;

            try
            {
                //See https://github.com/mtdowling/cron-expression
                if (!isset($this->cronExpression))
                    $this->cronExpression = CronExpression::factory($this->schedule);

                $nextRunDate = $this->cronExpression->getNextRunDate();
            } catch (\Exception $e)
            {
                $this->terminate();
            }

            //first step always skipped
            if (!isset($this->previousTime))
                $this->previousTime = $nextRunDate;

            if ($this->previousTime == $nextRunDate)
                return;

            $this->previousTime = $nextRunDate;

        }
        else
        {
            //once
            if (isset($this->previousTime))
                return;

            $this->previousTime = true;
        }

        //execute daemon now

        try
        {
            $this->daemon->run();
        } catch (\Exception $e)
        {
            $this->terminate();
        }
    }
}