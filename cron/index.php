<?php

include_once("init.php");

if (empty($_GET['id'])) {
    http_response_code(400);
    die();
}

$id = steralizeString($_GET['id']);

$sql = "INSERT INTO `cron-log` (cronID, timestamp) VALUES ($id, NOW())";
if ($conn->query($sql) === false) {
    http_response_code(500);
    exit($conn->error);
} else {
    http_response_code(200);
}
