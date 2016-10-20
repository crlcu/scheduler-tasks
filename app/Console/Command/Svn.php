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
use Symfony\Component\DomCrawler\Crawler;

use App\Model\LogEntry;

class Svn extends Command
{
    protected function configure()
    {
        $this->setName('svn:do')
            ->setDescription("Outputs Hello World")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'SVN Repository', 'https://src.dev.tempest-technology.com/svn/self_service/trunk'),
                    new InputOption('username', 'u', InputOption::VALUE_OPTIONAL, 'SVN Username'),
                    new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'SVN Password'),
                    new InputOption('start', 's', InputOption::VALUE_REQUIRED, 'Start revision'),
                    new InputOption('end', 'e', InputOption::VALUE_OPTIONAL, 'End revision', 'HEAD'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = sprintf("svn log --username '%s' --password '%s' -r %s:%s --xml %s",
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

        foreach ($xml->logentry as $log)
        {
            $entry = new LogEntry((array)$log);
            $output->writeln($entry->revision() . ' -> ' . $entry->fogbugz() . ' -> ' . $entry->toString());
        }
    }
}
