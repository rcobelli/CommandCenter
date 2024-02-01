<?php

include_once("../init.php");

if (empty($_GET['id'])) {
    http_response_code(400);
    die();
}


$config['type'] = Rybel\backbone\LogStream::api;

$helper = new CronHelper($config);

if ($helper->recordHeartbeat($_GET['id'])) {
    http_response_code(500);
} else {
    http_response_code(200);
}
