<?php

use Brainfit\Examples\CronJobExample;
use Brainfit\Examples\HourlyJobExample;
use Brainfit\Examples\TickExample;
use Brainfit\Examples\TickExample2;

return array(
    new CronJobExample(),
    new TickExample(),
    new TickExample2(),
    new HourlyJobExample()
);