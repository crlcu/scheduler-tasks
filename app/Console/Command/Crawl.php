<?php
namespace App\Console\Command;

use Illuminate\Support\Collection;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Exception;
use GuzzleHttp\Client as Http;
use GuzzleHttp\Exception\RequestException;
use SimpleXMLElement;

use App\Models\News;

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

        $collection = new Collection();

        foreach ($urls as $url)
        {
            $local = $this->crawlUrl($url, $input, $output);

            if ($local->count())
            {
                $collection = $collection->merge($local);
            }
        }

        $collection = $collection->sortBy(function($item) {
            return $item->date;
        });

        if ($input->getOption('html'))
        {
            foreach ($collection as $news)
            {
                $output->writeln($news->__toHtml());
            }
        }
        else
        {
            foreach ($collection as $news)
            {
                $output->writeln($news->__toString());
            }
        }
    }

    protected function crawlUrl($url, $input, $output)
    {
        $collection = new Collection();

        try {
            libxml_use_internal_errors(true);

            $response = $this->http->request('GET', $url);

            $crawler = new SimpleXMLElement($response->getBody()->getContents());
            $array = $this->xml2array($crawler);

            foreach ($array['channel']['item'] as $item) {
                if (!isset($item['title']))
                    continue;

                $news = new News($item);

                if ($news->isAboutCarCrashes() && $news->isNewerThan($input->getOption('from')))
                {
                    $collection->push($news);
                }
            }

            libxml_clear_errors();
        } catch (Exception $e) {
            // throw new Exception(sprintf('Could not access remote site. (%s)', $url));
            $output->writeln(sprintf('Nu am putut prelua stirile pentru %s', $url));
        }

        return $collection;
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
