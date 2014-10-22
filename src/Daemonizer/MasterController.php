<?php
namespace Brainfit\Daemonizer;

use React\EventLoop\LoopInterface;
use SplObjectStorage;

class MasterController
{
    private $daemons;
    /** @var LoopInterface */
    private $loop;
    /** @var ChildControllerInterface */
    private $child;

    public function __construct(LoopInterface $loop, ChildControllerInterface $child)
    {
        $this->daemons = new SplObjectStorage;
        $this->loop = $loop;
        $this->child = $child;
    }

    public function attach(DaemonizerInterface $daemon)
    {
        $this->daemons->attach($daemon);
    }

    function newInstance(DaemonizerInterface $daemon)
    {
        // @codeCoverageIgnoreStart
        if (!defined('PHPUNIT_TEST'))
            define('PHPUNIT_TEST', false);
        // @codeCoverageIgnoreEnd

        $pid = PHPUNIT_TEST ? 0 : pcntl_fork();

        if($pid == 0)
        {
            //we're in the slave now
            $child = clone $this->child;
            $child->init();
            $child->attach($daemon);
            $child->run();

            if (!PHPUNIT_TEST)
                exit;
        }
        elseif($pid > 0)
            return $pid;

        if (PHPUNIT_TEST)
            return mt_rand(50000,60000);
        else
            throw new \Exception('Fork is unfortunately failed');
    }

    public function start()
    {
        $this->bindSignals();
        $this->loop->addPeriodicTimer(1, function ()
        {
            pcntl_signal_dispatch();
        });

        foreach ($this->daemons as $daemon)
            $this->daemons[$daemon] = $this->newInstance($daemon);
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
                posix_kill($pid, SIGTERM);
                $this->daemons->detach($this->daemons->current());
            }

            $this->daemons->next();
        }

        sleep(5);
        exit;
    }
}