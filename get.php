<?php
require 'vendor/autoload.php';

require 'exceptions.php';
require 'checks.php';

$options = new App\Util\Options($argc, $argv);

$client = new GuzzleHttp\Client();

//echo sprintf("Going to fetch content from: %s\n", $options->get('host'));

$response = $client->request('GET', $options->get('host'), [
    'auth' => ['wholesale-robot', 'wholesale-robot-password']
]);

$content = $response->getBody()->__toString();

if ( doChecks($content, $options) ) {
    echo "Test passed.\n";
} else {
    throw new TestFailedException(sprintf("Headers: %s\n\nContent: %s", json_encode($response->getHeaders()), $content));
}
