<?php
/**
 * simpleevent.php
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2017 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

/**
 * Simple Event Example
 *
 * Create a simple iCalendar event
 * No time zone specified, so this event will be in UTC time zone
 *
 */

require_once("../zapcallib.php");

$title = "Simple Event";
// date/time is in SQL datetime format
$event_start = "2020-01-01 12:00:00";
$event_end = "2020-01-01 13:00:00";

// create the ical object
$icalobj = new ZCiCal();

// create the event within the ical object
$eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);

// add title
$eventobj->addNode(new ZCiCalDataNode("SUMMARY:" . $title));

// add start date
$eventobj->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($event_start)));

// add end date
$eventobj->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($event_end)));

// UID is a required item in VEVENT, create unique string for this event
// Adding your domain to the end is a good way of creating uniqueness
$uid = date('Y-m-d-H-i-s') . "@demo.icalendar.org";
$eventobj->addNode(new ZCiCalDataNode("UID:" . $uid));

// DTSTAMP is a required item in VEVENT
$eventobj->addNode(new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime()));

// Add description
$eventobj->addNode(new ZCiCalDataNode("Description:" . ZCiCal::formatContent(
	"This is a simple event, using the Zap Calendar PHP library. " .
	"Visit http://icalendar.org to validate icalendar files.")));

// write iCalendar feed to stdout
echo $icalobj->export();

