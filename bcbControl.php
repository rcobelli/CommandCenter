<?php

$conn = mysqli_connect("192.168.1.148","web","KpY-pnB-6cU-kkk","Assorted");
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">
	function bringTomorrow() {
		$.ajax({
			url : "https://cc.rybel-llc.com/bcbAction.php",
			success : function(data) {
				window.location.reload();
			}
		});
	}
</script>';

$result = $conn->query("SELECT date FROM CommandCenter WHERE date=(CURDATE() + INTERVAL 1 DAY)");
if ($result->num_rows == 0) {
	echo '<div class="item"><h1 style="font-size: 30px">Bring Chromebook</h1><img src="../serviceIcons/chrome.png" class="icon">';
	echo "<table><tr><td style='font-size: 32px; text-align: center'>" . "<a onclick='bringTomorrow()'>Add Reminder</a>";
	echo "</td></tr></table></div>";
}
