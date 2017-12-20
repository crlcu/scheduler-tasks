<?php
namespace App\Console\Command\Bitcoin\BTCxChange;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Laravel\Dusk\Chrome\SupportsChrome;
use Laravel\Dusk\Browser;

class Buy extends Command
{
    use SupportsChrome;

    /**
     * The callbacks that should be run on class tear down.
     *
     * @var array
     */
    protected static $afterClassCallbacks = [];
    
    protected function configure()
    {
        $this->setName('bitcoin:btcxchange:buy')
            ->setDescription("Returns the bitcoin price in euro.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('amount', null, InputOption::VALUE_REQUIRED, 'Amount', 1),
                    new InputOption('username', null, InputOption::VALUE_REQUIRED, 'Username'),
                    new InputOption('password', null, InputOption::VALUE_REQUIRED, 'Password'),
                ])
            );

        static::startChromeDriver();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $browser = new Browser($this->driver());
        
        $browser->visit('https://www.btcxchange.com/login')
            ->type('username', $input->getOption('username'))
            ->type('password', $input->getOption('password'))
            ->press('.btn--size-l')
            ->waitUntilMissing('[name="username"]', 10)
            ->press('[href="/order"]')
            ->type('buyInstantValue', $input->getOption('amount'))
            // ->press('.js-buy-btc-btn')
            ->waitFor('.alert.intervention', 10)
            ->quit();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless'
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Register an "after class" tear down callback.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function afterClass(\Closure $callback)
    {
        static::$afterClassCallbacks[] = $callback;
    }
}
