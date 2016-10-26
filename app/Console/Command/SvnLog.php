<?php
namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Model\LogEntry;

class SvnLog extends Command
{
    protected function configure()
    {
        $this->setName('svn:log')
            ->setDescription("Outputs the svn log.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'SVN Repository', 'https://src.dev.tempest-technology.com/svn/self_service/trunk'),
                    new InputOption('revision-url', 'ru', InputOption::VALUE_OPTIONAL, 'Revision url'),
                    new InputOption('username', 'u', InputOption::VALUE_OPTIONAL, 'SVN Username'),
                    new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'SVN Password'),
                    new InputOption('start', 's', InputOption::VALUE_REQUIRED, 'Start revision'),
                    new InputOption('end', 'e', InputOption::VALUE_OPTIONAL, 'End revision', 'HEAD'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = sprintf("echo 'p' | svn log --username '%s' --password '%s' -r %s:%s --xml %s",
            $input->getOption('username'),
            $input->getOption('password'),
            $input->getOption('start'),
            $input->getOption('end'),
            $input->getOption('repository')
        );

        $process = new Process($cmd);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful())
        {
            throw new ProcessFailedException($process);
        }

        $xml = simplexml_load_string(utf8_decode($process->getOutput()));

        if ($xml->logentry)
        {
            foreach ($xml->logentry as $log)
            {
                $params = (array)$log;
                $params['revisionUrl'] = $input->getOption('revision-url');

                $entry = new LogEntry($params);
                $output->write($entry->toHtml());
            }
        }
        else
        {
            $output->writeln('<p>No updates.</p>');
        }
    }
}
