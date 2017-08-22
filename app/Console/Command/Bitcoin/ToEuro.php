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
            ->setDescription("Returns the bitcoin price in euro.");
        
        $this->http = new Http();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $response = $this->http->request('GET', 'https://www.bitstamp.net/api/v2/ticker/btceur');

            $decoded = json_decode($response->getBody()->getContents(), true);

            $last = $decoded['last'];

            // usleep: this will help having a nice graph
            usleep($last * 1000);

            $output->writeln($last);
        } catch (RequestException $e) {
            throw new Exception("Can't fetch bitcoin price.");
        }
    }
}
