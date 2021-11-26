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
 * Object for storing an unfolded iCalendar line
 *
 * The ZCiCalDataNode class contains data from an unfolded iCalendar line
 */
class ZCiCalDataNode
	{
	/**
	 * The name of the node
	 *
	 */
	public string $name = '';

	/**
	 * Node parameters (before the colon ':')
	 *
	 */
	public array $parameter = [];

	/**
	 * Node values (after the colon ':')
	 *
	 */
	public array $value = [];

	/**
	 * Create an object from an unfolded iCalendar line
	 *
	 * @param string $line An unfolded iCalendar line
	 *
	 * @return void
	 */
	public function __construct(string $line)
		{
		//separate line into parameters and value
		// look for colon separating name or parameter and value
		// first change any escaped colons temporarily to make it easier
		$tline = \str_replace('\\:', '`~', $line);
		// see if first colon is inside a quoted string
		$i = 0;
		$datafind = false;
		$inquotes = false;

		while (! $datafind && ($i < \strlen($tline)))
			{
			if (! $inquotes && ':' == $tline[$i])
				{
				$datafind = true;
				}
			else
				{
				$i += 1;

				if ('"' == \substr($tline, $i, 1))
					{
					$inquotes = ! $inquotes;
					}
				}
			}

		if ($datafind)
			{
			$value = \str_replace('`~', '\\:', \substr($line, $i + 1));
			// fix escaped characters (don't see double quotes in spec but Apple apparently uses it in iCal)
			$value = \str_replace(['\\N', '\\n', '\\"'], ["\n", "\n", '"'], $value);
			$tvalue = \str_replace('\\,', '`~', $value);
			$tvalue = \explode(',', $tvalue);
			$value = \str_replace('`~', '\\,', $tvalue);
			$this->value = $value;
			}

		$parameter = \trim(\substr($line, 0, $i));

		$parameter = \str_replace('\\;', '`~', $parameter);
		$parameters = \explode(';', $parameter);
		$parameters = \str_replace('`~', '\\;', $parameters);
		$this->name = \array_shift($parameters);

		foreach ($parameters as $parameter)
			{
			$pos = \strpos($parameter, '=');

			if ($pos > 0)
				{
				$param = \substr($parameter, 0, $pos);
				$paramvalue = \substr($parameter, $pos + 1);
				$tvalue = \str_replace('\\,', '`~', $paramvalue);
				$paramvalue = \str_replace('`~', '\\,', $tvalue);
				$this->parameter[\strtolower($param)] = $paramvalue;
				}
			}
		}

	/**
	 * getName()
	 *
	 * Return the name of the object
	 *
	 */
	public function getName() : string
		{
		return $this->name;
		}

	/**
	 * Get $ith parameter from array
	 *
	 *
	 */
	public function getParameter(int $i)
		{
		return $this->parameter[$i];
		}

	/**
	 * Get parameter array
	 *
	 */
	public function getParameters() : array
		{
		return $this->parameter;
		}

	/**
	 * Get comma separated values
	 *
	 */
	public function getValues() : string
		{
		return \implode(',', $this->value);
		}
	}
