<?php

namespace Brainfit\Daemonizer\Command;

use Brainfit\Daemonizer\DaemonUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Stop extends Command
{
    protected function configure()
    {
        $this
            ->setName('stop')
            ->setDescription('Stop tasks daemon');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            list($iPid, $idKey) = DaemonUtils::checkPidFile();

            if($iPid == 0)
                throw new \Exception('Daemon not running');

            shell_exec('kill -15 ' . $iPid);
            sleep(5);
            $aProcess = explode(PHP_EOL,
                shell_exec("ps -ef | grep " . escapeshellarg(DAEMON_FILE . " internal:master --id={$idKey}")
                    . " | grep -v grep | awk '{print $2}'"));
            foreach ($aProcess as $iProcessId)
                if($iProcessId = (int)$iProcessId)
                    shell_exec('kill -9 ' . $iProcessId);

            sleep(5);

            shell_exec('kill -9 ' . $iPid);

            @unlink(DaemonUtils::getPidFilename());

            $output->writeln("<info>Daemon stopped</info>");
        }
        catch (\Exception $e)
        {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}