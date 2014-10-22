<?php

use Brainfit\Daemonizer\DaemonizerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;

class ChildControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoopInterface */
    private $loop;

    private function createLoop()
    {
        return new StreamSelectLoop();
    }

    public function setUp()
    {
        if(!defined('PHPUNIT_TEST'))
            define('PHPUNIT_TEST', true);

        $this->loop = $this->createLoop();
    }

    public function testDaemonChildEverySecond()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|DaemonizerInterface $mock */
        $mock = $this->getMock('\Brainfit\Examples\TickExample');

        //3 seconds, 4 times (1 run per second + first time)
        $mock->expects($this->exactly(4))
            ->method('run');

        $mock->expects($this->once())
            ->method('getSchedule')
            ->will($this->returnValue(1));

        $this->caseHelper($mock, 3.5);
    }

    public function testChildHourlyIfCurrentHour()
    {
        $currentHour = date('G');
        $mock = $this->getHourlyJobMock();

        $mock->expects($this->once())
            ->method('getSchedule')
            ->will($this->returnValue(
                array($currentHour)
            ));

        $mock->expects($this->once())
            ->method('run');

        $this->caseHelper($mock);
    }

    public function testChildHourlyIfDifferentHour()
    {
        $currentHour = date('G');
        $mock = $this->getHourlyJobMock();

        $mock->expects($this->once())
            ->method('getSchedule')
            ->will($this->returnValue(
                array($currentHour != 0 ? $currentHour - 1 : $currentHour + 1)
            ));

        $mock->expects($this->never())
            ->method('run');

        $this->caseHelper($mock);
    }

    public function testChildHourlyIfMoreArguments()
    {
        $currentHour = date('G');
        $mock = $this->getHourlyJobMock();

        $mock->expects($this->once())
            ->method('getSchedule')
            ->will($this->returnValue(
                array($currentHour != 0 ? $currentHour-1 : $currentHour+1, $currentHour)
            ));

        $mock->expects($this->once())
            ->method('run');

        $this->caseHelper($mock);
    }

    public function testChildCronExpressionExpected()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|DaemonizerInterface $mock */
        $mock = $this->getMock('\Brainfit\Examples\CronJobExample');

        //non-current minute
        $minute = date('i') + 1;
        if ($minute > 59)
            $minute = 0;

        $mock->expects($this->once())
            ->method('getSchedule')
            ->will($this->returnValue(
                $minute.' * * * *'
            ));

        $mock->expects($this->never())
            ->method('run');

        $this->caseHelper($mock);
    }

    /*public function testChildCronExpressionOverdue()
    {
        $mock = $this->getMock('\Brainfit\Examples\CronJobExample');

        $mock->expects($this->once())
            ->method('getSchedule')
            ->will($this->returnValue(
                '0 1 1 1 * 1999'
            ));

        $mock->expects($this->never())
            ->method('run');

        $mock->expects($this->once())
            ->method('terminate');

        $mock->expects($this->once())
            ->method('bootstrap');

        $mock->expects($this->at(0))
            ->method('bootstrap');
        $mock->expects($this->at(1))
            ->method('run');

        $this->limitedLoop(1);
        $mock2 = $this->getMock('\Brainfit\Daemonizer\ChildController');
        $mock2->expects($this->once())
            ->method('terminate');

        //$obChild = new \Brainfit\Daemonizer\ChildController();
        $mock2->init($this->loop);
        $mock2->attach($mock);
        $mock2->run();

    }*/

    /**
     * @param $mock PHPUnit_Framework_MockObject_MockObject|DaemonizerInterface
     */
    private function caseHelper(&$mock, $timeout = 1)
    {
        $mock->expects($this->once())
            ->method('bootstrap');

        $mock->expects($this->at(0))
            ->method('bootstrap');
        $mock->expects($this->at(1))
            ->method('run');

        $this->limitedLoop($timeout);
        $obChild = new \Brainfit\Daemonizer\ChildController();
        $obChild->init($this->loop);
        $obChild->attach($mock);
        $obChild->run();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|DaemonizerInterface
     */
    private function getHourlyJobMock()
    {
        return $this->getMock('\Brainfit\Examples\HourlyJobExample');
    }

    private function limitedLoop($timeout = 5)
    {
        $this->loop->addTimer($timeout, function(){
            $this->loop->stop();
        });
    }
}