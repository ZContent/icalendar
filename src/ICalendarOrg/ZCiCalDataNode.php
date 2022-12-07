<?php
/**
 * Create iCalendar data structure
 *
 * @author	Dan Cogliano <http://zcontent.net>
 * @copyright   Copyright (C) 2006 - 2018 by Dan Cogliano
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link http://phpfui.com/?n=ICalendarOrg
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
	 */
	public string $name = '';

	/**
	 * Node parameters (before the colon ':')
	 *
	 * @var array<string, string> $parameters
	 */
	public array $parameters = [];

	/**
	 * Node values (after the colon ':')
	 *
	 * @var array<string> $values
	 */
	public array $values = [];

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
//			$value = \str_replace(['\\N', '\\n', '\\"'], ["\n", "\n", '"'], $value);
			$tvalue = \str_replace('\\,', '`~', $value);
			$tvalue = \explode(',', $tvalue);
			$value = \str_replace('`~', '\\,', $tvalue);
			$this->values = $value;
			}

		$parameter = \substr($line, 0, $i);

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
				$this->parameters[\strtolower($param)] = $paramvalue;
				}
			}
		}

	public function __toString() : string
		{
		if (empty($this->name))
			{
			return '';
			}

		$output = $this->name;

		if (\count($this->parameters))
			{
			foreach ($this->parameters as $key => $value)
				{
				$output .= ';' . \strtoupper($key) . '=' . $value;
				}
			}

		if (\count($this->values))
			{
			$output .= ':' . $this->getValues();
			}

		return $output . "\n";
		}

	/**
	 * getName()
	 *
	 * @return string the name of the object
	 */
	public function getName() : string
		{
		return $this->name;
		}

	/**
	 * Get specific parameter from array
	 */
	public function getParameter(string $index) : string
		{
		return $this->parameters[$index];
		}

	/**
	 * Get parameter array
	 *
	 * @return array<string, string>
	 */
	public function getParameters() : array
		{
		return $this->parameters;
		}

	/**
	 * Get comma separated values
	 */
	public function getValues() : string
		{
		return \implode(',', $this->values);
		}
	}
