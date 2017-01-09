<?php

return $commands = [
    App\Console\Command\Crawl::class,
	App\Console\Command\IsItUp::class,
    App\Console\Command\Migrations\Apply::class,
    App\Console\Command\Migrations\Initialize::class,
    App\Console\Command\SvnLog::class,
    App\Console\Command\SvnStats::class,
    App\Console\Command\ReleaseNotes\Daisy::class,
];
