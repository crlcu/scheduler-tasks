<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
*/

use Symfony\Component\Console\Application;

$application = new Application('Tasks Scheduler console', '0.9');

$commands = require_once __DIR__.'/../commands.php';

foreach ($commands as $command) {
    $application->add(new $command());
}

/*
|--------------------------------------------------------------------------
| Set the timezone
|--------------------------------------------------------------------------
*/
date_default_timezone_set(getenv('APP_TIMEZONE'));

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
*/

return $application;
