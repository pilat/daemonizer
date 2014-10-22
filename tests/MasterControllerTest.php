<?php

use Brainfit\Daemonizer\ChildControllerInterface;
use Brainfit\Daemonizer\MasterController;
use Brainfit\Examples\TickExample;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;

class MasterControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoopInterface */
    private $loop;

    private function createLoop()
    {
        return new StreamSelectLoop();
    }

    public function setUp()
    {
        if (!defined('PHPUNIT_TEST'))
            define('PHPUNIT_TEST', true);

        $this->loop = $this->createLoop();
    }

    public function testControllerWithoutDaemons()
    {
        $mock = $this->getChildMock();
        $mock->expects($this->never())
            ->method('run');

        $controller = new MasterController($this->loop, $mock);
        $controller->start();

        $this->manyTicks();
    }

    public function testControllerWithOneDaemon()
    {
        $child = $this->getChildMock();
        $daemon = $this->getDaemonMock();

        $child->expects($this->once())
            ->method('run');

        $controller = new MasterController($this->loop, $child);

        $controller->attach($daemon);

        $controller->start();

        $this->manyTicks();
    }

    public function testControllerWithManyDaemons()
    {
        $child = $this->getChildMock();
        $daemon = $this->getDaemonMock();
        $daemon2 = clone $daemon;

        $child->expects($this->exactly(2))
            ->method('run');

        $controller = new MasterController($this->loop, $child);

        $controller->attach($daemon);
        $controller->attach($daemon2);

        $controller->start();

        $this->manyTicks();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ChildControllerInterface
     */
    private function getChildMock()
    {
        return $this->getMock('\Brainfit\Daemonizer\ChildController');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|TickExample
     */
    private function getDaemonMock()
    {
        return $this->getMock('\Brainfit\Examples\TickExample');
    }

    private function manyTicks()
    {
        for($i=0;$i<20;$i++)
            $this->loop->tick();
    }
}