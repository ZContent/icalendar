<?php
/**
 * recurringdate.php - create list of dates from recurring rule
 *
 * @package	ZapCalLib
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2018 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link	http://icalendar.org/php-library.html
 */

namespace ICalendarOrg;

/**
 * Zap Calendar Recurring Date Helper Class
 *
 * Class to expand recurring rule to a list of dates
 */
class ZCRecurringDate
	{
	/**
	 * @var array of repeat by day values
	 */
	public array $byday = [];

	/**
	 * @var array of repeat by hour values
	 */
	public array $byhour = [];

	/**
	 * @var array of repeat by minutes values
	 */
	public array $byminute = [];

	/**
	 * @var array of repeat by month values
	 */
	public array $bymonth = [];

	/**
	 * @var array of repeat by month day values
	 */
	public array $bymonthday = [];

	/**
	 * @var array of repeat by seconds values
	 */
	public array $bysecond = [];

	/**
	 * @var array of repeat by setpos values
	 */
	public array $bysetpos = [];

	/**
	 * @var array of repeat by year values
	 */
	public array $byyear = [];

	/**
	 * @var repeat count when repeat mode is 'c'
	 */
	public int $count = 0;

	/**
	 * @var debug level (for testing only)
	 */
	public int $debug = 0;

	/**
	 * @var error string (future use)
	 */
	public string $error;

	/**
	 * @var array of exception dates in Unix Timestamp format (UTC dates)
	 */
	public array $exdates = [];

	/**
	 * repeating frequency type (i.e. 'y' for yearly, 'm' for monthly)
	 *
	 * @var string
	 */
	public ?string $freq = null;

	/**
	 * inteval of repeating event (i.e. every 2 weeks, every 6 months)
	 *
	 */
	public int $interval = 1;

	/**
	 * repeat mode ('c': count, 'u': until)
	 *
	 * @var string
	 */
	public ?string $repeatmode = null;

	/**
	 * rules string
	 *
	 */
	public string $rules = '';

	/**
	 * start date in Unix Timestamp format (local timezone)
	 *
	 * @var int
	 */
	public ?int $startdate = null;

	/**
	 * timezone of event (using PHP timezones)
	 *
	 * @var string
	 */
	public ?string $tzid = null;

	/**
	 * repeat until date (in UTC Unix Timestamp format)
	 *
	 * @var int
	 */
	public ?int $until = null;

	/**
	 * Max year supported
	 *
	 */
	private int $maxYear;

	/**
	 * Expand recurring rule to a list of dates
	 *
	 * @param string $rules iCalendar rules string
	 * @param int $startdate start date in Unix Timestamp format
	 * @param array $exdates array of exception dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 */
	public function __construct(string $rules, int $startdate, array $exdates = [], string $tzid = 'UTC')
		{
		$this->maxYear = PHP_INT_SIZE == 4 ? 2036 : 9999;

		if (\strlen($rules) > 0)
			{
			//move exdates to event timezone for comparing with event date
			for ($i = 0; $i < \count($exdates); $i++)
				{
				$exdates[$i] = \ICalendarOrg\ZDateHelper::toUnixDateTime(\ICalendarOrg\ZDateHelper::toLocalDateTime(\ICalendarOrg\ZDateHelper::toSQLDateTime($exdates[$i]), $tzid));
				}

			$rules = \str_replace("\'", '', $rules);
			$this->rules = $rules;

			if (null == $startdate)
				{
				// if not specified, use start date of beginning of last year
				$tdate = \getdate();
				$startdate = \mktime(0, 0, 0, 1, 1, $tdate['year'] - 1);
				}
			$this->startdate = $startdate;
			$this->tzid = $tzid;
			$this->exdates = $exdates;

			$rules = \explode(';', $rules);
			$ruletype = '';

			foreach ($rules as $rule)
				{
				$item = \explode('=', $rule);

				switch ($item[0])
					{
					case 'FREQ':
						switch ($item[1])
							{
							case 'YEARLY':
								$this->freq = 'y';

								break;

							case 'MONTHLY':
								$this->freq = 'm';

								break;

							case 'WEEKLY':
								$this->freq = 'w';

								break;

							case 'DAILY':
								$this->freq = 'd';

								break;

							case 'HOURLY':
								$this->freq = 'h';

								break;

							case 'MINUTELY':
								$this->freq = 'i';

								break;

							case 'SECONDLY':
								$this->freq = 's';

								break;
							}

						break;

					case 'INTERVAL':
						$this->interval = $item[1];

						break;

					case 'BYSECOND':
						$this->bysecond = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'BYMINUTE':
						$this->byminute = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'BYHOUR':
						$this->byhour = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'BYDAY':
						$this->byday = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'BYMONTHDAY':
						$this->bymonthday = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'BYMONTH':
						$this->bymonth = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'BYYEAR':
						$this->byyear = \explode(',', $item[1]);
						$ruletype = $item[0];

						break;

					case 'COUNT':
						$this->count = (int)($item[1]);
						$this->repeatmode = 'c';

						break;

					case 'BYSETPOS':
						$this->bysetpos = \explode(',', $item[1]);

						break;

					case 'UNTIL':
						$this->until = \ICalendarOrg\ZDateHelper::fromiCaltoUnixDateTime($item[1]);
						$this->repeatmode = 'u';

						break;
					}
				}

			if (\count($this->bysetpos) > 0)
				{
				switch ($ruletype)
					{
					case 'BYYEAR':
						$this->byyear = $this->bySetPos($this->byyear, $this->bysetpos);

						break;

					case 'BYMONTH':
						$this->bymonth = $this->bySetPos($this->bymonth, $this->bysetpos);

						break;

					case 'BYMONTHDAY':
						$this->bymonthday = $this->bySetPos($this->bymonthday, $this->bysetpos);

						break;

					case 'BYDAY':
						$this->byday = $this->bySetPos($this->byday, $this->bysetpos);

						break;

					case 'BYHOUR':
						$this->byhour = $this->bySetPos($this->byhour, $this->bysetpos);

						break;

					case 'BYMINUTE':
						$this->byminute = $this->bySetPos($this->byminute, $this->bysetpos);

						break;

					case 'BYSECOND':
						$this->bysecond = $this->bySetPos($this->bysecond, $this->bysetpos);

						break;
					}
				}
			}
		}

	/**
	 * bysetpos rule support
	 */
	public function bySetPos(array $bytype, array $bysetpos) : array
		{
		$result = [];

		for ($i = 0; $i < \count($bysetpos); $i++)
			{
			for ($j = 0; $j < \count($bytype); $j++)
				{
				$result[] = $bysetpos[$i] . $bytype[$j];
				}
			}

		return $result;
		}

	/**
	 * display debug message
	 *
	 * @return ZCRecurringDate
	 */
	public function debug(int $level, string $msg) : self
		{
		if ($this->debug >= $level)
			{
			echo $msg . "<br/>\n";
			}

		return $this;
		}

	/**
	 * Get array of dates from recurring rule
	 *
	 * @param $maxdate integer maximum date to appear in repeating dates in Unix timestamp format
	 *
	 * @throws Exception
	 */
	public function getDates(?int $maxdate = null) : array
		{
		//$this->debug = 2;
		self::debug(1, 'getDates()');
		$nextdate = $enddate = $this->startdate;
		$rdates = [];
		$done = false;
		$eventcount = 0;
		$loopcount = 0;
		self::debug(2, 'freq: ' . $this->freq . ', interval: ' . $this->interval);

		while (! $done)
			{
			self::debug(1, "<b>*** Frequency ({$this->freq}) loop pass {$loopcount} ***</b>");

			switch ($this->freq)
				{
				case 'y':
					if ($eventcount > 0)
						{
						$nextdate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, 0, $this->interval, $this->tzid);
						self::debug(2, 'addDate() returned ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($nextdate));

						if (! empty($this->byday))
							{
							$t = \getdate($nextdate);
							$nextdate = \gmmktime($t['hours'], $t['minutes'], $t['seconds'], $t['mon'], 1, $t['year']);
							}
						self::debug(2, "nextdate set to {$nextdate} (" . \ICalendarOrg\ZDateHelper::toSQLDateTime($nextdate) . ')');
						}
					$enddate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, 0, 1);

					break;

				case 'm':
					if ($eventcount > 0)
						{
						$nextdate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, $this->interval, 0, 0, $this->tzid);
						self::debug(2, 'addDate() returned ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($nextdate));
						}

					if (\count($this->byday) > 0)
						{
						$t = \getdate($nextdate);

						if ($t['mday'] > 28)
							{
							//check for short months when using month by day, make sure we do not overshoot the counter and skip a month
							$nextdate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, $this->interval, 0, 0, $this->tzid);
							$t2 = \getdate($nextdate);

							if ($t2['mday'] < $t['mday'])
								{
								// oops, skipped a month, backup to previous month
								$nextdate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, $t2['mday'] - $t['mday'], 0, $this->tzid);
								}
							}
						$t = \getdate($nextdate);
						$nextdate = \mktime($t['hours'], $t['minutes'], $t['seconds'], $t['mon'], 1, $t['year']);
						}
					self::debug(2, "nextdate set to {$nextdate} (" . \ICalendarOrg\ZDateHelper::toSQLDateTime($nextdate) . ')');
					$enddate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, $this->interval, 0, 0);

					break;

				case 'w':
					if (0 == $eventcount)
						{
						$nextdate = $nextdate;
						}
					else
						{
						$nextdate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, $this->interval * 7, 0, $this->tzid);

						if (\count($this->byday) > 0)
							{
							$dow = \date('w', $nextdate);
							// move to beginning of week (Sunday)
							$bow = 0;
							$diff = $bow - $dow;

							if ($diff > 0)
								{
								$diff = $diff - 7;
								}
							$nextdate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, $diff, 0);
							}
						self::debug(2, "nextdate set to {$nextdate} (" . \ICalendarOrg\ZDateHelper::toSQLDateTime($nextdate) . ')');
						}
					$enddate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, $this->interval * 7, 0);

					break;

				case 'd':
					$nextdate = (0 == $eventcount ? $nextdate :
											 \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, $this->interval, 0, $this->tzid));
					$enddate = \ICalendarOrg\ZDateHelper::addDate($nextdate, 0, 0, 0, 0, 1, 0);

					break;
				}

			$count = $this->byYear($nextdate, $enddate, $rdates, $this->tzid);
			$eventcount += $count;

			if ($maxdate > 0 && $maxdate < $nextdate)
				{
				\array_pop($rdates);
				$done = true;
				}
			elseif (0 == $count && ! $this->maxDates($rdates))
				{
				$rdates[] = $nextdate;
				$eventcount++;
				}

			if ($this->maxDates($rdates))
				{
				$done = true;
				}

			$year = \date('Y', $nextdate);

			if ($year > $this->maxYear)
				{
				$done = true;
				}
			$loopcount++;

			if ($loopcount > $this->maxYear)
				{
				$done = true;

				throw new \Exception('Infinite loop detected in getDates()');
				}
			}

		if ('u' == $this->repeatmode && $rdates[\count($rdates) - 1] > $this->until)
			{
			// erase last item
			\array_pop($rdates);
			}
		$count1 = \count($rdates);
		$rdates = \array_unique($rdates);
		$count2 = \count($rdates);
		$dups = $count1 - $count2;
		$excount = 0;

		foreach ($this->exdates as $exdate)
			{
			if ($pos = \array_search($exdate, $rdates))
				{
				\array_splice($rdates, $pos, 1);
				$excount++;
				}
			}
		self::debug(1, 'getDates() returned ' . \count($rdates) . " dates, removing {$dups} duplicates, {$excount} exceptions");

		if ($this->debug >= 2)
			{
			self::debug(2, 'Recurring Dates:');

			foreach ($rdates as $rdate)
				{
				$d = \getdate($rdate);
				self::debug(2, \ICalendarOrg\ZDateHelper::toSQLDateTime($rdate) . ' ' . $d['wday']);
				}
			self::debug(2, 'Exception Dates:');

			foreach ($this->exdates as $exdate)
				{
				self::debug(2, \ICalendarOrg\ZDateHelper::toSQLDateTime($exdate));
				}
			}

		return $rdates;
		}

	/**
	 * get error message
	 *
	 * @return string error message
	 */
	public function getError() : string
		{
		return $this->error;
		}

	/**
	 * set debug level (0: none, 1: minimal, 2: more output)
	 *
	 *
	 * @return ZCRecurringDate
	 */
	public function setDebug(int $level) : self
		{
		$this->debug = $level;

		return $this;
		}

	/**
	 * save error
	 *
	 *
	 * @return ZCRecurringDate
	 */
	public function setError(string $msg) : self
		{
		$this->error = $msg;

		return $this;
		}

	/**
	 * Get repeating dates by day
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function byDay(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'byDay(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$days = [
			'SU' => 0,
			'MO' => 1,
			'TU' => 2,
			'WE' => 3,
			'TH' => 4,
			'FR' => 5,
			'SA' => 6];
		$idays = [
			0 => 'SU',
			1 => 'MO',
			2 => 'TU',
			3 => 'WE',
			4 => 'TH',
			5 => 'FR',
			6 => 'SA'];

		$count = 0;

		if (\count($this->byday) > 0)
			{
			if (empty($this->byday[0]))
				{
				$this->byday[0] = $idays[\date('w', $startdate)];
				}

			foreach ($this->byday as $tday)
				{
				$t = \getdate($startdate);
				$day = \substr($tday, \strlen($tday) - 2);

				if (\strlen($day) < 2)
					{
					// missing start day, use current date for DOW
					$day = $idays[\date('w', $startdate)];
					}

				if (\strlen($tday) > 2)
					{
					$imin = 1;
					$imax = 5; // max # of occurances in a month

					if (\strlen($tday) > 2)
						{
						$imin = $imax = \substr($tday, 0, \strlen($tday) - 2);
						}
					self::debug(2, "imin: {$imin}, imax: {$imax}, tday: {$tday}, day: {$day}, daynum: {$days[$day]}");

					for ($i = $imin; $i <= $imax; $i++)
						{
						$wdate = \ICalendarOrg\ZDateHelper::getDateFromDay($startdate, $i - 1, $days[$day], $tzid);
						self::debug(2, 'getDateFromDay(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate)
												. ",{$i},{$days[$day]}) returned " . \ICalendarOrg\ZDateHelper::toSQLDateTime($wdate));

						if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
							{
							$count = $this->byHour($wdate, $enddate, $rdates);

							if (0 == $count)
								{
								$rdates[] = $wdate;
								$count++;
								//break;
								}
							}
						}
					}
				else
					{
					// day of week version
					$startdate_dow = \date('w', $startdate);
					$datedelta = $days[$day] - $startdate_dow;
					self::debug(2, "start_dow: {$startdate_dow}, datedelta: {$datedelta}");

					if ($datedelta >= 0)
						{
						$wdate = \ICalendarOrg\ZDateHelper::addDate($startdate, 0, 0, 0, 0, $datedelta, 0, $this->tzid);
						self::debug(2, 'wdate: ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($wdate));

						if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
							{
							$count = $this->byHour($wdate, $enddate, $rdates);

							if (0 == $count)
								{
								$rdates[] = $wdate;
								$count++;
								self::debug(2, 'adding date ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($wdate));
								}
							}
						}
					}
				}
			}
		elseif (! $this->maxDates($rdates))
			{
			$count = $this->byHour($startdate, $enddate, $rdates);
			}
		self::debug(1, 'byDay() returned ' . $count);

		return $count;
		}

	/**
	 * Get repeating dates by hour
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function byHour(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'byHour(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$count = 0;

		if (\count($this->byhour) > 0)
			{
			foreach ($this->byhour as $hour)
				{
				$t = \getdate($startdate);
				$wdate = \mktime($hour, $t['minutes'], $t['seconds'], $t['mon'], $t['mday'], $t['year']);
				self::debug(2, 'checking date/time ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($wdate));

				if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
					{
					$count = $this->byMinute($wdate, $enddate, $rdates);

					if (0 == $count)
						{
						$rdates[] = $wdate;
						$count++;
						}
					}
				}
			}
		elseif (! $this->maxDates($rdates))
			{
			$count = $this->byMinute($startdate, $enddate, $rdates);
			}
		self::debug(1, 'byHour() returned ' . $count);

		return $count;
		}

	/**
	 * Get repeating dates by minute
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function byMinute(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'byMinute(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$count = 0;

		if (\count($this->byminute) > 0)
			{
			foreach ($this->byminute as $minute)
				{
				$t = \getdate($startdate);
				$wdate = \mktime($t['hours'], $minute, $t['seconds'], $t['mon'], $t['mday'], $t['year']);

				if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
					{
					$count = $this->bySecond($wdate, $enddate, $rdates);

					if (0 == $count)
						{
						$rdates[] = $wdate;
						$count++;
						}
					}
				}
			}
		elseif (! $this->maxDates($rdates))
			{
			$count = $this->bySecond($startdate, $enddate, $rdates);
			}
		self::debug(1, 'byMinute() returned ' . $count);

		return $count;
		}

	/**
	 * Get repeating dates by month
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function byMonth(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'byMonth(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$count = 0;

		if (\count($this->bymonth) > 0)
			{
			foreach ($this->bymonth as $month)
				{
				$t = \getdate($startdate);
				$wdate = \mktime($t['hours'], $t['minutes'], $t['seconds'], $month, $t['mday'], $t['year']);

				if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
					{
					$count = $this->byMonthDay($wdate, $enddate, $rdates, $tzid);

					if (0 == $count)
						{
						$rdates[] = $wdate;
						$count++;
						}
					}
				}
			}
		elseif (! $this->maxDates($rdates))
			{
			$count = $this->byMonthDay($startdate, $enddate, $rdates, $tzid);
			}
		self::debug(1, 'byMonth() returned ' . $count);

		return $count;
		}

	/**
	 * Get repeating dates by month day
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function byMonthDay(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'byMonthDay(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$count = 0;
		self::debug(1, 'start date: ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate));

		if (\count($this->bymonthday) > 0)
			{
			foreach ($this->bymonthday as $day)
				{
				$day = (int)$day;
				$t = \getdate($startdate);
				$wdate = \mktime($t['hours'], $t['minutes'], $t['seconds'], $t['mon'], $day, $t['year']);
				self::debug(2, 'mktime(' . $t['hours'] . ', ' . $t['minutes']
										. ', ' . $t['mon'] . ', ' . $day . ', ' . $t['year'] . ') returned $wdate');

				if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
					{
					$count = $this->byDay($wdate, $enddate, $rdates, $tzid);

					if (0 == $count)
						{
						$rdates[] = $wdate;
						$count++;
						}
					}
				}
			}
		elseif (! $this->maxDates($rdates))
			{
			self::debug(1, 'start date: ' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate));
			$count = $this->byDay($startdate, $enddate, $rdates, $tzid);
			}
		self::debug(1, 'byMonthDay() returned ' . $count);

		return $count;
		}

	/**
	 * Get repeating dates by second
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function bySecond(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'bySecond(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$count = 0;

		if (\count($this->bysecond) > 0)
			{
			foreach ($this->bysecond as $second)
				{
				$t = \getdate($startdate);
				$wdate = \mktime($t['hours'], $t['minutes'], $second, $t['mon'], $t['mday'], $t['year']);

				if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
					{
					$rdates[] = $wdate;
					$count++;
					}
				}
			}
		self::debug(1, 'bySecond() returned ' . $count);

		return $count;
		}

	/**
	 * Get repeating dates by year
	 *
	 * @param int $startdate start date of repeating events, in Unix timestamp format
	 * @param int $enddate end date of repeating events, in Unix timestamp format
	 * @param array $rdates array to contain expanded repeating dates
	 * @param string $tzid timezone of event (using PHP timezones)
	 *
	 * @return int count of dates
	 */
	private function byYear(int $startdate, int $enddate, array & $rdates, string $tzid = 'UTC') : int
		{
		self::debug(1, 'byYear(' . \ICalendarOrg\ZDateHelper::toSQLDateTime($startdate) . ','
								. \ICalendarOrg\ZDateHelper::toSQLDateTime($enddate) . ',' . \count($rdates) . ' dates)');
		$count = 0;

		if (\count($this->byyear) > 0)
			{
			foreach ($this->byyear as $year)
				{
				$t = \getdate($startdate);
				$wdate = \mktime($t['hours'], $t['minutes'], $t['seconds'], $t['month'], $t['mday'], $year);

				if ($startdate <= $wdate && $wdate < $enddate && ! $this->maxDates($rdates))
					{
					$count = $this->byMonth($wdate, $enddate, $rdates, $tzid);

					if (0 == $count)
						{
						$rdates[] = $wdate;
						$count++;
						}
					}
				}
			}
		elseif (! $this->maxDates($rdates))
			{
			$count = $this->byMonth($startdate, $enddate, $rdates, $tzid);
			}
		self::debug(1, 'byYear() returned ' . $count);

		return $count;
		}

	/**
	 * Determine if the loop has reached the end date
	 *
	 * @param array $rdates array of repeating dates
	 *
	 */
	private function maxDates(array $rdates) : bool
		{
		if ('c' == $this->repeatmode && \count($rdates) >= $this->count)
			{
			return true; // exceeded count
			}

		//past date
		return (bool)(\count($rdates) > 0 && 'u' == $this->repeatmode && $rdates[\count($rdates) - 1] > $this->until);
		}
	}
