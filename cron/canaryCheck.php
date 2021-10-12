<?php

include_once("init.php");

$sql = "SELECT * FROM systems WHERE canaryURL IS NOT NULL";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $row['canaryURL']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (curl_error($ch)) {
            $sql = "INSERT INTO `metric-log` (systemID, metricID, timestamp, status) VALUES ($id, 'Canary', NOW(), 2)";
            if ($conn->query($sql) === false) {
                echo $sql;
                exit('Load failure. ' . $conn->error);
            }
        } else {
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                $status = 1;
            } else {
                $status = 0;
            }

            $sql = "INSERT INTO `metric-log` (systemID, metricID, timestamp, status) VALUES ($id, 'Canary', NOW(), $status)";
            if ($conn->query($sql) === false) {
                echo $sql;
                exit('Load failure. ' . $conn->error);
            }
        }
    }
}
