<?php

include_once("../../init.php");

$config['type'] = Rybel\backbone\LogStream::cron;

$helper = new SystemHelper($config);

$systems = $helper->getAllSystems();
foreach ($systems as $row) {
    if (is_null($row['expDate'])) {
        continue;
    }

    $orignal_parse = parse_url($row['url'], PHP_URL_HOST);
    $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
    $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
    $cert = stream_context_get_params($read);
    $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

    $epoch = $certinfo['validTo_time_t'];

    $helper->updateSslExpirationDate($row['id'], date("Y-m-d H:i:s", $epoch));
}
