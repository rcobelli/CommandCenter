<?php

echo "<h1>Command Center</h1>";

include("weather.php");
echo "<hr/>";
include_once("calendar.php");
todayEvents();
echo "<hr/>";
tomorrowEvents();
echo "<hr/>";
include("showerButton.php");
echo "<hr/>";
include("wunderlist.php");
echo "<hr/>";
$alertsOnly = false;
include("aws.php");
echo "<hr/>";
include("nytButton.php");
echo "<hr/>";
include("travelTime.php");
echo "<hr/>";
include("appAnalytics.php");
