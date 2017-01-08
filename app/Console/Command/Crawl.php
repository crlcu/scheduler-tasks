<?php
namespace App\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Exception;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\RequestException;
use SimpleXMLElement;

use App\Model\News;

class Crawl extends Command
{
    private $http;

    protected function configure()
    {
        $this->setName('crawl:rss')
            ->setDescription("Crawl url.")
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
            $response = $this->http->request('GET', $input->getOption('url'));

            // $xmlString = require_once('xml.php');
            // $crawler = new SimpleXMLElement($xmlString);
            $crawler = new SimpleXMLElement($response->getBody()->getContents());
            $array = $this->xml2array($crawler);

            foreach ($array['channel']['item'] as $item) {
                if (!isset($item['title']))
                    continue;

                $news = new News($item);

                if ($news->condition()) {
                    echo $news->get('title') . "\n";
                    //dump($news->howMany());
                }
            }
        } catch (RequestException $e) {
            throw new Exception('Could not access remote site.');
        }
    }

    protected function xml2array($parent) {
        $array = array();

        foreach ($parent as $name => $element) {
            ($node = & $array[$name]) && (1 === count($node) ? $node = array($node) : 1) && $node = & $node[];

            $node = $element->count() ? $this->xml2array($element) : trim($element);
        }

        return $array;
    }
}
