<?php

namespace Brainfit\Daemonizer\Command;

use Brainfit\Daemonizer\DaemonUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Start extends Command
{
    protected function configure()
    {
        $this
            ->setName('start')
            ->setDescription('Start daemons')
            ->addOption(
                'allow-root',
                null,
                InputOption::VALUE_NONE,
                'Allow execute from root user'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            if(!$input->getOption('allow-root'))
            {
                $aProcessUser = posix_getpwuid(posix_geteuid());
                if($aProcessUser['name'] == 'root')
                    throw new \InvalidArgumentException('You can however run a command with '
                        . 'sudo using --allow-root option');
            }

            if(DaemonUtils::checkPidFile() !== false)
                throw new \Exception('Daemon already running');

            $id = mt_rand(1, 100000);
            shell_exec(DAEMON_FILE . " internal:master --id={$id} > /dev/null 2>&1 &");

            $output->writeln("<info>Daemon started</info>");
        }
        catch (\Exception $e)
        {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}