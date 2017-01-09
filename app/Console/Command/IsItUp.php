<?php
namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Exception;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\RequestException;

class IsItUp extends Command
{
    private $http;

    protected function configure()
    {
        $this->setName('isitup')
            ->setDescription("Checks if url is up.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('url', null, InputOption::VALUE_REQUIRED, 'URL'),
                    new InputOption('debug', null, InputOption::VALUE_NONE, 'Enable debug output'),
                ])
            );
        
        $this->http = new Http();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $response = $this->http->request('GET', $input->getOption('url'), [
                'debug' => $input->getOption('debug')
            ]);

            if ($input->getOption('debug')) {
                $output->writeln($response->getBody()->getContents());
            } else {
                $output->writeln('Status: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            throw new Exception('Is it down');
        }
    }
}
