<?php

$conn = mysqli_connect("192.168.1.148", "web", "KpY-pnB-6cU-kkk", "Assorted");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$conn->query("INSERT INTO CommandCenter SET date=(CURDATE() + INTERVAL 1 DAY)");
