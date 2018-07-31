<?php

include_once("calendarLib.php");


function showReminders()
{
    global $icsEvents;

    $debug = false;

    // Remove Google metadata
    unset($icsEvents [1]);

    date_default_timezone_set('America/New_York');

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

                $diffDays = floor(($day - $today) / (60*60*24));

                if ($diffDays == 0) { // Today
                    array_push($data, $item);
                    if ($debug) {
                        echo "//Today " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
                    }
                } elseif ($diffDays < 0) { // Past
                    array_push($data, $item);
                    if ($debug) {
                        echo "//Yesterday " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
                    }
                } elseif ($diffDays > 0) { // Future
                    if ($debug) {
                        echo "//Tomorrow " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
                    }
                } else {
                    // Something went wrong
                }
            } else {
                $timestamp = $item['DTSTART;VALUE=DATE'];

                $timestamp = substr($timestamp, 0, 8); // Remove the \r from the time
                $day = strtotime($timestamp);
                $today = strtotime(date('Y-m-d'));

                if ($debug) {
                    echo date('Y-m-d H:i:s', $timestamp);
                }

                $diffDays = floor(($day - $today) / (60*60*24));

                if ($diffDays == 0) { // Today
                    array_push($data, $item);
                    if ($debug) {
                        echo "//Today " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
                    }
                } elseif ($diffDays < 0) { // Past
                    array_push($data, $item);
                    if ($debug) {
                        echo "//Yesterday " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
                    }
                } elseif ($diffDays > 0) { // Future
                    if ($debug) {
                        echo "//Tomorrow " . $item["SUMMARY"] . var_dump($diff) . "<br/><br/>";
                    }
                } else {
                    // Something went wrong
                }
            }
        }
    }

    if ($debug) {
        //exit(json_encode($icsEvents));
    }

    $title = "Evernote";

    $html = '<div class="item"><h1 ' . $css . '>'.$title.'</h1><img src="../serviceIcons/evernote.png" class="icon"><table><tr><th> Note </th></tr>';
    if (count($data) == 0) {
        $html .= '<tr><td colspan=3>Nothing for today</td></tr>';
    } else {
        foreach ($data as $icsEvent) {
            /* Getting start date and time */
            $start = isset($icsEvent ['DTSTART;VALUE=DATE']) ? $icsEvent ['DTSTART;VALUE=DATE'] : $icsEvent ['DTSTART'];
            /* Converting to datetime and apply the timezone to get proper date time */
            $startDt = new DateTime($start);
            $startDt->setTimeZone(new DateTimezone('America/New_York'));
            if ($calToday) {
                $startDate = $startDt->format('h:i a');
            } else {
                $startDate = $startDt->format('h:i a');
            }
            /* Getting end date with time */
            $end = isset($icsEvent ['DTEND;VALUE=DATE']) ? $icsEvent ['DTEND;VALUE=DATE'] : $icsEvent ['DTEND'];
            $endDt = new DateTime($end);
            $endDt->setTimeZone(new DateTimezone('America/New_York'));
            if ($calToday) {
                $endDate = $endDt->format('h:i a');
            } else {
                $endDate = $endDt->format('h:i a');
            }
            /* Getting the name of event */
            $eventName = $icsEvent['SUMMARY'];
            $html .= '<tr><td>'.$eventName.'</td></tr>';
        }
    }
    echo $html.'</table></div>';
}


$file = "https://calendar.google.com/calendar/ical/e7rmi6rina681kqtahhuf3tfro%40group.calendar.google.com/private-1adcd98842342a2b242f65317496cf27/basic.ics";
// Import lib
$obj = new ics();
$icsEvents = $obj->getIcsEventsAsArray($file);

showReminders();
