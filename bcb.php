<?php

$conn = mysqli_connect("192.168.1.148","web","KpY-pnB-6cU-kkk","Assorted");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$result = $conn->query("SELECT date FROM CommandCenter WHERE date=CURDATE()");
if ($result->num_rows > 0) {
	echo '<div class="item"><h1>Bring Chromebook</h1><img src="../serviceIcons/chrome.png" class="icon">';
	echo "</div>";
}
