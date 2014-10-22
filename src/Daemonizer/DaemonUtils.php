<?php
namespace Brainfit\Daemonizer;

use Cron\CronExpression;
use Symfony\Component\Console\Input\InputInterface;

class DaemonUtils
{
    public static function checkPidFile()
    {
        $sPidFilename = self::getPidFilename();
        if(!file_exists($sPidFilename))
            return false;

        return explode(',', file_get_contents($sPidFilename));
    }

    public static function getPidFilename()
    {
        return getcwd() . '/daemonizer.pid';
    }

    public static function configCheck(InputInterface $input)
    {
        if(!defined('DAEMON_FILE') || !DAEMON_FILE || !file_exists(DAEMON_FILE))
            throw new \RuntimeException('Not defined DAEMON_FILE');

        if(!defined('DAEMON_CWD') || !DAEMON_CWD)
            throw new \RuntimeException('Not defined DAEMON_CWD');
    }

    public static function getDaemonsFromConfig()
    {
        $directories = array(getcwd(), getcwd() . DIRECTORY_SEPARATOR . 'config');

        $configFile = null;
        foreach ($directories as $directory)
        {
            $configFile = $directory . DIRECTORY_SEPARATOR . 'cli-daemonizer.php';

            if(file_exists($configFile))
                break;
        }

        if(!file_exists($configFile))
            throw new \Exception('Configuration file not exist. Create [cli-daemonizer.php] file');

        if(!is_readable($configFile))
            throw new \Exception('Configuration file [' . $configFile . '] does not have read permission.');

        $daemons = require $configFile;

        if(!$daemons || count($daemons) < 1)
            throw new \Exception('Invalid [cli-daemonizer.php] file: file must return array of classes ' .
                'implementing DaemonizerInterface');

        return $daemons;
    }

    public static function checkScheduleItem($schedule = null, $moduleName = '')
    {
        if(is_numeric($schedule))
        {

        }
        else if(is_array($schedule))
        {
            foreach ($schedule as $item)
                if($item < 0 || $item > 23)
                    return 'Invalid schedule time in configuration ' . $moduleName;
        }
        else if(is_string($schedule))
        {
            try
            {
                $expression = CronExpression::factory($schedule);

                $expression->getNextRunDate();
            }
            catch (\Exception $e)
            {
                return 'Invalid cron expression in configuration ' . $moduleName;
            }
        }

        return false;
    }
}