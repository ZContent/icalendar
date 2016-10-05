<?php

require_once("../zapcallib.php");

/*
 * Event with local timezone
 *
 */

$title = "Event in New York timezone";
// date/time is in SQL datetime format
$event_start = "2020-01-01 12:00:00";
$event_end = "2020-01-01 13:00:00";
// timezone must be a supported PHP timezone
// (see http://php.net/manual/en/timezones.php )
// Note: multi-word timezones must use underscore "_" separator
$tzid = "America/New_York";

// create the ical object
$icalobj = new ZCiCal();

// Add timezone data
ZCTimeZoneHelper::getTZNode(substr($event_start,0,4),substr($event_end,0,4),$tzid, $icalobj->curnode);

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

echo $icalobj->export();

