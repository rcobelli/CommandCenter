<?php

class ics {
    /* Function is to get all the contents from ics and explode all the datas according to the events and its sections */
    function getIcsEventsAsArray($file) {
        $icalString = file_get_contents ( $file );
        $icsDates = array ();
        /* Explode the ICs Data to get datas as array according to string ‘BEGIN:’ */
        $icsData = explode ( "BEGIN:", $icalString );
        /* Iterating the icsData value to make all the start end dates as sub array */
        foreach ( $icsData as $key => $value ) {
            $icsDatesMeta [$key] = explode ( "\n", $value );
        }
        /* Itearting the Ics Meta Value */
        foreach ( $icsDatesMeta as $key => $value ) {
            foreach ( $value as $subKey => $subValue ) {
                /* to get ics events in proper order */
                $icsDates = $this->getICSDates ( $key, $subKey, $subValue, $icsDates );
            }
        }
        return $icsDates;
    }

    /* funcion is to avaid the elements wich is not having the proper start, end  and summary informations */
    function getICSDates($key, $subKey, $subValue, $icsDates) {
        if ($key != 0 && $subKey == 0) {
            $icsDates [$key] ["BEGIN"] = $subValue;
        } else {
            $subValueArr = explode ( ":", $subValue, 2 );
            if (isset ( $subValueArr [1] )) {
                $icsDates [$key] [$subValueArr [0]] = $subValueArr [1];
            }
        }
        return $icsDates;
    }
}


function printEvents($calToday, $calTomorrow) {
	global $icsEvents;

	$debug = false;

	// Remove Google metadata
	unset( $icsEvents [1] );

	date_default_timezone_set ('America/New_York');

	// Applicable data
	$data = array();

	// Sort through literally every event on my calendar
	foreach ($icsEvents as $item) {
		if (!is_null($item["SUMMARY"])) {
			if (!is_null($item['DTSTART'])) {
				$timestamp = $item['DTSTART'];

				$timestamp = substr($timestamp, 0, 15) . "Etc/Zulu"; // Remove the \r from the time
				$day = strtotime($timestamp);
				$today = strtotime(date('Y-m-d'));

				if ($debug) {
					echo date('Y-m-d H:i:s', $timestamp);
				}

				$diffDays = floor(($day - $today) / (60*60*24) );

				if ($diffDays == 0 && $calToday) {
					array_push($data, $item);
					if ($debug) {
						echo "//Today " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($diffDays == -1) {
					if ($debug) {
						echo "//Yesterday " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($diffDays == 1 && $calTomorrow) {
					array_push($data, $item);
					if ($debug) {
						echo "//Tomorrow " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($diffDays > 0 && $difDays < 14) {
					if ($debug) {
						echo "//Future " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($difDays > -14  && $diffDays < 0) {
					if ($debug) {
						echo "//Past " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else {
					// Too far away to care about
				}
			} else {
				$timestamp = $item['DTSTART;VALUE=DATE'];

				$timestamp = substr($timestamp, 0, 8); // Remove the \r from the time
				$day = strtotime($timestamp);
				$today = strtotime(date('Y-m-d'));

				if ($debug) {
					echo date('Y-m-d H:i:s', $timestamp);
				}

				$diffDays = floor(($day - $today) / (60*60*24) );

				if ($diffDays == 0 && $calToday) {
					array_push($data, $item);
					if ($debug) {
						echo "//Today " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($diffDays == -1) {
					if ($debug) {
						echo "//Yesterday " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($diffDays == 1 && $calTomorrow) {
					array_push($data, $item);
					if ($debug) {
						echo "//Tomorrow " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($diffDays > 0 && $difDays < 14) {
					if ($debug) {
						echo "//Future " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else if ($difDays > -14  && $diffDays < 0) {
					if ($debug) {
						echo "//Past " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
					}
				} else {
					// Too far away to care about
				}
			}
		}
	}

	if ($debug) {
		//exit(json_encode($icsEvents));
	}

	if ($calToday) {
		$title = "Today's Calendar";
	} else {
		$title = "Tomorrow's Calendar";
	}

	$html = '<div class="item"><h1>'.$title.'</h1><img src="../serviceIcons/calendar.png" class="icon"><table><tr><th> Event </th><th> Start at </th><th> End at </th></tr>';
	foreach( $data as $icsEvent){
	        /* Getting start date and time */
	        $start = isset( $icsEvent ['DTSTART;VALUE=DATE'] ) ? $icsEvent ['DTSTART;VALUE=DATE'] : $icsEvent ['DTSTART'];
	        /* Converting to datetime and apply the timezone to get proper date time */
	        $startDt = new DateTime ( $start );
	        $startDt->setTimeZone ( new DateTimezone ('America/New_York') );
	        $startDate = $startDt->format ( 'm/d/Y h:i a' );
	        /* Getting end date with time */
	        $end = isset( $icsEvent ['DTEND;VALUE=DATE'] ) ? $icsEvent ['DTEND;VALUE=DATE'] : $icsEvent ['DTEND'];
	        $endDt = new DateTime ( $end );
			$endDt->setTimeZone ( new DateTimezone ('America/New_York') );
	        $endDate = $endDt->format ( 'm/d/Y h:i a' );
	        /* Getting the name of event */
	        $eventName = $icsEvent['SUMMARY'];
	        $html .= '<tr><td>'.$eventName.'</td><td>'.$startDate.'</td><td>'.$endDate.'</td></tr>';
	}
	echo $html.'</table></div>';
}
