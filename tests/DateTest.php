<?php

/**
 * This file is part of the ICalendarOrg package
 *
 * (c) Bruce Wells
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source
 * code
 *
 */

use ICalendarOrg\ZDateHelper;

class DateTest extends \PHPUnit\Framework\TestCase
	{

////	public static function addDate($date, $hour, $min, $sec, $month, $day, $year, $tzid = 'UTC')
//	public function testAddDate() : void
//		{
//		date_default_timezone_set($tzid);
//		$sqldate = self::toSQLDateTime($date);
//		$tdate = [];
//		$tdate['year'] = substr($sqldate, 0, 4);
//		$tdate['mon'] = substr($sqldate, 5, 2);
//		$tdate['mday'] = substr($sqldate, 8, 2);
//		$tdate['hours'] = substr($sqldate, 11, 2);
//		$tdate['minutes'] = substr($sqldate, 14, 2);
//		$tdate['seconds'] = substr($sqldate, 17, 2);
//		$newdate = mktime($tdate['hours'] + $hour, $tdate['minutes'] + $min, $tdate['seconds'] + $sec, $tdate['mon'] + $month, $tdate['mday'] + $day, $tdate['year'] + $year);
//		date_default_timezone_set('UTC');
//		//echo self::toSQLDateTime($date) . ' => ' . self::toSQLDateTime($newdate) . " ($hour:$min:$sec $month/$day/$year)<br/>\n";
//		return $newdate;
//		}

	public function testDayInMonth() : void
		{
		$this->assertEquals(31, ZDateHelper::dayInMonth(1, 2019));
		$this->assertEquals(28, ZDateHelper::dayInMonth(2, 2019));
		$this->assertEquals(29, ZDateHelper::dayInMonth(2, 2020));
		$this->assertEquals(31, ZDateHelper::dayInMonth(3, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(4, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(5, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(6, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(7, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(8, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(9, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(10, 2019));
		$this->assertEquals(30, ZDateHelper::dayInMonth(11, 2019));
		$this->assertEquals(31, ZDateHelper::dayInMonth(12, 2019));
		}

	public function testFromiCaltoUnixDateTime() : void
		{
		$this->assertEquals(strtotime('2019-1-1'), ZDateHelper::fromiCaltoUnixDateTime('20190101'));
		$this->assertEquals(strtotime('2019-1-1 01:02:03'), ZDateHelper::fromiCaltoUnixDateTime('20190101T010203'));
		$this->assertEquals(strtotime('2019-1-1 01:02:03'), ZDateHelper::fromiCaltoUnixDateTime('20190101T010203Z'));
		$this->assertEquals(strtotime('2020-3-1 01:02:03'), ZDateHelper::fromiCaltoUnixDateTime('20200301T010203Z'));
		}

	public function testFromUnixDateTimetoiCal() : void
		{
		$this->assertEquals('20190101T010203', ZDateHelper::fromUnixDateTimetoiCal(strtotime('2019-1-1 01:02:03')));
		}

//	/**
//	 * Convert from a relative date to an absolute date
//	 *
//	 * Examples of relative dates are '-2y' for 2 years ago, '18m'
//	 * for 18 months after today. Relative date uses 'y', 'm' and 'd' for
//	 * year, month and day. Relative date can be combined into comma
//	 * separated list, i.e., '-1y,-1d' for 1 year and 1 day ago.
//	 *
//	 * @param string $date relative date string (i.e. '1y' for 1 year from today)
//	 * @param string $rdate reference date, or blank for current date (in SQL date-time format)
//	 *
//	 * @return string in SQL date-time format
//	 */
////	public static function getAbsDate($date, $rdate = '')

	public function testGetAbsDate() : void
		{
//			return gmdate('Y-m-d H:i:s', self::now());

//		$this->assertEquals('2019-01-01 01:02:03', ZDateHelper::getAbsDate('+1d,+1h', '2018-12-31 01:02:03'));
//		if (str_replace(['y', 'm', 'd', 'h', 'n'], '', strtolower($date)) != strtolower($date))
//			{
//			date_default_timezone_set('UTC');
//			if ($rdate == '')
//				{
//				$udate = time();
//				}
//			else
//				{
//				$udate = self::toUnixDateTime($rdate);
//				}
//			$values = explode(',', strtolower($date));
//			$y = 0;
//			$m = 0;
//			$d = 0;
//			$h = 0;
//			$n = 0;
//			foreach ($values as $value)
//				{
//				$rtype = substr($value, strlen($value) - 1);
//				$rvalue = intval(substr($value, 0, strlen($value) - 1));
//				switch ($rtype)
//					{
//					case 'y':
//						$y = $rvalue;
//						break;
//					case 'm':
//						$m = $rvalue;
//						break;
//					case 'd':
//						$d = $rvalue;
//						break;
//					case 'h':
//						$h = $rvalue;
//						break;
//					case 'n':
//						$n = $rvalue;
//						break;
//					}
//				// for '-' values, move to start of day , otherwise, move to end of day
//				if ($rvalue[0] == '-')
//					{
//					$udate = mktime(0, 0, 0, date('m', $udate), date('d', $udate), date('Y', $udate));
//					}
//				else
//					{
//					$udate = mktime(0, -1, 0, date('m', $udate), date('d', $udate) + 1, date('Y', $udate));
//					}
//				$udate = self::addDate($udate, $h, $n, 0, $m, $d, $y);
//				}
//			$date = self::toSQLDateTime($udate);
//			}
//
//		return $date;
		}
//
//	/**
//	 * Date math: get date from week and day in specifiec month
//	 *
//	 * This routine finds actual dates for the second Tuesday of the month, last Friday of the month, etc.
//	 * For second Tuesday, use $week = 1, $wday = 2
//	 * for last Friday, use $week = -1, $wday = 5
//	 *
//	 * @param int $date Unix timestamp
//	 * @param int $week week number, 0 is first week, -1 is last
//	 * @param int $wday day of week, 0 is Sunday, 6 is Saturday
//	 * @param string $tzid PHP supported timezone
//	 *
//	 * @return int Unix timestamp
//	 */
////	public static function getDateFromDay($date, $week, $wday, $tzid = 'UTC')
//	public function testGetDateFromDay() : void
//		{
//		//echo 'getDateFromDay(' . self::toSQLDateTime($date) . ",$week,$wday)<br/>\n";
//		// determine first day in month
//		$tdate = getdate($date);
//		$monthbegin = gmmktime(0, 0, 0, $tdate['mon'], 1, $tdate['year']);
//		$monthend = self::addDate($monthbegin, 0, 0, 0, 1, -1, 0, $tzid); // add 1 month and subtract 1 day
//		$day = self::addDate($date, 0, 0, 0, 0, 1 - $tdate['mday'], 0, $tzid);
//		$month = [[]];
//		while ($day <= $monthend)
//			{
//			$tdate = getdate($day);
//			$month[$tdate['wday']][] = $day;
//			//echo self::toSQLDateTime($day) . "<br/>\n";
//			$day = self::addDate($day, 0, 0, 0, 0, 1, 0, $tzid); // add 1 day
//			}
//		$dayinmonth = 0;
//		if ($week >= 0)
//			{
//			$dayinmonth = $month[$wday][$week];
//			}
//		else
//			{
//			$dayinmonth = $month[$wday][count($month[$wday]) - 1];
//			}
//		//echo 'return ' . self::toSQLDateTime($dayinmonth);
//		//exit;
//		return $dayinmonth;
//		}
//
//	/**
//	 * Convert iCal duration string to # of seconds
//	 *
//	 * @param string $duration iCal duration string
//	 *
//	 * @return int
//	 */
////	public static function iCalDurationtoSeconds($duration)
//	public function testICalDurationtoSeconds() : void
//		{
//		$secs = 0;
//		if ($duration{0} == 'P')
//			{
//			$duration = str_replace(['H', 'M', 'S', 'T', 'D', 'W', 'P'], ['H,', 'M,', 'S,', '', 'D,', 'W,', ''], $duration);
//			$dur2 = explode(',', $duration);
//			foreach ($dur2 as $dur)
//				{
//				$val = intval($dur);
//				if (strlen($dur) > 0)
//					{
//					switch ($dur{strlen($dur) - 1})
//						{
//						case 'H':
//							$secs += 60 * 60 * $val;
//							break;
//						case 'M':
//							$secs += 60 * $val;
//							break;
//						case 'S':
//							$secs += $val;
//							break;
//						case 'D':
//							$secs += 60 * 60 * 24 * $val;
//							break;
//						case 'W':
//							$secs += 60 * 60 * 24 * 7 * $val;
//							break;
//						}
//					}
//				}
//			}
//
//		return $secs;
//		}
//
//	/**
//	 * Check if day falls within date range
//	 *
//	 * @param int $daystart start of day in Unix timestamp format
//	 * @param int $begin Unix timestamp of starting date range
//	 * @param int $end Unix timestamp of end date range
//	 *
//	 * @return bool
//	 */
////	public static function inDay($daystart, $begin, $end)
//	public function testInDay() : void
//		{
//		//$dayend = $daystart + 60*60*24 - 60;
//		// add 1 day to determine end of day
//		// don't use 24 hours, since twice a year DST Sundays are 23 hours and 25 hours in length
//		// adding 1 day takes this into account
//		$dayend = self::addDate($daystart, 0, 0, 0, 0, 1, 0);
//
//		$end = max($begin, $end); // $end can't be less than $begin
//		$inday =
//				($daystart <= $begin && $begin < $dayend)
//				|| ($daystart < $end && $end < $dayend)
//				|| ($begin <= $daystart && $end > $dayend)
//		;
//
//		return $inday;
//		}
//
//	/**
//	 * Is given date after today?
//	 *
//	 * @param int $date date in Unix timestamp format
//	 * @param string $tzid PHP recognized timezone (default is UTC)
//	 *
//	 * @return bool
//	 */
////	public static function isAfterToday($date, $tzid = 'UTC')
//	public function testIsAfterToday() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return mktime(0, 0, 0, date('m', $now), date('d', $now), date('Y', $now)) <
//							 mktime(0, 0, 0, date('m', $date), date('d', $date), date('Y', $now));
//		}
//
//	/**
//	 * Is given date before today?
//	 *
//	 * @param int $date date in Unix timestamp format
//	 * @param string $tzid PHP recognized timezone (default is UTC)
//	 *
//	 * @return bool
//	 */
////	public static function isBeforeToday($date, $tzid = 'UTC')
//	public function testIsBeforeToday() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return mktime(0, 0, 0, date('m', $now), date('d', $now), date('Y', $now)) >
//							 mktime(0, 0, 0, date('m', $date), date('d', $date), date('Y', $now));
//		}
//
//	/**
//	 * Is given date in the future?
//	 *
//	 * This routine differs from isAfterToday() in that isFuture() will
//	 * return true for date-time values later in the same day.
//	 *
//	 * @param int $date date in Unix timestamp format
//	 * @param string $tzid PHP recognized timezone (default is UTC)
//	 *
//	 * @return bool
//	 */
////	public static function isFuture($date, $tzid = 'UTC')
//	public function testIsFuture() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return $date > $now;
//		}
//
//	/**
//	 * Is given date in the past?
//	 *
//	 * This routine differs from isBeforeToday() in that isPast() will
//	 * return true for date-time values earlier in the same day.
//	 *
//	 * @param int $date date in Unix timestamp format
//	 * @param string $tzid PHP recognized timezone (default is UTC)
//	 *
//	 * @return bool
//	 */
////	public static function isPast($date, $tzid = 'UTC')
//	public function testIsPast() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return $date < $now;
//		}
//
//	/**
//	 * Is given date today?
//	 *
//	 * @param int $date date in Unix timestamp format
//	 * @param string $tzid PHP recognized timezone (default is UTC)
//	 *
//	 * @return bool
//	 */
////	public static function isToday($date, $tzid = 'UTC')
//	public function testIsToday() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return gmdate('Y-m-d', $date) == gmdate('Y-m-d', $now);
//		}
//
//	/**
//	 * Is given date tomorrow?
//	 *
//	 * @param int $date date in Unix timestamp format
//	 * @param string $tzid PHP recognized timezone (default is UTC)
//	 *
//	 * @return bool
//	 */
////	public static function isTomorrow($date, $tzid = 'UTC')
//	public function testIsTomorrow() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return gmdate('Y-m-d', $date) == gmdate('Y-m-d', $now + 60 * 60 * 24);
//		}
//
//	/**
//	 * Is given date fall on a weekend?
//	 *
//	 * @param int $date Unix timestamp
//	 *
//	 * @return bool
//	 */
////	public static function isWeekend($date)
//	public function testIsWeekend() : void
//		{
//		$dow = gmdate('w', $date);
//
//		return $dow == 0 || $dow == 6;
//		}
//
//	/**
//	 * Return current Unix timestamp in local timezone
//	 *
//	 * @param string $tzid PHP recognized timezone
//	 *
//	 * @return int
//	 */
////	public static function now($tzid = 'UTC')
//	public function testNow() : void
//		{
//		$dtz = new \DateTimeZone($tzid);
//		$dt = new \DateTime('now', $dtz);
//		$now = time() + $dtz->getOffset($dt);
//
//		return $now;
//		}
//
//	/**
//	 * Format Unix timestamp to iCal date format
//	 *
//	 * @param int $datetime Unix timestamp
//	 *
//	 * @return string iCal date-time string
//	 */
////	public static function toiCalDate($datetime = null)
//	public function testToiCalDate() : void
//		{
//		date_default_timezone_set('UTC');
//		if ($datetime == null)
//			{
//			$datetime = time();
//			}
//
//		return gmdate('Ymd', $datetime);
//		}
//
//	/**
//	 * Format Unix timestamp to iCal date-time format
//	 *
//	 * @param int $datetime Unix timestamp
//	 *
//	 * @return string iCal date-time string
//	 */
////	public static function toiCalDateTime($datetime = null)
//	public function testToiCalDateTime() : void
//		{
//		date_default_timezone_set('UTC');
//		if ($datetime == null)
//			{
//			$datetime = time();
//			}
//
//		return gmdate("Ymd\THis", $datetime);
//		}
//
//	/**
//	 * Convert UTC date-time to local date-time
//	 *
//	 * @param string $sqldate SQL date-time string
//	 * @param string $tzid PHP recognized timezone (default is 'UTC')
//	 *
//	 * @return string SQL date-time string
//	 */
////	public static function toLocalDateTime($sqldate, $tzid = 'UTC')
//	public function testToLocalDateTime() : void
//		{
//		try
//			{
//			$timezone = new \DateTimeZone($tzid);
//			}
//		catch (\Exception $e)
//			{
//			// bad time zone specified
//			return $sqldate;
//			}
//		$udate = self::toUnixDateTime($sqldate);
//		$daydatetime = new \DateTime('@' . $udate);
//		$tzoffset = $timezone->getOffset($daydatetime);
//
//		return self::toSQLDateTime($udate + $tzoffset);
//		}
//
//	/**
//	 * Format Unix timestamp to SQL date
//	 *
//	 * @param int $t Unix timestamp
//	 *
//	 * @return string
//	 */
////	public static function toSQLDate($t = 0)
//	public function testToSQLDate() : void
//		{
//		date_default_timezone_set('GMT');
//		if ($t == 0)
//			{
//			return gmdate('Y-m-d', self::now());
//			}
//
//		return gmdate('Y-m-d', $t);
//		}
//
//	/**
//	 * Format Unix timestamp to SQL date-time
//	 *
//	 * @param int $t Unix timestamp
//	 *
//	 * @return string
//	 */
////	public static function toSQLDateTime($t = 0)
//	public function testToSQLDateTime() : void
//		{
//		date_default_timezone_set('GMT');
//		if ($t == 0)
//			{
//			return gmdate('Y-m-d H:i:s', self::now());
//			}
//
//		return gmdate('Y-m-d H:i:s', $t);
//		}
//
//	/**
//	 * Convert SQL date or date-time to Unix timestamp
//	 *
//	 * @param string $datetime SQL date or date-time
//	 *
//	 * @return int Unix date-time timestamp
//	 */
////	public static function toUnixDate($datetime)
//	public function testToUnixDate() : void
//		{
//		$year = substr($datetime, 0, 4);
//		$month = substr($datetime, 5, 2);
//		$day = substr($datetime, 8, 2);
//
//		return mktime(0, 0, 0, $month, $day, $year);
//		}
//
//	/**
//	 * Convert SQL date or date-time to Unix date timestamp
//	 *
//	 * @param string $datetime SQL date or date-time
//	 *
//	 * @return int Unix timestamp
//	 */
////	public static function toUnixDateTime($datetime)
//	public function testToUnixDateTime() : void
//		{
//		// convert to absolute dates if neccessary
//		$datetime = self::getAbsDate($datetime);
//		$year = substr($datetime, 0, 4);
//		$month = substr($datetime, 5, 2);
//		$day = substr($datetime, 8, 2);
//		$hour = 0;
//		$minute = 0;
//		$second = 0;
//		if (strlen($datetime) > 10)
//			{
//			$hour = substr($datetime, 11, 2);
//			$minute = substr($datetime, 14, 2);
//			$second = substr($datetime, 17, 2);
//			}
//
//		return gmmktime($hour, $minute, $second, $month, $day, $year);
//		}
//
//	/**
//	 * Convert local date-time to UTC date-time
//	 *
//	 * @param string $sqldate SQL date-time string
//	 * @param string $tzid PHP recognized timezone (default is 'UTC')
//	 *
//	 * @return string SQL date-time string
//	 */
////	public static function toUTCDateTime($sqldate, $tzid = 'UTC')
//	public function testToUTCDateTime() : void
//		{
//		date_default_timezone_set('UTC');
//		try
//			{
//			$date = new \DateTime($sqldate, $tzid);
//			}
//		catch (\Exception $e)
//			{
//			// bad time zone specified
//			return $sqldate;
//			}
//		$offset = $date->getOffsetFromGMT();
//		if ($offset >= 0)
//			{
//			$date->sub(new \DateInterval('PT' . $offset . 'S'));
//			}
//		else
//			{
//			$date->add(new \DateInterval('PT' . abs($offset) . 'S'));
//			}
//
//		return $date->toSQL(true);
//		}
	}

