<?php

namespace Brainfit\Daemonizer\Command;

use Brainfit\Daemonizer\ChildController;
use Brainfit\Daemonizer\DaemonUtils;
use Brainfit\Daemonizer\DaemonizerInterface;
use Brainfit\Daemonizer\MasterController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Master extends Command
{
    protected function configure()
    {
        $this
            ->setName('internal:master')
            ->setDescription('Execute master-process. Daemon run same self with this command')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED,
                'Internal process id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try
        {
            $id = $input->getOption('id');

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

            file_put_contents(DaemonUtils::getPidFilename(), getmypid() . ',' . $id);

            $loop = \React\EventLoop\Factory::create();

            $master = new MasterController($loop, new ChildController);
            foreach ($daemons as $daemon)
            {
                if(!$daemon instanceof DaemonizerInterface)
                    throw new \Exception('Invalid [cli-daemonizer.php] file: file contain not-implementer ' .
                        'DaemonizerInterface class');
                $master->attach($daemon);
            }
            $master->start();

            $loop->run();
        }
        catch (\Exception $e)
        {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}