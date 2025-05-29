<?php
$client = new \GuzzleHttp\Client();
$response = $client->get('https://www.google.com');
echo $response->getStatusCode();
