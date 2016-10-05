<?php

require_once("../zapcallib.php");

/*
 * Simple Event
 *
 * Create a simple iCalendar event
 *
 * No time zone specified, so this event will be in UTC time zone
 *
 */

$title = "Simple Event";
// date/time is in SQL datetime format
$event_start = "2020-01-01 12:00:00";
$event_end = "2020-01-01 13:00:00";

// create the ical object
$icalobj = new ZCiCal();

// create the event within the ical object
$eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);

// add title
$datanode = new ZCiCalDataNode("SUMMARY:" . $title);
$eventobj->data[$datanode->getName()] = $datanode;

// add start date
$datanode = new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($event_start));
$eventobj->data[$datanode->getName()] = $datanode;

// add end date
$datanode = new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($event_end));
$eventobj->data[$datanode->getName()] = $datanode;

// UID is a required item in VEVENT, create unique string for this event
// Adding your domain to the end is a good way of creating uniqueness
$uid = date('Y-m-d-H-i-s') . "@demo.icalendar.org";
$datanode = new ZCiCalDataNode("UID:" . $uid);
$eventobj->data[$datanode->getName()] = $datanode;

// DTSTAMP is a required item in VEVENT
$datanode = new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime());
$eventobj->data[$datanode->getName()] = $datanode;

// Add description
$datanode = new ZCiCalDataNode("Description:" . ZCiCal::formatContent(
	"This is a simple event, using the Zap Calendar PHP library. " .
	"Visit http://icalendar.org to validate icalendar files."));
$eventobj->data[$datanode->getName()] = $datanode;

// write iCalendar feed
echo $icalobj->export();

