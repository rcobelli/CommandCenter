<?php


$url = "https://www.nytimes.com/newsletters/" . date('Y/m/d') . "/";
if (date('G') > 18) {
	$url .= "evening-briefing";
} else {
	$url .= "morning-briefing";
}

echo "<a href='$url'>View NY Times Briefing</a>";
