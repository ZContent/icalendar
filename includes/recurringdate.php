<?php

/**
 * Zap Calendar Recurring Date Helper Class
 *
 * @copyright   Copyright (C) 2006 - 2016 by Dan Cogliano
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_ZAPCAL') or die( 'Restricted access' );

/**
 * Class to expand recurring rule to a list of dates
 */
class ZCRecurringDate {
	var $rules = "";
	var $startdate = null;
	var $nextdate = null;
	var $freq = null;
	var $tzid = null;
	var $year=0;
	var $month=0;
	var $week=0;
	var $day=0;
	var $hour=0;
	var $minute=0;
	var $second=0;
	var $until=null;
	var $count=0;
	var $repeatmode=null;
	var $incr=0;
	var $bysecond=array();
	var $byminute=array();
	var $byhour=array();
	var $byday=array();
	var $bymonthday=array();
	var $daybegin=array();
	var $dayend=array();
	var $bymonth=array();
	var $byyear=array();
	var $bysetpos=array();
	var $ibymonthday = 0;
	var $ibymonth = 0;
	var $interval = 1;
	var $debug = 0;
	var $error;
	var $exdates=array();
	
	// $startdate is in local timezone
	// $exdates  array is in UTC timezone
	function ZCRecurringDate($rules, $startdate, $exdates = array(),$tzid = "UTC"){
		if(strlen($rules) > 0){
			//move exdates to event timezone for comparing with event date
			for($i = 0; $i < count($exdates); $i++)
			{
				$exdates[$i] = ZDateHelper::toUnixDateTime(ZDateHelper::toLocalDateTime(ZDateHelper::toSQLDateTime($exdates[$i]),$tzid));
			}
	
			$rules=str_replace("\'","",$rules);
			$this->rules = $rules;
			if($startdate == null){
				// if not specified, use start date of beginning of last year
				$tdate=getdate();
				$startdate=mktime(0,0,0,1,1,$tdate["year"] - 1);
			}
			$this->startdate = $startdate;
			$this->nextdate = $startdate;
			$this->tzid = $tzid;
			$this->exdates = $exdates;
	
			$rules=explode(";", $rules);
			$ruletype = "";
			foreach($rules as $rule){
				$item=explode("=",$rule);
				//echo $item[0] . "=" . $item[1] . "<br/>\n";
				switch($item[0]){
					case "FREQ":
						switch($item[1]){
							case "YEARLY":
								$this->year = 1;
								$this->freq="y";
								break;
							case "MONTHLY":
								$this->month = 1;
								$this->freq="m";
								break;
							case "WEEKLY":
								$this->week = 1;
								$this->freq="w";
								break;
							case "DAILY":
								$this->day = 1;
								$this->freq="d";
								break;
							case "HOURLY":
								$this->hour = 1;
								$this->freq="h";
								break;
							case "MINUTELY":
								$this->minute = 1;
								$this->freq="i";
								break;
							case "SECONDLY":
								$this->second = 1;
								$this->freq="s";
								break;
						}
						break;
					case "INTERVAL":
						$this->interval = $item[1];
						break;
					case "BYSECOND":
						$this->bysecond = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "BYMINUTE":
						$this->byminute = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "BYHOUR":
						$this->byhour = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "BYDAY":
						$this->byday = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "BYMONTHDAY":
						$this->bymonthday = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "BYMONTH":
						$this->bymonth = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "BYYEAR":
						$this->byyear = explode(",",$item[1]);
						$ruletype = $item[0];
						break;
					case "COUNT":
						$this->count = intval($item[1]);
						$this->repeatmode = "c";
						break;
					case "BYSETPOS":
						$this->bysetpos = explode(",",$item[1]);
						break;
					case "UNTIL":
						$this->until = ZDateHelper::fromiCaltoUnixDateTime($item[1]);
						$this->repeatmode = "u";
						break;
				}
			}
			if(count($this->bysetpos) > 0){
				switch($ruletype){
					case "BYYEAR":
						$this->byyear = $this->bySetPos($this->byyear,$this->bysetpos);
						break;
					case "BYMONTH":
						$this->bymonth = $this->bySetPos($this->bymonth,$this->bysetpos);
						break;
					case "BYMONTHDAY":
						$this->bymonthday = $this->bySetPos($this->bymonthday,$this->bysetpos);
						break;
					case "BYDAY":
						$this->byday = $this->bySetPos($this->byday,$this->bysetpos);
						break;
					case "BYHOUR":
						$this->byhour = $this->bySetPos($this->byhour,$this->bysetpos);
						break;
					case "BYMINUTE":
						$this->byminute = $this->bySetPos($this->byminute,$this->bysetpos);
						break;
					case "BYSECOND":
						$this->bysecond = $this->bySetPos($this->bysecond,$this->bysetpos);
						break;
				}
			}
			if(is_array($this->byday)){
				$dow = array("SU","MO","TU","WE","TH","FR","SA");
				for($w=0; $w<=5; $w++){
					for($d=0; $d < 7; $d++){
						$this->daybegin[$d][$w] = 0;
						$this->dayend[$d][$w] = 0;
						if(in_array($dow[$d],$this->byday)||
							in_array(($w+1) . $dow[$d], $this->byday)||
							in_array(($w+1) . $dow[$d], $this->byday))
							$this->daybegin[$d][$w] = 1;
						if(in_array("-" . ($w+1) . $dow[$d], $this->byday)){
							$this->dayend[$d][$w] = 1;
						}
						// echo $this->daybegin[$d][$w] . " ";
					}
					// echo "<br />\n";
				}
			}
	
		}
	}
	
