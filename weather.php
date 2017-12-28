<?php
$jsonurl = "http://api.openweathermap.org/data/2.5/weather?q=kennesaw&APPID=5f65812acce4b77fb724ad730e18709a&units=imperial";
$json = file_get_contents($jsonurl);

$weather = json_decode($json, true);

echo '<div class="item"><h1>Weather</h1><img src="../serviceIcons/weather.png" class="icon">';
echo "<table><tr><td style='font-size: 32px'>" . (int)$weather["main"]["temp"] . "° and " . $weather["weather"][0]["main"] . "</td><td>";
echo "<img src='../weatherIcons/" . substr($weather["weather"][0]["icon"], 0, 2) . ".png' style='float:right; margin-right: 5px;'>";
echo "</td></tr></table></div>";
