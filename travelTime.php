<?php

// TODO: Based on day of week, what time I need to leave to make it to class

echo '<div class="item"><h1>Travel Time</h1><img src="../serviceIcons/travel.png" class="icon">';
echo "<table><tr><th>Left to KMT</th><th>Right to KMT</th><th>KMT to HRS</th></tr>";
echo '<tr><td style="font-size: 25px;">'.getDuration("left").'</td><td style="font-size: 25px;">'.getDuration("right").'</td><td style="font-size: 25px;">'.getHarrison().'</td></tr>';
echo "</table></div>";
