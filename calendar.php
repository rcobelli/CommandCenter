<?php

include_once('calendarLib.php');

$file = "https://calendar.google.com/calendar/ical/ryan.cobelli%40gmail.com/private-9740322ac5c4a1a471628cfbb3c36075/basic.ics";
// Import lib
$obj = new ics();
$icsEvents = $obj->getIcsEventsAsArray( $file );

$file = "https://calendar.google.com/calendar/ical/rybelllc%40gmail.com/private-6f9c832b54927adecc010e430df6f032/basic.ics";
$icsEvents2 = $obj->getIcsEventsAsArray( $file );

$icsEvents = array_merge($icsEvents, $icsEvents2);

function todayEvents() {
	printEvents(true, false);
}

function tomorrowEvents() {
	printEvents(false, true);
}
