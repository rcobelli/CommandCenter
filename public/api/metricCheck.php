<?php

include_once("../../init.php");

$config['type'] = Rybel\backbone\LogStream::cron;

$systemHelper = new SystemHelper($config);

$systems = $systemHelper->getAllSystems();
foreach ($systems as $row) {
    $id = $row['id'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $row['url'] . ":2812/_status?format=xml");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $row['username'] . ":" . $row['password']);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);

    if (curl_error($ch)) {
        $metrics = $systemHelper->getMetricsForSystem($id);
        foreach ($metrics as $metric) {
            $systemHelper->recordMetric($id, $metric['name'], 2);
        }
    } else {
        $xml = simplexml_load_string($output);
        $json = json_encode($xml);
        $raw = json_decode($json, true);

        foreach ($raw['service'] as $service) {
            $name = $service['name'];

            if ($service['monitor'] != "1" || $service['status'] != "0") {
                $status = 0;
            } else {
                $status = 1;
            }

            $systemHelper->recordMetric($id, $name, $status);
        }
    }
}
