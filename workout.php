<?php

switch (date('N')) {
    case 1: // Monday
        $workout = "Rest";
        break;
    case 2: // Tuesday
        $workout = "Long Run";
        break;
    case 3: // Wednesday
        $workout = "Rest";
        break;
    case 4: // Thursday
        $workout = "Speed Day";
        break;
    case 5: // Friday
        $workout = "Rest";
        break;
    case 6: // Saturday
        $workout = "Match Day";
        break;
    default: // Sunday
        $workout = "Match Day";
        break;
}

echo '<div class="item"><h1>Today\'s Workout</h1><img src="../serviceIcons/workout.png" class="icon">';
echo "<table><tr><td style='font-size: 32px'>" . $workout . "</td><td>";
echo "</td></tr></table></div>";
