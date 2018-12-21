<?php

include_once("init.php");

$sql = "SELECT * FROM systems";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
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
            $sql = "SELECT name FROM metrics WHERE systemID = $id";
            $result2 = $conn->query($sql);
            if ($result->num_rows > 0) {
                $sql = "INSERT INTO `metric-log` (systemID, metricID, timestamp, status) VALUES";
                while ($row = $result2->fetch_assoc()) {
                    $sql .= " ($id, '" . $row['name'] . "', NOW(), 0),";
                }
                $sql = rtrim($sql, ',');
                if ($conn->query($sql) === false) {
                    exit($conn->error);
                }
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

                $sql = "INSERT INTO `metric-log` (systemID, metricID, timestamp, status) VALUES ($id, '$name', NOW(), $status)";
                if ($conn->query($sql) === false) {
                    exit($conn->error);
                }
            }
        }
    }
}
