<?php
namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Carbon\Carbon;
use Exception;
use SimpleXMLElement;

use App\Models\LogEntry;

class SvnLog extends Command
{
    protected function configure()
    {
        $this->setName('svn:log')
            ->setDescription("Outputs the svn log.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('repository', 'r', InputOption::VALUE_OPTIONAL, 'SVN Repository'),
                    new InputOption('revision-url', 'ru', InputOption::VALUE_OPTIONAL, 'Revision url'),
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
        $startDate = $this->isDate($input->getOption('start'));
        $endDate = $this->isDate($input->getOption('end'));

        $start = $startDate ?
            sprintf("'{%s}'", (new Carbon($startDate))
                ->subMinutes(getenv('START_TIME_MARGIN') ? : 0)
                ->toDateTimeString()
            ) : 
            $input->getOption('start');

        $end = $endDate ?
            sprintf("'{%s}'", (new Carbon($endDate))
                    ->addMinutes(getenv('END_TIME_MARGIN') ? : 0)
                    ->toDateTimeString()
            ) : 
            $input->getOption('end');

        $cmd = sprintf("echo 'p' | svn log --username '%s' --password '%s' -r %s:%s --xml %s",
            $input->getOption('username'),
            $input->getOption('password'),
            $start,
            $end,
            $input->getOption('repository')
        );

        $process = new Process($cmd);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful())
        {
            throw new ProcessFailedException($process);
        }

        try {
            libxml_use_internal_errors(true);

            $xml = new SimpleXMLElement($process->getOutput());

            foreach ($xml->logentry as $log)
            {
                $params = (array)$log;
                $params['revisionUrl'] = $input->getOption('revision-url');

                $entry = new LogEntry($params);

                // When you specify a date,
                // Subversion resolves that date to the most recent revision of the repository as of that date
                // That's why we have to check the date interval again
                if (($startDate && $entry->date() < $startDate) || ($endDate && $entry->date() > $endDate))
                {
                    continue;
                }

                // Output as required (html/string)
                if ($input->getOption('html'))
                {
                    $output->writeln($entry->toHtml());
                }
                else
                {
                    $output->writeln($entry->toString());
                }
            }
        } catch (Exception $e) {
            $output->writeln(sprintf('Could not fetch log for %s', $input->getOption('repository')));
        }
    }

    private function isDate($string)
    {
        $date = false;

        try {
            $date = (new Carbon(str_replace(["'{", '{', "}'", '}'], '', $string)))->toDateTimeString();
        }
        catch (Exception $e)
        {
            # do nothing
        }

        return $date;
    }
}
