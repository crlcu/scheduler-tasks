<?php
namespace App\Console\Command\Bitcoin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Exception;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\RequestException;

class ToEuro extends Command
{
    private $http;

    protected function configure()
    {
        $this->setName('bitcoin:euro')
            ->setDescription("Returns the bitcoin price in euro.")
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
        
        $this->http = new Http();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $response = $this->http->request('GET', 'https://www.bitstamp.net/api/v2/ticker/btceur');

            $decoded = json_decode($response->getBody()->getContents(), true);

            $last = $decoded['last'];

            $output->writeln($last);
        } catch (RequestException $e) {
            throw new Exception("Can't fetch bitcoin price.");
        }

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
