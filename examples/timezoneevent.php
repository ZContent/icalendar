<?php
/**
 * timezoneevent.php
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2018 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

include '../vendor/autoload.php';

/*
 * Create Event Example With Local Timezone
 */

$title = 'Event in New York timezone';
// date/time is in SQL datetime format
$event_start = '2020-01-01 12:00:00';
$event_end = '2020-01-01 13:00:00';
// timezone must be a supported PHP timezone
// (see http://php.net/manual/en/timezones.php )
// Note: multi-word timezones must use underscore '_' separator
$tzid = 'America/New_York';

// create the ical object
$icalobj = new \ICalendarOrg\ZCiCal();

// Add timezone data
\ICalendarOrg\ZCTimeZoneHelper::getTZNode(substr($event_start,0,4),substr($event_end,0,4),$tzid, $icalobj->curnode);

// create the event within the ical object
$eventobj = new \ICalendarOrg\ZCiCalNode('VEVENT', $icalobj->curnode);

// add title
$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('SUMMARY:' . $title));

// add start date
$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DTSTART:' . \ICalendarOrg\ZDateHelper::fromSqlDateTime($event_start)));

// add end date
$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DTEND:' . \ICalendarOrg\ZDateHelper::fromSqlDateTime($event_end)));

// UID is a required item in VEVENT, create unique string for this event
// Adding your domain to the end is a good way of creating uniqueness
$uid = date('Y-m-d-H-i-s') . '@demo.icalendar.org';
$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('UID:' . $uid));

// DTSTAMP is a required item in VEVENT
$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DTSTAMP:' . \ICalendarOrg\ZDateHelper::fromSqlDateTime()));

// write iCalendar feed to stdout
echo $icalobj->export();

