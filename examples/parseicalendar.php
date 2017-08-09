<?php
/**
 * parseicalendar.php
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2017 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

/**
 * Parse iCalendar Example
 *
 * Enter an ics filename or URL on the command line, 
 * or leave blank to parse the default file.
 *
 */

require_once("../zapcallib.php");

$icalfile = count($argv) > 1 ? $argv[1] : "abrahamlincoln.ics";
$icalfeed = file_get_contents($icalfile);

// create the ical object
$icalobj = new ZCiCal($icalfeed);

echo "Number of events found: " . $icalobj->countEvents() . "\n";

$ecount = 0;

// read back icalendar data that was just parsed
if(isset($icalobj->tree->child))
{
	foreach($icalobj->tree->child as $node)
	{
		if($node->getName() == "VEVENT")
		{
			$ecount++;
			echo "Event $ecount:\n";
			foreach($node->data as $key => $value)
			{
				if(is_array($value))
				{
					for($i = 0; $i < count($value); $i++)
					{
						$p = $value[$i]->getParameters();
						echo "  $key: " . $value[$i]->getValues() . "\n";
					}
				}
				else
				{
					echo "  $key: " . $value->getValues() . "\n";
				}
			}
		}
	}
}
