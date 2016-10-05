<?php

/**
 * @package	 Zap Calendar ical Helper Class
 *
 * @copyright   Copyright (C) 2006 - 2016 by Dan Cogliano
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_ZAPCAL') or die( 'Restricted access' );

class ZCiCalDataNode {
	var $name = "";
	var $parameter=array();
	var $value=array();

	function ZCiCalDataNode( $line ) {
		//echo "ZCiCalDataNode($line)<br/>\n";
		//separate line into parameters and value
		// look for colon separating name or parameter and value
		// first change any escaped colons temporarily to make it easier
		$tline = str_replace("\\:", "`~", $line);
		// see if first colon is inside a quoted string
		$i = 0;
		$datafind = false;
		$inquotes = false;
		while(!$datafind && ($i < strlen($tline))) {
			//echo "$i: " . $tline{$i} . ", ord() = " . ord($tline{$i}) . "<br>\n";
			if(!$inquotes && $tline{$i} == ':')
				$datafind=true;
			else{
				$i += 1;
				if(substr($tline,$i,1) == '"')
					$inquotes = !$inquotes;
			}
		}
		if($datafind){
			$value = str_replace("`~","\\:",substr($line,$i+1));
			// fix escaped characters (don't see double quotes in spec but Apple apparently uses it in iCal)
			$value = str_replace(array('\\N' , '\\n', '\\"' ), array("\n", "\n" , '"'), $value);
			$tvalue = str_replace("\\,", "`~", $value);
			//echo "value: " . $tvalue . "<br>\n";
			$tvalue = explode(",",$tvalue);
			$value = str_replace("`~","\\,",$tvalue);
			$this->value = $value;
		}

		$parameter = trim(substr($line,0,$i));

		$parameter = str_replace("\\;", "`~", $parameter);
		$parameters = explode(";", $parameter);
		$parameters = str_replace("`~", "\\;", $parameters);
		$this->name = array_shift($parameters);
		foreach($parameters as $parameter){
			$pos = strpos($parameter,"=");
			if($pos > 0){
				$param = substr($parameter,0,$pos);
				$paramvalue = substr($parameter,$pos+1);
				$tvalue = str_replace("\\,", "`~", $paramvalue);
				//$tvalue = explode(",",$tvalue);
				$paramvalue = str_replace("`~","\\,",$tvalue);
				$this->parameter[strtolower($param)] = $paramvalue;
				//$this->paramvalue[] = $paramvalue;
			}
		}
	}

	function getName(){
		return $this->name;
	}

	function getParameter($i){
		return $this->parameter[$i];
	}

	function getParameters(){
		return $this->parameter;
	}

	function getValues(){
		return implode(",",$this->value);
	}
}

class ZCiCalNode {
	var $name="";
	var $parentnode=null;
	var $child= array();
	var $data= array();
	var $next=null;
	var $prev=null;

	function ZCiCalNode( $_name, & $_parent, $first = false) {
		$this->name = $_name;
		$this->parentnode = $_parent;
		if($_parent != null){
			if(count($this->parentnode->child) > 0) {
				if($first)
				{
					$first = & $this->parentnode->child[0];
					$first->prev = & $this;
					$this->next = & $first;
				}
				else
				{
					$prev =& $this->parentnode->child[count($this->parentnode->child)-1];
					$prev->next =& $this;
					$this->prev =& $prev;
				}
			}
			if($first)
			{
				array_unshift($this->parentnode->child, $this);
			}
			else
			{
				$this->parentnode->child[] =& $this;
			}
		}
		/*
		echo "creating " . $this->getName();
		if($_parent != null)
			echo " child of " . $_parent->getName() . "/" . count($this->parentnode->child);
		echo "<br/>";
		*/
	}

	function getName() {
		return $this->name;
	}

	function getAttrib($i) {
		return $this->attrib[$i];
	}

	function setAttrib($value) {
		$this->attrib[] = $value;
	}

	function &getParent() {
		return $this->parentnode;
	}
	function &getFirstChild(){
		static $nullguard = null;
		if(count($this->child) > 0) {
			//echo "moving from " . $this->getName() . " to " . $this->child[0]->getName() . "<br/>";
			return $this->child[0];
		}
		else
			return $nullguard;
	}

	function printTree(& $node=null, $level=1){
		$level += 1;
		$html = "";
		if($node == null)
			$node = $this->parentnode;
		if($level > 5)
		{
			die("levels nested too deep<br/>\n");
			//return;
		}
		for($i = 0 ; $i < $level; $i ++)
			$html .= "+";
		$html .= $node->getName() . "<br/>\n";
		foreach ($node->child as $c){
			$html .= $node->printTree($c,$level);
		}
		$level -= 1;
		return $html;
	}

	function export(& $node=null, $level=0){
		$txtstr = "";
		if($node == null)
			$node = $this;
		if($level > 5)
		{
			//die("levels nested too deep<br/>\n");
			throw new Exception("levels nested too deep");
		}
		$txtstr .= "BEGIN:" . $node->getName() . "\r\n";
		foreach ($node->data as $d){
			$p = "";
			$params = @$d->getParameters();
			if(count($params) > 0)
			{
				foreach($params as $key => $value){
					$p .= ";" . strtoupper($key) . "=" . $value;
				}
			}
			$values = $d->getValues();
			// don't think we need this, Sunbird does not like it in the EXDATE field
			//$values = str_replace(",", "\\,", $values);

			$line = $d->getName() . $p . ":" . $values;
			$line = str_replace(array("<br>","<BR>","<br/>","<BR/"),"\\n",$line);
			$line = str_replace(array("\r\n","\n\r","\n","\r"),'\n',$line);
			//$line = str_replace(array(',',';','\\'), array('\\,','\\;','\\\\'),$line);
			//$line =strip_tags($line);
			$linecount = 0;
			while (strlen($line) > 0) {
				$linewidth = ($linecount == 0? 75 : 74);
				$linesize = (strlen($line) > $linewidth? $linewidth: strlen($line));
				if($linecount > 0)
					$txtstr .= " ";
				$txtstr .= substr($line,0,$linesize) . "\r\n";
				$linecount += 1;
				$line = substr($line,$linewidth);
			}
			//echo $line . "\n";
		}
		foreach ($node->child as $c){
			$txtstr .= $node->export($c,$level + 1);
		}
		$txtstr .= "END:" . $node->getName() . "\r\n";
		return $txtstr;
	}

}

