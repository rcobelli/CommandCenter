<?php
switch (date('N')) {
	case 1:
		$workout = "Rest";
		break;
	case 2:
		$workout = "Long Run";
		break;
	case 3:
		$workout = "Recovery Run";
		break;
	case 4:
		$workout = "Speed Day";
		break;
	case 5:
		$workout = "Rest";
		break;
	case 6:
		$workout = "Match Day";
		break;
	default:
		$workout = "Match Day";
		break;
}

echo '<div class="item"><h1>Today\'s Workout</h1><img src="../serviceIcons/workout.png" class="icon">';
echo "<table><tr><td style='font-size: 32px'>" . $workout . "</td><td>";
echo "</td></tr></table></div>";