	function bySetPos($bytype, $bysetpos){
		$result = array();
		for($i=0; $i < count($bysetpos); $i++){
			for($j=0; $j < count($bytype); $j++){
				$result[] = $bysetpos[$i] . $bytype[$j];
			}
		}
		return $result;
	}
	
	function error($msg){
		$this->error = $msg;
		echo "ZCRecurringDate() error:" . $this->error . "<br />\n";
	}
	
	function debug($level, $msg){
		if($this->debug >= $level)
			echo $msg . "<br/>\n";
	}
	
	function getError(){
		return $this->error;
	}
	
	function byYear($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"byYear(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$count = 0;
		if(count($this->byyear) > 0){
			foreach($this->byyear as $year){
				$t = getdate($startdate);
				$wdate = mktime($t[hours],$t[minutes],$t[seconds],$t[month],$t[mday],$year);
				if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
					$count = $this->byMonth($wdate, $enddate, $rdates, $tzid);
					if($count == 0) {
						$rdates[] = $wdate;
						$count++;
					}
				}
			}
		}
		else if(!$this->maxDates($rdates))
			$count = $this->byMonth($startdate, $enddate, $rdates, $tzid);
		self::debug(1,"byYear() returned " . $count );
		return $count;
	}
	
	function byMonth($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"byMonth(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$count = 0;
		if(count($this->bymonth) > 0){
			foreach($this->bymonth as $month){
				$t = getdate($startdate);
				$wdate = mktime($t["hours"],$t["minutes"],$t["seconds"],$month,$t["mday"],$t["year"]);
				if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
					$count = $this->byMonthDay($wdate, $enddate, $rdates, $tzid);
					if($count == 0) {
						$rdates[] = $wdate;
						$count++;
					}
				}
			}
		}
		else if(!$this->maxDates($rdates))
			$count = $this->byMonthDay($startdate, $enddate, $rdates, $tzid);
		self::debug(1,"byMonth() returned " . $count );
		return $count;
	}
	
	function byMonthDay($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"byMonthDay(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$count = 0;
		self::debug(1,"start date: " . ZDateHelper::toSqlDateTime($startdate));
		if(count($this->bymonthday) > 0){
			foreach($this->bymonthday as $day){
				$day = intval($day);
				$t = getdate($startdate);
				$wdate = mktime($t['hours'],$t['minutes'],$t['seconds'],$t['mon'],$day,$t['year']);
				self::debug(2,"mktime(" . $t['hours'] . ", " . $t['minutes']
				. ", " . $t['mon'] . ", " . $day . ", " . $t['year'] . ") returned $wdate");
				if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
					$count = $this->byDay($wdate, $enddate, $rdates, $tzid);
					if($count == 0) {
						$rdates[] = $wdate;
						$count++;
					}
				}
			}
		}
		else if(!$this->maxDates($rdates)) {
			self::debug(1,"start date: " . ZDateHelper::toSqlDateTime($startdate));
			$count = $this->byDay($startdate, $enddate, $rdates, $tzid);
		}
		self::debug(1,"byMonthDay() returned " . $count );
		return $count;
	}
	
	function byDay($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"byDay(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$days = array(
			"SU" => 0,
			"MO" => 1,
			"TU" => 2,
			"WE" => 3,
			"TH" => 4,
			"FR" => 5,
			"SA" => 6);
		$idays = array(
			0 => "SU",
			1 => "MO",
			2 => "TU",
			3 => "WE",
			4 => "TH",
			5 => "FR",
			6 => "SA");
	
		$count = 0;
		if(count($this->byday) > 0){
			if(empty($this->byday[0]))
			{
				$this->byday[0] = $idays[date("w",$startdate)];
			}
			foreach($this->byday as $tday){
				$t = getdate($startdate);
				$day = substr($tday,strlen($tday) - 2);
				if(strlen($day) < 2)
				{
					// missing start day, use current date for DOW
					$day = $idays[date("w",$startdate)];
				}
				if(strlen($tday) > 2) {
					$imin = 1;
					$imax = 5; // max # of occurances in a month
					if(strlen($tday) > 2)
						$imin = $imax = substr($tday,0,strlen($tday) - 2);
					self::debug(2,"imin: $imin, imax: $imax, tday: $tday, day: $day, daynum: {$days[$day]}");
					for($i = $imin; $i <= $imax; $i++){
						$wdate = ZDateHelper::getDateFromDay($startdate,$i-1,$days[$day],$tzid);
						self::debug(2,"getDateFromDay(" . ZDateHelper::toSqlDateTime($startdate)
							. ",$i,{$days[$day]}) returned " . ZDateHelper::toSqlDateTime($wdate));
						if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
							$count = $this->byHour($wdate, $enddate, $rdates);
							if($count == 0){
								$rdates[] = $wdate;
								$count++;
								//break;
							}
						}
					}
				}
				else {
					// day of week version
					$startdate_dow = date("w",$startdate);
					$datedelta = $days[$day] - $startdate_dow;
					self::debug(2, "start_dow: $startdate_dow, datedelta: $datedelta");
					if($datedelta >= 0)
					{
						$wdate = ZDateHelper::addDate($startdate,0,0,0,0,$datedelta,0,$this->tzid);
						self::debug(2, "wdate: " . ZDateHelper::toSqlDateTime($wdate));
						if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
							$count = $this->byHour($wdate, $enddate, $rdates);
							if($count == 0){
								$rdates[] = $wdate;
								$count++;
								self::debug(2,"adding date " . ZDateHelper::toSqlDateTime($wdate) );
							}
						}
					}
				}
			}
		}
		else if(!$this->maxDates($rdates))
			$count = $this->byHour($startdate, $enddate, $rdates);
		self::debug(1,"byDay() returned " . $count );
		return $count;
	}
	
	function byHour($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"byHour(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$count = 0;
		if(count($this->byhour) > 0){
			foreach($this->byhour as $hour){
				$t = getdate($startdate);
				$wdate = mktime($hour,$t["minutes"],$t["seconds"],$t["mon"],$t["mday"],$t["year"]);
				self::debug(2,"checking date/time " . ZDateHelper::toSqlDateTime($wdate));
				if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
					$count = $this->byMinute($wdate, $enddate, $rdates);
					if($count == 0) {
						$rdates[] = $wdate;
						$count++;
					}
				}
			}
		}
		else if(!$this->maxDates($rdates))
			$count = $this->byMinute($startdate, $enddate, $rdates);
		self::debug(1,"byHour() returned " . $count );
		return $count;
	}
	
	function byMinute($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"byMinute(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$count = 0;
		if(count($this->byminute) > 0){
			foreach($this->byminute as $minute){
				$t = getdate($startdate);
				$wdate = mktime($t["hours"],$minute,$t["seconds"],$t["mon"],$t["mday"],$t["year"]);
				if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
					$count = $this->bySecond($wdate, $enddate, $rdates);
					if($count == 0) {
						$rdates[] = $wdate;
						$count++;
					}
				}
			}
		}
		else if(!$this->maxDates($rdates))
			$count = $this->bySecond($startdate, $enddate, $rdates);
		self::debug(1,"byMinute() returned " . $count );
		return $count;
	}
	
	function bySecond($startdate, $enddate, &$rdates, $tzid="UTC"){
		self::debug(1,"bySecond(" . ZDateHelper::toSqlDateTime($startdate) . ","
			. ZDateHelper::toSqlDateTime($enddate) . "," . count($rdates) . " dates)");
		$count = 0;
		if(count($this->bysecond) > 0){
			foreach($this->bysecond as $second){
				$t = getdate($startdate);
				$wdate = mktime($t["hours"],$t["minutes"],$second,$t["mon"],$t["mday"],$t["year"]);
				if($startdate <= $wdate && $wdate < $enddate && !$this->maxDates($rdates)){
					$rdates[] = $wdate;
					$count++;
				}
			}
		}
		self::debug(1,"bySecond() returned " . $count );
		return $count;
	}
	
	function maxDates($rdates){
		if($this->repeatmode == "c" && count($rdates) >= $this->count)
			return true; // exceeded count
		else if(count($rdates) > 0 && $this->repeatmode == "u" && $rdates[count($rdates) - 1] > $this->until){
			return true; //past date
		}
		return false;
	}
	
	function getDates($maxdate = null){
		//$this->debug = 2;
		self::debug(1,"getDates()");
		$nextdate = $enddate = $this->startdate;
		$rdates = array();
		$done = false;
		$eventcount = 0;
		$loopcount = 0;
		self::debug(2,"freq: " . $this->freq . ", interval: " . $this->interval);
		while(!$done){
			self::debug(1,"<b>*** Frequency ({$this->freq}) loop pass $loopcount ***</b>");
			switch($this->freq){
			case "y":
				if($eventcount > 0)
				{
					$nextdate = ZDateHelper::addDate($nextdate,0,0,0,0,0,$this->interval,$this->tzid);
					self::debug(2,"addDate() returned " . ZDateHelper::toSqlDateTime($nextdate));
					if(!empty($this->byday)){
						$t = getdate($nextdate);
						$nextdate = gmmktime($t["hours"],$t["minutes"],$t["seconds"],$t["mon"],1,$t["year"]);
					}
					self::debug(2,"nextdate set to $nextdate (". ZDateHelper::toSQLDateTime($nextdate) . ")");
				}
				$enddate=ZDateHelper::addDate($nextdate,0,0,0,0,0,1);
				break;
			case "m":
				if($eventcount > 0)
				{
					
					$nextdate = ZDateHelper::addDate($nextdate,0,0,0,$this->interval,0,0,$this->tzid);
					self::debug(2,"addDate() returned " . ZDateHelper::toSqlDateTime($nextdate));
				}
				if(count($this->byday) > 0)
				{
					$t = getdate($nextdate);
					if($t["mday"] > 28)
					{
						//check for short months when using month by day, make sure we do not overshoot the counter and skip a month
						$nextdate = ZDateHelper::addDate($nextdate,0,0,0,$this->interval,0,0,$this->tzid);
						$t2 = getdate($nextdate);
						if($t2["mday"] < $t["mday"])
						{
							// oops, skipped a month, backup to previous month
							$nextdate = ZDateHelper::addDate($nextdate,0,0,0,0,$t2["mday"] - $t["mday"],0,$this->tzid);
						}
					}
					$t = getdate($nextdate);
					$nextdate = mktime($t["hours"],$t["minutes"],$t["seconds"],$t["mon"],1,$t["year"]);
				}
				self::debug(2,"nextdate set to $nextdate (". ZDateHelper::toSQLDateTime($nextdate) . ")");
				$enddate=ZDateHelper::addDate($nextdate,0,0,0,$this->interval,0,0);
				break;
			case "w":
				if($eventcount == 0)
					$nextdate=$nextdate;
				else {
					$nextdate = ZDateHelper::addDate($nextdate,0,0,0,0,$this->interval*7,0,$this->tzid);
					if(count($this->byday) > 0){
						$dow = date("w", $nextdate);
						// move to beginning of week (Sunday)
						$bow = 0;
						$diff = $bow - $dow;
						if($diff > 0)
							$diff = $diff - 7;
						$nextdate = ZDateHelper::addDate($nextdate,0,0,0,0,$diff,0);
					}
					self::debug(2,"nextdate set to $nextdate (". ZDateHelper::toSQLDateTime($nextdate) . ")");
				}
				$enddate=ZDateHelper::addDate($nextdate,0,0,0,0,$this->interval*7,0);
				break;
			case "d":
				$nextdate=($eventcount==0?$nextdate:
					ZDateHelper::addDate($nextdate,0,0,0,0,$this->interval,0,$this->tzid));
				$enddate=ZDateHelper::addDate($nextdate,0,0,0,0,1,0);
				break;
			}
	
			$count = $this->byYear($nextdate,$enddate,$rdates,$this->tzid);
			$eventcount += $count;
			if($maxdate > 0 && $maxdate < $nextdate)
			{
				array_pop($rdates);
				$done = true;
			}
			else if($count == 0 && !$this->maxDates($rdates)){
				$rdates[] = $nextdate;
				$eventcount++;
			}
			if($this->maxDates($rdates))
				$done = true;
	
			$year = date("Y", $nextdate);
			if($year > _ZAPCAL_MAXYEAR)
			{
				$done = true;
			}
			$loopcount++;
			if($loopcount > _ZAPCAL_MAXYEAR){
				$done = true;
				throw new Exception("Infinite loop detected in getDates()");
			}
		}
		if($this->repeatmode == "u" && $rdates[count($rdates) - 1] > $this->until){
			// erase last item
			array_pop($rdates);
		}
		$count1 = count($rdates);
		$rdates = array_unique($rdates);
		$count2 = count($rdates);
		$dups = $count1 - $count2;
		$excount = 0;
	
		foreach($this->exdates as $exdate)
		{
			if($pos = array_search($exdate,$rdates))
			{
				array_splice($rdates,$pos,1);
				$excount++;
			}
		}
		self::debug(1,"getDates() returned " . count($rdates) . " dates, removing $dups duplicates, $excount exceptions");
	
	
		if($this->debug >= 2)
		{
			self::debug(2,"Recurring Dates:");
			foreach($rdates as $rdate)
			{
				$d = getdate($rdate);
				self::debug(2,ZDateHelper::toSQLDateTime($rdate) . " " . $d["wday"] ); 
			}
			self::debug(2,"Exception Dates:");
			foreach($this->exdates as $exdate)
			{
				self::debug(2, ZDateHelper::toSQLDateTime($exdate));
			}
			//exit;
		}
	
		return $rdates;
	}
}