class ZTZDate {
	var $date;
	var $from;
	var $to;
	var $fromstandard;
	var $tostandard;
	var $name;
	function ZTZDate($date, $from, $to, $fromstandard, $tostandard, $name=""){
		$from = ($from[0] =="-"?-1:1)*(substr($from,1,2)*60*60 + substr($from,3,2)*60);
		$to = ($to[0] =="-"?-1:1)*(substr($to,1,2)*60*60 + substr($to,3,2)*60);
		$this->date = $date;
		$this->from = $from;
		$this->to = $to;
		$this->fromstandard = $fromstandard;
		$this->tostandard = $tostandard;
		$this->name = $name;
	}

	static function cmp_obj($a, $b)
	{
		if($a->date == $b->date)
			return 0;
		return ($a->date > $b->date) ? +1 : -1;
	}

}

class ZTimeZone {
	var $tzid;
	var $dates = array();
	function ZTimeZone($tzid, $dates){
		$this->tzid = $tzid;
		$this->dates = $dates;
	}
}

class ZCiCal {
	var $tree=null;
	var $curnode=null;
	var $linecount = 0;

/*
* ZCiCal()
* data: icalendar feed string
* maxevents: maximum # of events to read
* startevent: starting event to read
*
* use maxevents and startevent to read events in multiple passes (to save memory)
*
*/
function ZCiCal ($data = "", $maxevents = 1000000, $startevent = 0) {

	if($data != ""){
		// unfold lines
		// first change all eol chars to "\n"
		$data = str_replace(array("\r\n", "\n\r", "\n", "\r"), "\n", $data);
		// now unfold lines
		//$data = str_replace(array("\n ", "\n	"),"!?", $data);
		$data = str_replace(array("\n ", "\n	"),"", $data);
		// replace special iCal chars
		$data = str_replace(array("\\\\","\,"),array("\\",","), $data);

		// parse each line
		$lines = explode("\n", $data);

		//echo $data;
		//exit;
		$linecount = 0;
		$eventcount = 0;
		$eventpos = 0;
		foreach($lines as $line) {
			//$line = str_replace("!?", "\n", $line); // add nl back into descriptions
			// echo ($linecount + 1) . ": " . $line . "<br/>";
			if(substr($line,0,6) == "BEGIN:") {
				// start new object
				$name = substr($line,6);
				if($name == "VEVENT")
				{
					if($eventcount < $maxevents && $eventpos >= $startevent)
					{
						$this->curnode = new ZCiCalNode($name, $this->curnode);
						if($this->tree == null)
							$this->tree = $this->curnode;
					}
				}
				else
				{
					$this->curnode = new ZCiCalNode($name, $this->curnode);
					if($this->tree == null)
						$this->tree = $this->curnode;
					}
					//echo "new node: " . $this->curnode->name . "<br/>\n";
					/*
					if($this->curnode->getParent() != null)
						echo "parent of " . $this->curnode->getName() . " is " . $this->curnode->getParent()->getName() . "<br/>";
					else
						echo "parent of " . $this->curnode->getName() . " is null<br/>";
					*/
			}
			else if(substr($line,0,4) == "END:") {
				$name = substr($line,4);
				if($name == "VEVENT")
				{
					if($eventcount < $maxevents && $eventpos >= $startevent)
					{
						$eventcount++;
						if($this->curnode->getName() != $name) {
							//panic, mismatch in iCal structure
							//die("Can't read iCal file structure, expecting " . $this->curnode->getName() . " but reading $name instead");
							throw new Exception("Can't read iCal file structure, expecting " . $this->curnode->getName() . " but reading $name instead");
						}
						if($this->curnode->getParent() != null) {
							//echo "moving up from " . $this->curnode->getName() ;
							$this->curnode = & $this->curnode->getParent();
							//echo " to " . $this->curnode->getName() . "<br/>";
							//echo $this->curnode->getName() . " has " . count($this->curnode->child) . " children<br/>";
						}
					}
					$eventpos++;
				}
				else
				{
					if($this->curnode->getName() != $name) {
						//panic, mismatch in iCal structure
						//die("Can't read iCal file structure, expecting " . $this->curnode->getName() . " but reading $name instead");
						throw new Exception("Can't read iCal file structure, expecting " . $this->curnode->getName() . " but reading $name instead");
					}
					if($this->curnode->getParent() != null) {
						//echo "moving up from " . $this->curnode->getName() ;
						$this->curnode = & $this->curnode->getParent();
						//echo " to " . $this->curnode->getName() . "<br/>";
						//echo $this->curnode->getName() . " has " . count($this->curnode->child) . " children<br/>";
					}
				}
			}
			else {
				$datanode = new ZCiCalDataNode($line);
				if($this->curnode->getName() == "VEVENT")
				{
					if($eventcount < $maxevents && $eventpos >= $startevent)
					{
						if($datanode->getName() == "EXDATE")
						{
							if(!array_key_exists($datanode->getName(),$this->curnode->data))
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
							$this->curnode->data[$datanode->getName()] = $datanode;
						}
					}
				}
				else
				{
					if($datanode->getName() == "EXDATE")
					{
						if(!array_key_exists($datanode->getName(),$this->curnode->data))
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
						$this->curnode->data[$datanode->getName()] = $datanode;
					}
				}
			}
			$linecount++;
		}
	}
	else {
				$name = "VCALENDAR";
				$this->curnode = new ZCiCalNode($name, $this->curnode);
				$this->tree = $this->curnode;
				$datanode = new ZCiCalDataNode("VERSION:2.0");
				$this->curnode->data[$datanode->getName()] = $datanode;

				$datanode = new ZCiCalDataNode("PRODID:-//ZContent.net//Zap Calendar 1.0//EN");
				$this->curnode->data[$datanode->getName()] = $datanode;
				$datanode = new ZCiCalDataNode("CALSCALE:GREGORIAN");
				$this->curnode->data[$datanode->getName()] = $datanode;
				$datanode = new ZCiCalDataNode("METHOD:PUBLISH");
				$this->curnode->data[$datanode->getName()] = $datanode;
	}
}

function countEvents() {
	$count = 0;
	if(isset($this->tree->child)){
		foreach($this->tree->child as $child){
			if($child->getName() == "VEVENT")
				$count++;
		}
	}
	return $count;
}

function countVenues() {
	$count = 0;
	if(isset($this->tree->child)){
		foreach($this->tree->child as $child){
			if($child->getName() == "VVENUE")
				$count++;
		}
	}
	return $count;
}

function export() {
	return $this->tree->export($this->tree);
}

function &getFirstEvent() {
	$nullvalue = null;
	if ($this->countEvents() > 0){
		$child = $this->tree->child[0];
		$event=false;
		while(!$event && $child != null){
			if($child->getName() == "VEVENT")
				$event = true;
			else
				$child = $child->next;
		}
		return $child;
	}
	else
		return $nullvalue;
}

function &getNextEvent($event){
	do{
		$event = $event->next;
	} while($event != null && $event->getName() != "VEVENT");
	return $event;
}

function &getFirstVenue() {
	$nullvalue = null;
	if ($this->countVenues() > 0){
		$child = $this->tree->child[0];
		$event=false;
		while(!$event && $child != null){
			if($child->getName() == "VVENUE")
				$event = true;
			else
				$child = $child->next;
		}
		return $child;
	}
	else
		return $nullvalue;
}

function &getNextVenue($venue){
	do{
		$venue = $venue->next;
	} while($venue != null && $venue->getName() != "VVENUE");
	return $venue;
}

function &getFirstChild(& $thisnode){
	$nullvalue = null;
	if(count($thisnode->child) > 0) {
		//echo "moving from " . $thisnode->getName() . " to " . $thisnode->child[0]->getName() . "<br/>";
		return $thisnode->child[0];
	}
	else
		return $nullvalue;
}

function &getNextSibling(& $thisnode){
	return $thisnode->next;
}

function &getPrevSibling(& $thisnode){
	return $thisnode->prev;
}

//read iCal time, including time zone
function toUnixDateTime($datetime){
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
	$d1 = mktime($hour, $minute, $second, $month, $day, $year);

}

// format into iCal time format from Unix date/time stamp
static function fromUnixDateTime($datetime = null){
	date_default_timezone_set('UTC');
	if($datetime == null)
		$datetime = time();
	return date("Ymd\THis",$datetime);
}

// format into iCal date format from Unix date/time stamp
static function fromUnixDate($datetime = null){
	date_default_timezone_set('UTC');
	if($datetime == null)
		$datetime = time();
	return date("Ymd",$datetime);
}

// format into iCal time format from SQL Date or Date/Time format
static function fromSqlDateTime($datetime = ""){
	if($datetime == "")
		$datetime = ZDateHelper::toSqlDateTime();
	if(strlen($datetime) > 10)
		return sprintf('%04d%02d%02dT%02d%02d%02d',substr($datetime,0,4),substr($datetime,5,2),substr($datetime,8,2),
			substr($datetime,11,2),substr($datetime,14,2),substr($datetime,17,2));
	else
		return sprintf('%04d%02d%02d',substr($datetime,0,4),substr($datetime,5,2),substr($datetime,8,2));
}

// format iCal time format into SQL date or Date/Time format
static function toSqlDateTime($datetime = ""){
	if($datetime == "")
		return ZDateHelper::toSqlDateTime();
	if(strlen($datetime) > 10)
		return sprintf('%04d-%02d-%02d %02d:%02d:%02d',substr($datetime,0,4),substr($datetime,5,2),substr($datetime,8,2),
			substr($datetime,11,2),substr($datetime,14,2),substr($datetime,17,2));
	else
		return sprintf('%04d-%02d-%02d',substr($datetime,0,4),substr($datetime,5,2),substr($datetime,8,2));
}

static function getTZValues($node){
	$tzvalues = array();

	$tnode = @$node->data['TZOFFSETFROM'];
	if($tnode != null){
		$tzvalues["tzoffsetfrom"] = $tnode->getValues();
	}

	$tnode = @$node->data['TZOFFSETTO'];
	if($tnode != null){
		$tzvalues["tzoffsetto"] = $tnode->getValues();
	}

	$tnode = @$node->data['TZNAME'];
	if($tnode != null){
		$tzvalues["tzname"] = $tnode->getValues();
	}
	else
		$tzvalues["tzname"] = "";

	$tnode = @$node->data['DTSTART'];
	if($tnode != null){
		$tzvalues["dtstart"] = ZDateHelper::fromiCaltoUnixDateTime($tnode->getValues());
	}

	$tnode = @$node->data['RRULE'];
	if($tnode != null){
		$tzvalues["rrule"] = $tnode->getValues();
		//echo "rule: " . $tzvalues["rrule"] . "<br/>\n";
	}
	else{
		// no rule specified, let's create one from based on the date
		$date = getdate($tzvalues["dtstart"]);
		$month = $date["mon"];
		$day = $date["mday"];
		$tzvalues["rrule"] = "FREQ=YEARLY;INTERVAL=1;BYMONTH=$month;BYMONTHDAY=$day";
	}

	return $tzvalues;
}

static function getTimeZones($icalobj){
	// find timezone settings defined in the ical stream
	$tznode = @$icalobj->tree->child[0];
	$ZTZDates = array();
	$tzones = array();
	$tzid = "";
	if($tznode != null) {
		//$tnode = $icalobj->tree->getFirstChild();
		$child = $tznode;
		$config = JFactory::getConfig();
		//echo "starting while<br/>\n";
		while($child != null){
			if($child->getName() == "VTIMEZONE"){
				$tzid = "default";
				$tnode = $child->data['TZID'];
				if($tnode != null){
					$tzid = $tnode->getValues();
				}

				//echo "at " . $child->getName() . "<br/>";
				$daylight = array();
				$standard = array();
				$vtz=$child->getFirstChild();
				while($vtz != null){
					//echo "** " . $vtz->getName() . "<br/>\n";
					switch($vtz->getName()){
					case "DAYLIGHT":
						//echo "in daylight<br/>";
						$daylight = ZCiCal::getTZValues($vtz);
						break;
					case "STANDARD":
						//echo "in standard<br/>";
						$standard = ZCiCal::getTZValues($vtz);
						$eventtz = (substr($standard["tzoffsetto"],0,1)=="-"?-1:1)*(substr($standard["tzoffsetto"],1,2)*60*60 + substr($standard["tzoffsetto"],3,2)*60);
						//echo "tz: " . $standard["tzoffsetto"] . " / " . $eventtz . "<br/>\n";
						//exit;
						break;
					}
					$vtz = $icalobj->getNextSibling($vtz);
				}
				if(count($daylight) > 0 || count($standard) > 0){
					$ZTZDate = array();
					if(count($daylight) > 0){
						$drule = new ZCalendarRecurringDate(@$daylight["rrule"], @$daylight["dtstart"]);
						//$drule->debug=5;
						$ddates = $drule->getDates();
						foreach($ddates as $ddate){
							$ZTZDate[$ddate] = new ZTZDate($ddate, $daylight["tzoffsetfrom"], $daylight["tzoffsetto"], true, false, $daylight["tzname"]);
						}
					}
					if(count($standard) > 0){
						$srule = new ZCalendarRecurringDate(@$standard["rrule"], @$standard["dtstart"]);
						//$srule->debug=5;
						$sdates = $srule->getDates();
						foreach($sdates as $sdate){
							$ZTZDate[$sdate] = new ZTZDate($sdate, $standard["tzoffsetfrom"], $standard["tzoffsetto"], false, true, $standard["tzname"]);
						}
					}
					usort($ZTZDate,array("ZTZDate","cmp_obj"));
					$tzones[$tzid] = $ZTZDate;
				}
			}
			$child = $icalobj->getNextSibling($child);
		}
	}
	return $tzones;
}

// escape slashes, commas and semicolons in strings
static function formatContent($content)
{
	$content = str_replace(array('\\' , ',' , ';' ), array('\\\\' , '\\,' , '\\;' ),$content);
	return $content;
}

}

?>
