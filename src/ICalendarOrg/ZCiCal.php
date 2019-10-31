<?php
/**
 * ical.php	create iCalendar data structure
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2018 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

namespace ICalendarOrg;

/**
 * The main iCalendar object containing ZCiCalDataNodes and ZCiCalNodes.
 */
class ZCiCal
	{
	/**
	 * The most recently created  node in the tree
	 *
	 * @var object
	 */
	public $curnode = null;
	/**
	 * The root node of the object tree
	 *
	 * @var object
	 */
	public $tree = null;

	/**
	 * The main iCalendar object containing ZCiCalDataNodes and ZCiCalNodes.
	 *
	 * use maxevents and startevent to read events in multiple passes (to save memory)
	 *
	 * @param string $data icalendar feed string (empty if creating new feed)
	 * @param int $maxevents maximum # of events to read
	 * @param int $startevent starting event to read
	 *
	 * @throws Exception
	 */
	public function __construct($data = '', $maxevents = 1000000, $startevent = 0)
		{
		if ($data != '')
			{
			// unfold lines
			// first change all eol chars to "\n"
			$data = str_replace(["\r\n", "\n\r", "\n", "\r"], "\n", $data);
			// now unfold lines
			//$data = str_replace(array("\n ", "\n	"),"!?", $data);
			$data = str_replace(["\n ", "\n	"], '', $data);
			// replace special iCal chars
			$data = str_replace(['\\\\', "\,"], ['\\', ','], $data);

			// parse each line
			$lines = explode("\n", $data);

			$linecount = 0;
			$eventcount = 0;
			$eventpos = 0;
			foreach ($lines as $line)
				{
				if (substr($line, 0, 6) == 'BEGIN:')
					{
					// start new object
					$name = substr($line, 6);
					if ($name == 'VEVENT')
						{
						if ($eventcount < $maxevents && $eventpos >= $startevent)
							{
							$this->curnode = new ZCiCalNode($name, $this->curnode);
							if ($this->tree == null)
								{
								$this->tree = $this->curnode;
								}
							}
						}
					else
						{
						$this->curnode = new ZCiCalNode($name, $this->curnode);
						if ($this->tree == null)
							{
							$this->tree = $this->curnode;
							}
						}
					}
				elseif (substr($line, 0, 4) == 'END:')
					{
					$name = substr($line, 4);
					if ($name == 'VEVENT')
						{
						if ($eventcount < $maxevents && $eventpos >= $startevent)
							{
							$eventcount++;
							if ($this->curnode->getName() != $name)
								{
								throw new \Exception("Can't read iCal file structure, expecting " . $this->curnode->getName() . ' but reading $name instead');
								}
							if ($this->curnode->getParent() != null)
								{
								$this->curnode = $this->curnode->getParent();
								}
							}
						$eventpos++;
						}
					else
						{
						if ($this->curnode->getName() != $name)
							{
							throw new \Exception("Can't read iCal file structure, expecting " . $this->curnode->getName() . ' but reading $name instead');
							}
						if ($this->curnode->getParent() != null)
							{
							$this->curnode = $this->curnode->getParent();
							}
						}
					}
				else
					{
					$datanode = new ZCiCalDataNode($line);
					if ($this->curnode->getName() == 'VEVENT')
						{
						if ($eventcount < $maxevents && $eventpos >= $startevent)
							{
							if ($datanode->getName() == 'EXDATE')
								{
								if (! array_key_exists($datanode->getName(), $this->curnode->data))
									{
									$this->curnode->data[$datanode->getName()] = $datanode;
									}
								else
									{
									$this->curnode->data[$datanode->getName()]->value[] = $datanode->value[0];
									}
								}
							else
								{
								if (! array_key_exists($datanode->getName(), $this->curnode->data))
									{
									$this->curnode->data[$datanode->getName()] = $datanode;
									}
								else
									{
									$tnode = $this->curnode->data[$datanode->getName()];
									$this->curnode->data[$datanode->getName()] = [];
									$this->curnode->data[$datanode->getName()][] = $tnode;
									$this->curnode->data[$datanode->getName()][] = $datanode;
									}
								}
							}
						}
					else
						{
						if ($datanode->getName() == 'EXDATE')
							{
							if (! array_key_exists($datanode->getName(), $this->curnode->data))
								{
								$this->curnode->data[$datanode->getName()] = $datanode;
								}
							else
								{
								$this->curnode->data[$datanode->getName()]->value[] = $datanode->value[0];
								}
							}
						else
							{
							if (! array_key_exists($datanode->getName(), $this->curnode->data))
								{
								$this->curnode->data[$datanode->getName()] = $datanode;
								}
							else
								{
								$tnode = $this->curnode->data[$datanode->getName()];
								$this->curnode->data[$datanode->getName()] = [];
								$this->curnode->data[$datanode->getName()][] = $tnode;
								$this->curnode->data[$datanode->getName()][] = $datanode;
								}
							}
						}
					}
				$linecount++;
				}
			}
		else
			{
			$name = 'VCALENDAR';
			$this->curnode = new ZCiCalNode($name, $this->curnode);
			$this->tree = $this->curnode;
			$datanode = new ZCiCalDataNode('VERSION:2.0');
			$this->curnode->data[$datanode->getName()] = $datanode;

			$datanode = new ZCiCalDataNode('PRODID:-//ZContent.net//ZapCalLib 1.0//EN');
			$this->curnode->data[$datanode->getName()] = $datanode;
			$datanode = new ZCiCalDataNode('CALSCALE:GREGORIAN');
			$this->curnode->data[$datanode->getName()] = $datanode;
			$datanode = new ZCiCalDataNode('METHOD:PUBLISH');
			$this->curnode->data[$datanode->getName()] = $datanode;
			}
		}

	/**
	 * CountEvents()
	 *
	 * Return the # of VEVENTs in the object
	 *
	 * @return int
	 */

	public function countEvents()
		{
		$count = 0;
		if (isset($this->tree->child))
			{
			foreach ($this->tree->child as $child)
				{
				if ($child->getName() == 'VEVENT')
					{
					$count++;
					}
				}
			}

		return $count;
		}

	/**
	 * CountVenues()
	 *
	 * Return the # of VVENUEs in the object
	 *
	 * @return int
	 */

	public function countVenues()
		{
		$count = 0;
		if (isset($this->tree->child))
			{
			foreach ($this->tree->child as $child)
				{
				if ($child->getName() == 'VVENUE')
					{
					$count++;
					}
				}
			}

		return $count;
		}

	/**
	 * Export object to string
	 *
	 * This function exports all objects to an iCalendar string
	 *
	 * @return string an iCalendar formatted string
	 */
	public function export()
		{
		return $this->tree->export($this->tree);
		}

	/**
	 * Escape slashes, commas and semicolons in strings
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function formatContent($content)
		{
		$content = str_replace(['\\', ',', ';'], ['\\\\', '\\,', '\\;'], $content);

		return $content;
		}

	/**
	 * Format into iCal time format from SQL date or SQL date-time format
	 *
	 * @param string $datetime SQL date or SQL date-time string
	 *
	 * @return string iCal formatted string
	 * @deprecated Use ZDateHelper::fromSqlDateTime() instead
	 */
	public static function fromSqlDateTime($datetime = '')
		{
		if ($datetime == '')
			{
			$datetime = ZDateHelper::toSQLDateTime();
			}
		if (strlen($datetime) > 10)
			{
			return sprintf('%04d%02d%02dT%02d%02d%02d', substr($datetime, 0, 4), substr($datetime, 5, 2), substr($datetime, 8, 2),
																							 substr($datetime, 11, 2), substr($datetime, 14, 2), substr($datetime, 17, 2));
			}

		return sprintf('%04d%02d%02d', substr($datetime, 0, 4), substr($datetime, 5, 2), substr($datetime, 8, 2));
		}

	/**
	 * fromUnixDate()
	 *
	 * Take Unix timestamp and format to iCal date string
	 *
	 * @param int $datetime Unix timestamp, leave blank for current date/time
	 *
	 * @return string formatted iCal date string
	 * @deprecated Use ZDateHelper::fromUnixDateTimetoiCal() instead
	 */
	public static function fromUnixDate($datetime = null)
		{
		date_default_timezone_set('UTC');
		if ($datetime == null)
			{
			$datetime = time();
			}

		return date('Ymd', $datetime);
		}

	/**
	 * fromUnixDateTime()
	 *
	 * Take Unix timestamp and format to iCal date/time string
	 *
	 * @param int $datetime Unix timestamp, leave blank for current date/time
	 *
	 * @return string formatted iCal date/time string
	 * @deprecated Use ZDateHelper::fromUnixDateTimetoiCal() instead
	 */
	public static function fromUnixDateTime($datetime = null)
		{
		date_default_timezone_set('UTC');
		if ($datetime == null)
			{
			$datetime = time();
			}

		return date('Ymd\THis', $datetime);
		}

	/**
	 * Get first child in object list
	 * Use getNextSibling() and getPreviousSibling() to navigate through list
	 *
	 * @param object $thisnode The parent object
	 *
	 * @return object The child object
	 */
	public function getFirstChild($thisnode)
		{
		if (count($thisnode->child) > 0)
			{
			return $thisnode->child[0];
			}

		return null;
		}

	/**
	 * Get first event in object list
	 * Use getNextEvent() to navigate through list
	 *
	 * @return object The first event, or null
	 */
	public function getFirstEvent()
		{
		if ($this->countEvents() > 0)
			{
			$child = $this->tree->child[0];
			$event = false;
			while (! $event && $child != null)
				{
				if ($child->getName() == 'VEVENT')
					{
					$event = true;
					}
				else
					{
					$child = $child->next;
					}
				}

			return $child;
			}

		return null;
		}

	/**
	 * Get first venue in object list
	 * Use getNextVenue() to navigate through list
	 *
	 * @return object The first venue, or null
	 */
	public function getFirstVenue()
		{
		if ($this->countVenues() > 0)
			{
			$child = $this->tree->child[0];
			$event = false;
			while (! $event && $child != null)
				{
				if ($child->getName() == 'VVENUE')
					{
					$event = true;
					}
				else
					{
					$child = $child->next;
					}
				}

			return $child;
			}

		return null;
		}

	/**
	 * Get next event in object list
	 *
	 * @param object $event The current event object
	 *
	 * @return object Returns the next event or null if past last event
	 */
	public function getNextEvent($event)
		{
		do
			{
			$event = $event->next;
			} while ($event != null && $event->getName() != 'VEVENT');

		return $event;
		}

	/**
	 * Get next sibling in object list
	 *
	 * @param object $thisnode The current object
	 *
	 * @return object Returns the next sibling
	 */
	public function getNextSibling($thisnode)
		{
		return $thisnode->next;
		}

	/**
	 * Get next venue in object list
	 *
	 * @param object $venue The current venue object
	 *
	 * @return object Returns the next venue or null if past last venue
	 */
	public function getNextVenue($venue)
		{
		do
			{
			$venue = $venue->next;
			} while ($venue != null && $venue->getName() != 'VVENUE');

		return $venue;
		}

	/**
	 * Get previous sibling in object list
	 *
	 * @param object $thisnode The current object
	 *
	 * @return object Returns the previous sibling
	 */
	public function getPrevSibling($thisnode)
		{
		return $thisnode->prev;
		}

	/**
	 * Pull timezone data from node and put in array
	 *
	 * Returning array contains the following array keys: tzoffsetfrom, tzoffsetto, tzname, dtstart, rrule
	 *
	 * @param array $node timezone object
	 *
	 * @return array
	 */
	public static function getTZValues($node)
		{
		$tzvalues = [];

		$tnode = @$node->data['TZOFFSETFROM'];
		if ($tnode != null)
			{
			$tzvalues['tzoffsetfrom'] = $tnode->getValues();
			}

		$tnode = @$node->data['TZOFFSETTO'];
		if ($tnode != null)
			{
			$tzvalues['tzoffsetto'] = $tnode->getValues();
			}

		$tnode = @$node->data['TZNAME'];
		if ($tnode != null)
			{
			$tzvalues['tzname'] = $tnode->getValues();
			}
		else
			{
			$tzvalues['tzname'] = '';
			}

		$tnode = @$node->data['DTSTART'];
		if ($tnode != null)
			{
			$tzvalues['dtstart'] = ZDateHelper::fromiCaltoUnixDateTime($tnode->getValues());
			}

		$tnode = @$node->data['RRULE'];
		if ($tnode != null)
			{
			$tzvalues['rrule'] = $tnode->getValues();
			}
		else
			{
			// no rule specified, let's create one from based on the date
			$date = getdate($tzvalues['dtstart']);
			$month = $date['mon'];
			$day = $date['mday'];
			$tzvalues['rrule'] = 'FREQ=YEARLY;INTERVAL=1;BYMONTH=$month;BYMONTHDAY=$day';
			}

		return $tzvalues;
		}

	/**
	 * Format iCal time format to either SQL date or SQL date-time format
	 *
	 * @param string $datetime icalendar formatted date or date-time
	 *
	 * @return string SQL date or SQL date-time string
	 * @deprecated Use ZDateHelper::toSQLDateTime() instead
	 */
	public static function toSQLDateTime($datetime = '')
		{
		if ($datetime == '')
			{
			return ZDateHelper::toSQLDateTime();
			}
		if (strlen($datetime) > 10)
			{
			return sprintf('%04d-%02d-%02d %02d:%02d:%02d', substr($datetime, 0, 4), substr($datetime, 5, 2), substr($datetime, 8, 2),
																							 substr($datetime, 11, 2), substr($datetime, 14, 2), substr($datetime, 17, 2));
			}

		return sprintf('%04d-%02d-%02d', substr($datetime, 0, 4), substr($datetime, 5, 2), substr($datetime, 8, 2));
		}

	/**
	 * Read date/time in iCal formatted string
	 *
	 * @param string iCal formated date/time string
	 *
	 * @return int Unix timestamp
	 * @deprecated Use ZDateHelper::toUnixDateTime() instead
	 */
	public function toUnixDateTime($datetime)
		{
		$year = substr($datetime, 0, 4);
		$month = substr($datetime, 4, 2);
		$day = substr($datetime, 6, 2);
		$hour = 0;
		$minute = 0;
		$second = 0;
		if (strlen($datetime) > 8 && $datetime{8} == 'T')
			{
			$hour = substr($datetime, 9, 2);
			$minute = substr($datetime, 11, 2);
			$second = substr($datetime, 13, 2);
			}

		return mktime($hour, $minute, $second, $month, $day, $year);
		}
	}


