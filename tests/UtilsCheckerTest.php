<?php

class UtilsCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigChecker()
    {
        $this->assertEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem('* * * * *'));
        $this->assertEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem('59 * * * *'));
        $this->assertEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem(array(0, 1, 2, 3, 4,
                                                                                             5, 10, 12, 15, 19, 23)));
        $this->assertEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem(1));
        $this->assertEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem());


        $this->assertNotEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem('* * * * * 1999'));
        $this->assertNotEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem('60 * * * *'));
        $this->assertNotEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem('61 * * * *'));
        $this->assertNotEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem(array(1,25)));
        $this->assertNotEquals(false, \Brainfit\Daemonizer\DaemonUtils::checkScheduleItem(array(-1,25)));
    }
}