<?php

// Client ID: 280833156075-naqcuf9rut3ir7avmqvtv5050h4ajkfv.apps.googleusercontent.com
// Client Secret: yvRgyoGjZAT9wRTamPZzHuuG

include_once("init.php");

if (isset($_GET['error'])) {
    exit($_GET);
}

$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->authenticate($_GET['code']);
$access_token = $client->getAccessToken();
$_SESSION['access_token'] = $access_token;

$plus = new Google_Service_Plus($client);
$person = $plus->people->get('me');

$_SESSION['name'] = $person['displayName'];
$_SESSION['email'] = $person['emails'][0]['value'];
$_SESSION['id'] = $person['id'];

header("Location: dashboard.php");
