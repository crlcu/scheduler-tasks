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
                    new InputOption('from', null, InputOption::VALUE_OPTIONAL, 'News newer than.', 'yesterday'),
                    new InputOption('html', null, InputOption::VALUE_NONE, 'Output as html.'),
                ])
            );
        
        $this->http = new Http();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $urls = split(', ', $input->getOption('url'));

        foreach ($urls as $url)
        {
            $this->crawlUrl($input->getOption('url'), $input, $output);
        }
    }

    protected function crawlUrl($url, $input, $output)
    {
        try {
            $response = $this->http->request('GET', $url);

            $crawler = new SimpleXMLElement($response->getBody()->getContents());
            $array = $this->xml2array($crawler);

            foreach ($array['channel']['item'] as $item) {
                if (!isset($item['title']))
                    continue;

                $news = new News($item);

                if ($news->isAboutCarCrashes() && $news->isNewerThan($input->getOption('from')))
                {
                    if ($input->getOption('html'))
                    {
                        $output->writeln($news->__toHtml());
                    }
                    else
                    {
                        $output->writeln($news->__toString());
                    }
                }
            }
        } catch (RequestException $e) {
            throw new Exception(sprintf('Could not access remote site. (%s)', $url));
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
