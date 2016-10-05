<?php

/**
 * @package	 Zap Calendar Date Helper Class
 *
 * @copyright   Copyright (C) 2006 - 2016 by Dan Cogliano
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_ZAPCAL') or die( 'Restricted access' );

class ZDateHelper {

	/*
	 * Find the number of days in a month
	 * Year is between 1 and 32767 inclusive
	 * Month is between 1 and 12 inclusive
	 */
	static function DayInMonth($month, $year) {
	   $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	   if ($month != 2) return $daysInMonth[$month - 1];
	   return (checkdate($month, 29, $year)) ? 29 : 28;
	}

	static function isToday($date, $tzid = "UTC") {
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return gmdate('Y-m-d', $date) == gmdate('Y-m-d', $now);
	}

	static function isBeforeToday($date, $tzid = "UTC"){
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now)) >
			mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$now));
	}

	static function isAfterToday($date, $tzid = "UTC"){
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return mktime(0,0,0,date('m',$now),date('d',$now),date('Y',$now)) <
			mktime(0,0,0,date('m',$date),date('d',$date),date('Y',$now));
	}

	static function isTomorrow($date, $tzid = "UTC") {
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return gmdate('Y-m-d', $date) == gmdate('Y-m-d', $now + 60 * 60 * 24);
	}

	static function isFuture($date, $tzid = "UTC"){
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return $date > $now;
	}

	static function isPast($date, $tzid = "UTC") {
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return $date < $now;
	}

	static function now($tzid = "UTC"){
		$dtz = new DateTimeZone($tzid);
		$dt = new DateTime("now", $dtz);
		$now = time() + $dtz->getOffset($dt);
		return $now;
	}

	static function isWeekend($date) {
		$dow = gmdate('w',$date);
		return $dow == 0 || $dow == 6;
	}

	static function toSqlDateTime($t = 0)
	{
		date_default_timezone_set('GMT');
		if($t == 0)
			return gmdate('Y-m-d H:i:s',self::now());
		return gmdate('Y-m-d H:i:s', $t);
	}

	static function toSqlDate($t = 0)
	{
		date_default_timezone_set('GMT');
		if($t == 0)
			return gmdate('Y-m-d',self::now());
		return gmdate('Y-m-d', $t);
	}

	// $datetime is in iCal time format( YYYYMMDD or YYYYMMDDTHHMMSS or YYYYMMDDTHHMMSSZ )
	static function fromiCaltoUnixDateTime($datetime) {
			// first check format
			$formats = array();
			$formats[] = "/[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]/";
			$formats[] = "/[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]T[0-9][0-9][0-9][0-9][0-9][0-9]/";
			$formats[] = "/[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]T[0-9][0-9][0-9][0-9][0-9][0-9]Z/";
			$ok = false;
			foreach($formats as $format){
				if(preg_match($format,$datetime)){
					$ok = true;
					break;
				}
			}
			if(!$ok)
				return null;
			$year = substr($datetime,0,4);
			$month = substr($datetime,4,2);
			$day = substr($datetime,6,2);
			$hour = 0;
			$minute = 0;
			$second = 0;
			if(strlen($datetime) > 8 && $datetime{8} == "T") {
				$hour = substr($datetime,9,2);
				$minute = substr($datetime,11,2);
				$second = substr($datetime,13,2);
			}
			return gmmktime($hour, $minute, $second, $month, $day, $year);
	}

	static function fromUnixDateTimetoiCal($datetime){
		date_default_timezone_set('GMT');
		return gmdate("Ymd\THis",$datetime);
	}

	// convert iCal duration string to # of seconds
	static function iCalDurationtoSeconds($duration) {
		$secs = 0;
		if($duration{0} == "P") {
			$duration = str_replace(array("H","M","S","T","D","W","P"),array("H,","M,","S,","","D,","W,",""),$duration);
			$dur2 = explode(",",$duration);
			foreach($dur2 as $dur){
				$val=intval($dur);
				if(strlen($dur) > 0){
					switch($dur{strlen($dur) - 1}) {
						case "H":
							$secs += 60*60 * $val;
							break;
						case "M":
							$secs += 60 * $val;
							break;
						case "S":
							$secs += $val;
							break;
						case "D":
							$secs += 60*60*24 * $val;
							break;
						case "W":
							$secs += 60*60*24*7 * $val;
							break;
					}
				}
			}
		}
		return $secs;
	}

	// see if date range (begin-end) falls on day
	static function inDay($daystart, $begin, $end)
	{
		//$dayend = $daystart + 60*60*24 - 60;
		// add 1 day to determine end of day
		// don't use 24 hours, since twice a year DST Sundays are 23 hours and 25 hours in length
		// adding 1 day takes this into account
		$dayend = self::addDate($daystart, 0,0,0,0,1,0);

		$end = max($begin, $end); // $end can't be less than $begin
		$inday = 
			($daystart <= $begin && $begin < $dayend)
			||($daystart < $end && $end < $dayend)
			||($begin <= $daystart && $end > $dayend)
			;
		return $inday;
	}

	// see if event falls in date range 
	// event: event object
	// begin, end: range in Unix timestamp format (UTC)
	// tzid: timezone of calendar, or null (will use event if not specified)
	static function inRange($event, $begin, $end, $tzid = null)
	{
		$eventstart = self::toUnixDateTime($event->event_start);
		$eventend = self::toUnixDateTime($event->event_end);
		/*
		echo "inRange(" .
			self::toSQLDateTime($eventstart) . " - " .
			self::toSQLDateTime($eventend) . ", " .
			self::toSQLDateTime($begin) . ", " .
			self::toSQLDateTime($end) . ", $tzid)<br/>\n";
		*/
		if(empty($tzid))
			$tzid = $event->tzid;
		if($event->event_type == 1)
			$tzid = ""; // no timezone for all day events

		$end = max($begin, $end); // $end can't be less than $begin

		if($event->event_type == 2)
		{
			// convert meetings to local time zone
			$eventstart = self::toUnixDateTime(self::toLocalDateTime($event->event_start,$tzid));
			$eventend = self::toUnixDateTime(self::toLocalDateTime($event->event_end,$tzid));
 		}
		/*
		$inrange = 
			($eventstart <= $begin && $begin < $eventend)
			||($eventstart < $end && $end <= $eventend)
			||($begin <= $eventstart && $end > $eventend)
			;
		*/
		$inrange = 
			($eventstart <= $begin && $begin < $eventend)
			||($eventstart < $end && $end <= $eventend)
			||($eventstart <= $begin && $end <= $eventend)
			||($begin <= $eventstart && $end > $eventend)
			;
		//echo "inRange() returns " . ($inrange? "true" : "false") . "<br/>\n";
		return $inrange;
	}

	// $datetime is in SQL date or date/time format
	static function toUnixDate($datetime)
	{
			$datetime = self::convertDate($datetime);
			$year = substr($datetime,0,4);
			$month = substr($datetime,5,2);
			$day = substr($datetime,8,2);

			return mktime(0, 0, 0, $month, $day, $year);
	}

	// $datetime is in SQL date format
	static function toUnixDateTime($datetime)
	{
		// convert to absolute dates if neccessary
		//$datetime = self::convertDate(self::getAbsDate($datetime));
		$datetime = self::getAbsDate($datetime);
		$year = substr($datetime,0,4);
		$month = substr($datetime,5,2);
		$day = substr($datetime,8,2);
		$hour = 0;
		$minute = 0;
		$second = 0;
		if(strlen($datetime) > 10) {
			$hour = substr($datetime,11,2);
			$minute = substr($datetime,14,2);
			$second = substr($datetime,17,2);
		}
		return gmmktime($hour, $minute, $second, $month, $day, $year);
	}

	//date math: add to current date to get new date
	static function addDate($date, $hour, $min, $sec, $month, $day, $year, $tzid = "UTC") {
		if($tzid == -1) // old param
			$tzid = "UTC";
		date_default_timezone_set($tzid);
		//date_default_timezone_set("UTC");
		//$tdate=getdate($date);
		//print_r($tdate);
		$sqldate = self::toSQLDateTime($date);
		$tdate = array();
		$tdate["year"] = substr($sqldate,0,4);
		$tdate["mon"] = substr($sqldate,5,2);
		$tdate["mday"] = substr($sqldate,8,2);
		$tdate["hours"] = substr($sqldate,11,2);
		$tdate["minutes"] = substr($sqldate,14,2);
		$tdate["seconds"] = substr($sqldate,17,2);
		$newdate=mktime($tdate["hours"] + $hour, $tdate["minutes"] + $min, $tdate["seconds"] + $sec, $tdate["mon"] + $month, $tdate["mday"] + $day, $tdate["year"] + $year);
		date_default_timezone_set("UTC");
		//echo self::toSQLDateTime($date) . " => " . self::toSQLDateTime($newdate) . " ($hour:$min:$sec $month/$day/$year)<br/>\n";
		return $newdate;
	}

	// date math: get date from week and day in specified month, i.e. second Tuesday, last Friday
	// params:
	// date - Unix datetime
	// week - week number, 0 is first , -1 is last
	// wday - day of week, like Unix, 0 is Sunday .. 6 is Saturday
	// second Tuesday is 1,2 , last Friday is -1,5
	static function getDateFromDay($date, $week, $wday,$tzid="UTC") {
		//echo "getDateFromDay(" . self::toSqlDateTime($date) . ",$week,$wday)<br/>\n";
		// determine first day in month
		$tdate = getdate($date);
		$monthbegin = gmmktime(0,0,0, $tdate["mon"],1,$tdate["year"]);
		$monthend = self::addDate($monthbegin, 0,0,0,1,-1,0,$tzid); // add 1 month and subtract 1 day
		$day = self::addDate($date,0,0,0,0,1 - $tdate["mday"],0,$tzid);
		$month = array(array());
		while($day <= $monthend) {
			$tdate=getdate($day);
			$month[$tdate["wday"]][]=$day;
			//echo self::toSQLDateTime($day) . "<br/>\n";
			$day = self::addDate($day, 0,0,0,0,1,0,$tzid); // add 1 day
		}
		$dayinmonth=0;
		if($week >= 0)
			$dayinmonth = $month[$wday][$week];
		else
			$dayinmonth = $month[$wday][count($month[$wday]) - 1];
		//echo "return " . self::toSQLDateTime($dayinmonth);
		//exit;
		return $dayinmonth;
	}

	// convert UTC date/time to local/date time using PHP's DateTime class (new in PHP 5.3)
	static function toLocalDateTime($sqldate, $tzid = "UTC" ){
		try
		{
			$timezone = new DateTimeZone($tzid);
		}
		catch(Exception $e)
		{
			// bad time zone specified
			return $sqldate;
		}
		$udate = self::toUnixDateTime($sqldate);
		$daydatetime = new DateTime("@" . $udate);
		$tzoffset = $timezone->getOffset($daydatetime);
		return self::toSqlDateTime($udate + $tzoffset);
	}

	// convert local date/time to UTC date time using PHP's DateTime class (new in PHP 5.3)
	static function toUTCDateTime($sqldate, $tzid = "UTC" ){

		date_default_timezone_set("UTC");
		try
		{
			$date = JDate::getInstance($sqldate, $tzid);
		}
		catch(Exception $e)
		{
			// bad time zone specified
			return $sqldate;
		}
		$offset = $date->getOffsetFromGMT();
		if($offset >= 0)
			$date->sub(new DateInterval("PT".$offset."S"));
		else
			$date->add(new DateInterval("PT".abs($offset)."S"));
		return $date->toSql(true);
	}

	// return date, convert to absolute date if a relative date
	// absolute date is in the format yyyy-mm-dd
	// relative date uses y, m or d ("-2y": 2 years ago, "18m": 18 months after today, etc.)
	// relative date can include combined, comma separated values, i.e. "-1y,-1d" for 1 year and 1 day ago
	// date relative to current date or $rdate if specified
	static function getAbsDate($date,$rdate = ""){
		if(str_replace(array("y","m","d","h","n"),"",strtolower($date)) != strtolower($date)){
			date_default_timezone_set("UTC");
			if($rdate == "")
				$udate = time();
			else
				$udate = self::toUnixDateTime($rdate);
			$values=explode(",",strtolower($date));
			$y = 0;
			$m = 0;
			$d = 0;
			$h = 0;
			$n = 0;
			foreach($values as $value){
				$rtype = substr($value,strlen($value)-1);
				$rvalue = intval(substr($value,0,strlen($value) - 1));
				switch($rtype){
					case 'y':
						$y = $rvalue;
						break;
					case 'm':
						$m = $rvalue;
						break;
					case 'd':
						$d = $rvalue;
						break;
					case 'h':
						$h = $rvalue;
						break;
					case 'n':
						$n = $rvalue;
						break;
				}
				// for "-" values, move to start of day , otherwise, move to end of day
				if($rvalue[0] == '-')
					$udate = mktime(0,0,0,date('m',$udate),date('d',$udate),date('Y',$udate));
				else
					$udate = mktime(0,-1,0,date('m',$udate),date('d',$udate)+1,date('Y',$udate));
				$udate = self::addDate($udate,$h,$n,0,$m,$d,$y);
				//$date = self::toSqlDate(self::addDate(time(),$h,$n,0,$m,$d,$y));
			}
			$date = self::toSqlDateTime($udate);
		}
		return $date;
	}

	// format into iCal time format from Unix date/time stamp
	static function toiCalDateTime($datetime = null){
		date_default_timezone_set('UTC');
		if($datetime == null)
			$datetime = time();
		return gmdate("Ymd\THis",$datetime);
	}

	// format into iCal date format from Unix date/time stamp
	static function toiCalDate($datetime = null){
		date_default_timezone_set('UTC');
		if($datetime == null)
			$datetime = time();
		return gmdate("Ymd",$datetime);
	}
}
