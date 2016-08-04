<?php

require 'vendor/autoload.php';
require 'checks.php';

function handle($options) {
    $client = new GuzzleHttp\Client();

    //echo sprintf("Going to fetch content from: %s\n", $options['host']);

    $response = $client->request('GET', $options['host'], [
        'auth' => ['wholesale-robot', 'wholesale-robot-password']
    ]);

    $content = '';

    try {
        $content = $response->getBody()->__toString();
    } catch (Exception $e) {}

    return doChecks($content, $options);
}

$options = [];

for ($i = 1; $i < $argc; $i++) {
    list($name, $value) = explode('=', $argv[$i]);

    $options[ str_replace('--', '', $name) ] = $value;
}

$ok = handle($options);

if ($ok) {
    echo "Test passed.\n";
} else {
    throw new Exception('Test failed.');
}
