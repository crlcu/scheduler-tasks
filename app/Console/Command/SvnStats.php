<?php
namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Models\LogEntry;

class SvnStats extends Command
{
    protected function configure()
    {
        $this->setName('svn:stats')
            ->setDescription("Outputs svn stats.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'SVN Repository'),
                    new InputOption('username', 'u', InputOption::VALUE_OPTIONAL, 'SVN Username'),
                    new InputOption('password', 'p', InputOption::VALUE_OPTIONAL, 'SVN Password'),
                    new InputOption('start', 's', InputOption::VALUE_REQUIRED, 'Start revision'),
                    new InputOption('end', 'e', InputOption::VALUE_OPTIONAL, 'End revision', 'HEAD'),
                    new InputOption('html', null, InputOption::VALUE_NONE, 'Output as html.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = sprintf("echo 'p' | svn diff --username '%s' --password '%s' -r %s:%s %s | diffstat | awk 'END{print}'",
            $input->getOption('username'),
            $input->getOption('password'),
            $input->getOption('start'),
            $input->getOption('end'),
            $input->getOption('repository')
        );

        $process = new Process($cmd);
        
        $process->setTimeout(15 * 60);
        $process->setIdleTimeout(15 * 60);

        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful())
        {
            throw new ProcessFailedException($process);
        }

        $output->writeln(rtrim($process->getOutput()));
    }
}
