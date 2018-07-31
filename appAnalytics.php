<?php

include_once('appAnalyticsLib.php');

$ids = ["Emoji Keyboard" => 971878648,
                "Core X" => 972403903,
                "Inside The Box" => 894459208,
                "Frame Timer" => 1023278352,
                "Gradespark" => 972429322,
                "Trac Time" => 1223459141,
                "Legacy Park" => 1109233591,
                "Swim Board" => 853911322,
                "Big Shanty" => 1260389036,
                "Baker" => 1262793125,
                "Magnet Treats" => 1122603283];

function getDownloads($id)
{
    global $itunes;

    $allStats = $itunes->getStats(
        new DateTime('01-07-2015'),
        new DateTime(),
        $id
    );

    $installs = 0;

    echo $allStats;

    foreach ($allStats as $day) {
        $installs += $day['installs'];
    }

    return $installs;
}

$itunes = new iTunesConnect("test@rybel-llc.com", "6iTakfxh8cL");

foreach ($ids as $name => $id) {
    echo $name . ": " . getDownloads($id) . "<br/>";
}
