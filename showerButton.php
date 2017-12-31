<?php

echo '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">
	function callAPI() {
		$.ajax({
			url : "https://cc.rybel-llc.com/itunesRedirect.php"
		});
	}
</script>';


echo '<div class="item"><h1>Start Music</h1><img src="../serviceIcons/music.png" class="icon">';
echo "<table><tr><td style='font-size: 32px; text-align: center'>" . "<a onclick='callAPI()'>Play</a>";
echo "</td></tr></table></div>";
