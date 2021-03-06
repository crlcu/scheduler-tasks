<?php

return $commands = [
    App\Console\Command\Bitcoin\BTCxChange\Buy::class,
    App\Console\Command\Bitcoin\BTCxChange\BuyRate::class,
    App\Console\Command\Bitcoin\BTCxChange\Sell::class,
    App\Console\Command\Bitcoin\BTCxChange\SellRate::class,
    App\Console\Command\Bitcoin\ToEuro::class,
    App\Console\Command\Bitcoin\ToRon::class,
    App\Console\Command\Migrations\Apply::class,
    App\Console\Command\Migrations\Initialize::class,
    App\Console\Command\ReleaseNotes\Daisy::class,
    App\Console\Command\Crawl::class,
	App\Console\Command\IsItUp::class,
    App\Console\Command\SvnLog::class,
    App\Console\Command\SvnStats::class,
];
