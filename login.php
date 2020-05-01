<?php

if (isset($_GET['error'])) {
    exit($_GET);
}

try {
    $client = new Google_Client();
    $client->setAuthConfig('client_secret.json');
    $client->setAccessType("offline");        // offline access
    $client->setIncludeGrantedScopes(true);
    $client->setRedirectUri(getURL() . 'index.php');
    $client->authenticate($_GET['code']);
    $access_token = $client->getAccessToken();
} catch (Google_Exception $e) {
    exit($e);
}

$_SESSION['access_token'] = $access_token['access_token'];


$plus = new Google_Service_Oauth2($client);
$person = $plus->userinfo->get();

$_SESSION['name'] = $person['name'];
$_SESSION['email'] = $person['email'];
$_SESSION['id'] = md5($person['email']);


//Where the magic happends
if (isset($_SESSION['access_token'])) {

    //Set the new access token after authentication
    $client->setAccessToken($_SESSION['access_token']);

    //json decode the session token and save it in a variable as object
    $sessionToken = json_decode($_SESSION['access_token']);

    setcookie('commandcenter', json_encode(array('name' => $person['name'], 'email' => $person['email'], 'id' => md5($person['email']))), time() + (86400 * 30), "/"); // 86400 = 1 day
}

header("Location: dashboard.php");
die();
