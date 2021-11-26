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
	public function __construct(string $data = '', int $maxevents = 1000000, int $startevent = 0)
		{
		if ('' != $data)
			{
			// unfold lines
			// first change all eol chars to "\n"
			$data = \str_replace(["\r\n", "\n\r", "\n", "\r"], "\n", $data);
			// now unfold lines
			//$data = str_replace(array("\n ", "\n	"),"!?", $data);
			$data = \str_replace(["\n ", "\n	"], '', $data);
			// replace special iCal chars
			$data = \str_replace(['\\\\', "\,"], ['\\', ','], $data);

			// parse each line
			$lines = \explode("\n", $data);

			$linecount = 0;
			$eventcount = 0;
			$eventpos = 0;

			foreach ($lines as $line)
				{
				if ('BEGIN:' == \substr($line, 0, 6))
					{
					// start new object
					$name = \substr($line, 6);

					if ('VEVENT' == $name)
						{
						if ($eventcount < $maxevents && $eventpos >= $startevent)
							{
							$this->curnode = new \ICalendarOrg\ZCiCalNode($name, $this->curnode);

							if (null == $this->tree)
								{
								$this->tree = $this->curnode;
								}
							}
						}
					else
						{
						$this->curnode = new \ICalendarOrg\ZCiCalNode($name, $this->curnode);

						if (null == $this->tree)
							{
							$this->tree = $this->curnode;
							}
						}
					}
				elseif ('END:' == \substr($line, 0, 4))
					{
					$name = \substr($line, 4);

					if ('VEVENT' == $name)
						{
						if ($eventcount < $maxevents && $eventpos >= $startevent)
							{
							$eventcount++;

							if ($this->curnode->getName() != $name)
								{
								throw new \Exception("Can't read iCal file structure, expecting " . $this->curnode->getName() . ' but reading $name instead');
								}

							if (null != $this->curnode->getParent())
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

						if (null != $this->curnode->getParent())
							{
							$this->curnode = $this->curnode->getParent();
							}
						}
					}
				else
					{
					$datanode = new \ICalendarOrg\ZCiCalDataNode($line);

					if ('VEVENT' == $this->curnode->getName())
						{
						if ($eventcount < $maxevents && $eventpos >= $startevent)
							{
							if ('EXDATE' == $datanode->getName())
								{
								if (! \array_key_exists($datanode->getName(), $this->curnode->data))
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
								if (! \array_key_exists($datanode->getName(), $this->curnode->data))
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
						if ('EXDATE' == $datanode->getName())
							{
							if (! \array_key_exists($datanode->getName(), $this->curnode->data))
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
							if (! \array_key_exists($datanode->getName(), $this->curnode->data))
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
			$this->curnode = new \ICalendarOrg\ZCiCalNode($name, $this->curnode);
			$this->tree = $this->curnode;
			$datanode = new \ICalendarOrg\ZCiCalDataNode('VERSION:2.0');
			$this->curnode->data[$datanode->getName()] = $datanode;

			$datanode = new \ICalendarOrg\ZCiCalDataNode('PRODID:-//ZContent.net//ZapCalLib 1.0//EN');
			$this->curnode->data[$datanode->getName()] = $datanode;
			$datanode = new \ICalendarOrg\ZCiCalDataNode('CALSCALE:GREGORIAN');
			$this->curnode->data[$datanode->getName()] = $datanode;
			$datanode = new \ICalendarOrg\ZCiCalDataNode('METHOD:PUBLISH');
			$this->curnode->data[$datanode->getName()] = $datanode;
			}
		}

	/**
	 * CountEvents()
	 *
	 * Return the # of VEVENTs in the object
	 *
	 */
	public function countEvents() : int
		{
		$count = 0;

		if (isset($this->tree->child))
			{
			foreach ($this->tree->child as $child)
				{
				if ('VEVENT' == $child->getName())
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
	 */
	public function countVenues() : int
		{
		$count = 0;

		if (isset($this->tree->child))
			{
			foreach ($this->tree->child as $child)
				{
				if ('VVENUE' == $child->getName())
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
	public function export() : string
		{
		return $this->tree->export($this->tree);
		}

	/**
	 * Escape slashes, commas and semicolons in strings
	 *
	 *
	 */
	public static function formatContent(string $content) : string
		{
		$content = \str_replace(['\\', ',', ';'], ['\\\\', '\\,', '\\;'], $content);

		return $content;
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
		if (\count($thisnode->child) > 0)
			{
			return $thisnode->child[0];
			}


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

			while (! $event && null != $child)
				{
				if ('VEVENT' == $child->getName())
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

			while (! $event && null != $child)
				{
				if ('VVENUE' == $child->getName())
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
			} while (null != $event && 'VEVENT' != $event->getName());

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
			} while (null != $venue && 'VVENUE' != $venue->getName());

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

		if (null != $tnode)
			{
			$tzvalues['tzoffsetfrom'] = $tnode->getValues();
			}

		$tnode = @$node->data['TZOFFSETTO'];

		if (null != $tnode)
			{
			$tzvalues['tzoffsetto'] = $tnode->getValues();
			}

		$tnode = @$node->data['TZNAME'];

		if (null != $tnode)
			{
			$tzvalues['tzname'] = $tnode->getValues();
			}
		else
			{
			$tzvalues['tzname'] = '';
			}

		$tnode = @$node->data['DTSTART'];

		if (null != $tnode)
			{
			$tzvalues['dtstart'] = \ICalendarOrg\ZDateHelper::fromiCaltoUnixDateTime($tnode->getValues());
			}

		$tnode = @$node->data['RRULE'];

		if (null != $tnode)
			{
			$tzvalues['rrule'] = $tnode->getValues();
			}
		else
			{
			// no rule specified, let's create one from based on the date
			$date = \getdate($tzvalues['dtstart']);
			$month = $date['mon'];
			$day = $date['mday'];
			$tzvalues['rrule'] = 'FREQ=YEARLY;INTERVAL=1;BYMONTH=$month;BYMONTHDAY=$day';
			}

		return $tzvalues;
		}
	}
