<?php

function push($user, $title, $message)
{
    // Put your device token here (without spaces):
    $device_token = $user;
    //echo $key;
    $kid      = "ZR9S83VL76";
    $teamId   = "AL6H9GEC6N";
    $app_bundle_id = "com.rybel-llc.Morning-Briefing";
    $base_url = "https://api.development.push.apple.com";

    $header = ["alg" => "ES256", "kid" => $kid];
    $header = base64_encode(json_encode($header));

    $claim = ["iss" => $teamId, "iat" => time()];
    $claim = base64_encode(json_encode($claim));

    $token = $header.".".$claim;
    // key in same folder as the script
    $filename = "APNS.p8";
    $pkey     = openssl_pkey_get_private("file://{$filename}");
    $signature;
    openssl_sign($token, $signature, $pkey, 'sha256');
    $sign = base64_encode($signature);

    $jws = $token.".".$sign;

    $message = '{"aps":{"alert" : {"title" : "' . $title . '", "body" : "' . $message . '"},"sound":"default"}}';

    sendHTTP2Push($base_url, $app_bundle_id, $message, $device_token, $jws);
}

function sendHTTP2Push($base_url, $app_bundle_id, $message, $device_token, $jws)
{
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $url = "{$base_url}/3/device/{$device_token}";
    // headers
    $headers = array(
        "apns-topic: {$app_bundle_id}",
        'Authorization: bearer ' . $jws
    );

    echo exec('LD_LIBRARY_PATH=/usr/local/lib curl -v  -d \'' . $message . '\' -H "apns-topic: ' . $app_bundle_id . '" -H "authorization: bearer ' . $jws . '" --http2 ' . $url);
}

push("03DD18E770BE0B75968EB3661DE12BF6E561563FC227BB597CC076E3E865046B", $_GET['serviceName'], "Has finished being deployed");
