<?php
namespace App\Console\Command\Bitcoin\BTCxChange;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Console\Traits\Check;

class Sell extends Command
{
    use Check;

    private $http;

    protected function configure()
    {
        $this->setName('bitcoin:btcxchange:sell')
            ->setDescription("Returns the bitcoin price in ron.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('check', null, InputOption::VALUE_NONE, 'Check value'),
                    new InputOption('method', null, InputOption::VALUE_OPTIONAL, 'Method', 'eq'),
                    new InputOption('value', null, InputOption::VALUE_OPTIONAL, 'Value'),
                    new InputOption('regex', null, InputOption::VALUE_OPTIONAL, 'Regex'),
                    new InputOption('min', null, InputOption::VALUE_OPTIONAL, 'Min', 0),
                    new InputOption('max', null, InputOption::VALUE_OPTIONAL, 'Max', 0),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $process = new Process("curl -s https://www.btcxchange.com/  | grep liveLow | grep -ioE '([0-9]*\.[0-9]+|[0-9]+)'");
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $last = $process->getOutput();

        $output->writeln(str_replace("\n", '', $last));

        if ($input->getOption('check'))
        {
            if (self::check($input, $output, $last))
            {
                $output->writeln("Checked: YES");
            } else {
                $output->writeln("Checked: NO");
            }
        }
    }
}
