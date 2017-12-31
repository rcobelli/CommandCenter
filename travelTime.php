<?php

function getDuration($side) {
	$left = "https://maps.googleapis.com/maps/api/directions/json?units=imperial&origin=34.052724,-84.627248&destination=33.998988,-84.623871&key=AIzaSyC47vUPrhkZ1N1Y1SJ8krFSUr61kLm3NT0&waypoints=34.041726,-84.616121|34.041726,-84.616121";
	$right = "https://maps.googleapis.com/maps/api/directions/json?units=imperial&origin=34.052724,-84.627248&destination=33.998988,-84.623871&key=AIzaSyC47vUPrhkZ1N1Y1SJ8krFSUr61kLm3NT0&waypoints=34.039165,-84.627021|34.039165,-84.627021|34.016189,-84.626764";

	if ($side == "left") {
		$contents = file_get_contents($left);
	} else {
		$contents = file_get_contents($right);
	}

	$array = json_decode($contents, true);

	$duration = $array["routes"][0]["legs"][0]["duration"]["value"];
	$duration += $array["routes"][0]["legs"][1]["duration"]["value"];
	$duration += $array["routes"][0]["legs"][2]["duration"]["value"];
	if ($side == "right") {
		$duration += $array["routes"][0]["legs"][3]["duration"]["value"];
	}

	return floor(($duration / 60) + 1) . ":" . str_pad($duration % 60, 2, "0", STR_PAD_LEFT);
}

function getHarrison() {
	$contents = file_get_contents("https://maps.googleapis.com/maps/api/directions/json?units=imperial&origin=33.998988,-84.623871&destination=33.966873,-84.683018&key=AIzaSyC47vUPrhkZ1N1Y1SJ8krFSUr61kLm3NT0");

	$array = json_decode($contents, true);

	$duration = $array["routes"][0]["legs"][0]["duration"]["value"];

	return floor(($duration / 60) + 1) . ":" . str_pad($duration % 60, 2, "0", STR_PAD_LEFT);

}

echo '<div class="item"><h1>Travel Time</h1><img src="../serviceIcons/travel.png" class="icon">';
echo "<table><tr><th>Left to KMT</th><th>Right to KMT</th><th>KMT to HRS</th></tr>";
echo '<tr><td style="font-size: 25px;">'.getDuration("left").'</td><td style="font-size: 25px;">'.getDuration("right").'</td><td style="font-size: 25px;">'.getHarrison().'</td></tr>';
echo "</table></div>";
