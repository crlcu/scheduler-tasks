<?php
namespace App\Console\Command\Bitcoin;

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

class Sell extends Command
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
        $this->setName('bitcoin:sell')
            ->setDescription("Returns the bitcoin price in euro.")
            ->setDefinition(
                new InputDefinition([
                    new InputOption('amount', null, InputOption::VALUE_OPTIONAL, 'Amount', 1),
                ])
            );

        static::startChromeDriver();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $browser = new Browser($this->driver());
        
        $browser->visit('https://www.btcxchange.ro/login')
                ->type('username', getenv('BTCXCHANGE_USERNAME'))
                ->type('password', getenv('BTCXCHANGE_PASSWORD'))
                ->press('[type="submit"]')
                ->waitUntilMissing('[name="username"]', 10)
                ->assertPathIs('/account')
                ->press('[href="/order"]')
                ->assertSee('Cumpara')
                ->type('sellInstantAmount', $input->getOption('amount'))
                ->press('#sellInstantButton')
                ->waitFor('.alert.intervention', 10)
                ->assertSee('Fonduri insuficiente.')
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
