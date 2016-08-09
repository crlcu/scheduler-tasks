<?php
require 'vendor/autoload.php';

require 'exceptions.php';
require 'checks.php';

$options = [];

for ($i = 1; $i < $argc; $i++) {
    list($name, $value) = explode('=', $argv[$i]);

    $options[ str_replace('--', '', $name) ] = $value;
}

$client = new GuzzleHttp\Client();

//echo sprintf("Going to fetch content from: %s\n", $options['host']);

$response = $client->request('GET', $options['host'], [
    'auth' => ['wholesale-robot', 'wholesale-robot-password']
]);

$content = $response->getBody()->__toString();

if ( doChecks($content, $options) ) {
    echo "Test passed.\n";
} else {
    throw new TestFailedException(sprintf("Headers: %s\n\nContent: %s", json_encode($response->getHeaders()), $content));
}
