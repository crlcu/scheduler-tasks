<?php

require 'vendor/autoload.php';

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

    if (isset($options['should-contain']) && $check = $options['should-contain']) {
        echo sprintf("Checking if response contains *%s*.\n", $check);
        return strpos($content, $check) != false ? 1 : 0;
    } elseif (isset($options['should-not-contain']) && $check = $options['should-not-contain']) {
        echo sprintf("Checking if response does not contain *%s*.\n", $check);
        return strpos($content, $check) == false ? 1 : 0;
    } elseif (isset($options['should-be-equal-to']) && $check = $options['should-be-equal-to']) {
        echo sprintf("Checking if response is equal to *%s*.\n", $check);
        return $content == $check ? 1 : 0;
    }

    return (0);
}

$options = [];

for ($i = 1; $i < $argc; $i++) {
    list($name, $value) = explode('=', $argv[$i]);

    $options[ str_replace('--', '', $name) ] = $value;
}

$ok = handle($options);

if ($ok) {
    echo "Test passed.";
} else {
    throw new Exception('Test failed.');
}
