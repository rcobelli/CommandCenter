<?php

include_once("../../init.php");

$config['type'] = Rybel\backbone\LogStream::cron;

$helper = new SystemHelper($config);

$systems = $helper->getAllSystems();
foreach ($system as $row) {
    if (is_null($row['canaryURL'])) {
        continue;
    }

    $id = $row['id'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $row['canaryURL']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);

    if (curl_error($ch)) {
        $status = 2;
    } else if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
        $status = 1;
    } else {
        $status = 0;
    }

    $helper->recordMetric($id, 'Canary', $status);
}
