<?php

include_once("init.php");

$sql = "SELECT * FROM systems WHERE expDate IS NOT NULL";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];

        $orignal_parse = parse_url($row['url'], PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        $epoch = $certinfo['validTo_time_t'];

        echo $row['name'] . " " . $epoch . PHP_EOL;

        /** @noinspection SyntaxError */
        $sql = "UPDATE `systems` SET expDate = '" . date("Y-m-d H:i:s", $epoch) . "' WHERE id = $id";
        if ($conn->query($sql) === false) {
            exit('Load failure. ' . $conn->error);
        }
    }
}


