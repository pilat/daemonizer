<?php
namespace Brainfit\Daemonizer;

use Symfony\Component\Console\Input\InputInterface;

class DaemonUtils
{
    public static function checkPidFile()
    {
        $sPidFilename = self::getPidFilename();
        if (!file_exists($sPidFilename))
            return false;

        return explode(',', file_get_contents($sPidFilename));
    }

    public static function getPidFilename()
    {
        return getcwd().'/daemonizer.pid';
    }

    public static function configCheck(InputInterface $input)
    {
        if (!defined('DAEMON_FILE') || !DAEMON_FILE || !file_exists(DAEMON_FILE))
            throw new \RuntimeException('Not defined DAEMON_FILE');

        if (!defined('DAEMON_CWD') || !DAEMON_CWD)
            throw new \RuntimeException('Not defined DAEMON_CWD');
    }
}