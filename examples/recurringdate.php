<?php
/**
 * recurringdate.php
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2017 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

/**
 * Recurring Date Example
 *
 * Recurring date examples with RRULE property
 *
 */

require_once("../zapcallib.php");

$examples = 
array(
	array(
		"name" => "Abraham Lincon's birthday",
		"date" => "2015-02-12",
		"rule" => "FREQ=YEARLY;INTERVAL=1;BYMONTH=2;BYMONTHDAY=12"
	),

	array(
		"name" => "Start of U.S. Supreme Court Session (1st Monday in October)",
		"date" => "2015-10-01",
		"rule" => "FREQ=YEARLY;INTERVAL=1;BYMONTH=10;BYDAY=1MO"
	)
);

// Use maxdate to limit # of infinitely repeating events
$maxdate = strtotime("2021-01-01");

foreach($examples as $example)
{
	echo $example["name"] . ":\n";
	$rd = new ZCRecurringDate($example["rule"],strtotime($example["date"]));
	$dates = $rd->getDates($maxdate);
	foreach($dates as $d)
	{
		echo "  " . date('l, F j, Y ',$d) . "\n";
	}
	echo "\n";
}
