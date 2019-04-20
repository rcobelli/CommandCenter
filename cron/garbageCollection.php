<?php

include_once("init.php");

// Clean up metricCheck older than a week
$sql = "DELETE FROM `metric-log` WHERE timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$conn->query($sql);

// Clean up cron logs older than 120 days
$sql = "DELETE FROM `cron-log` WHERE timestamp < DATE_SUB(NOW(), INTERVAL 120 DAY)";
$conn->query($sql);
