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
 * Object for storing a list of unfolded iCalendar lines (ZCiCalDataNode objects)
 *
 * @property object $parentnode Parent of this node
 * @property array $child Array of children for this node
 * @property data $data Array of data for this node
 * @property object $next Next sibling of this node
 * @property object $prev Previous sibling of this node
 */
class ZCiCalNode
	{
	/**
	 * Array of children for this node
	 *
	 */
	public array $child = [];

	/**
	 * Array of $data for this node
	 *
	 */
	public array $data = [];

	/**
	 * The name of the node
	 *
	 */
	public string $name = '';

	/**
	 * Next sibling of this node
	 *
	 * @var object
	 */
	public $next = null;

	/**
	 * The parent of this node
	 *
	 * @var object
	 */
	public $parentnode = null;

	/**
	 * Previous sibling of this node
	 *
	 * @var object
	 */
	public $prev = null;

	/**
	 * Create ZCiCalNode
	 *
	 * @param string $_name Name of node
	 * @param object $_parent Parent node for this node
	 * @param bool $first Is this the first child for this parent?
	 */
	public function __construct(string $_name, $_parent, bool $first = false)
		{
		$this->name = $_name;
		$this->parentnode = $_parent;

		if (null != $_parent)
			{
			if (\count($this->parentnode->child) > 0)
				{
				if ($first)
					{
					$first = & $this->parentnode->child[0];
					$first->prev = & $this;
					$this->next = & $first;
					}
				else
					{
					$prev = & $this->parentnode->child[\count($this->parentnode->child) - 1];
					$prev->next = & $this;
					$this->prev = & $prev;
					}
				}

			if ($first)
				{
				\array_unshift($this->parentnode->child, $this);
				}
			else
				{
				$this->parentnode->child[] = & $this;
				}
			}
		}

	/**
	 * Add node to list
	 *
	 * @param object $node
	 *
	 * @return ZCiCalNode
	 */
	public function addNode($node)
		{
		if (\array_key_exists($node->getName(), $this->data))
			{
			if (! \is_array($this->data[$node->getName()]))
				{
				$this->data[$node->getName()] = [];
				}
			$this->data[$node->getName()][] = $node;
			}
		else
			{
			$this->data[$node->getName()] = $node;
			}

		return $this;
		}

	/**
	 * export tree to icalendar format
	 *
	 * @param object $node Top level node to export
	 * @param int $level Level of recursion (usually leave this blank)
	 *
	 * @throws Exception
	 * @return string iCalendar formatted output
	 */
	public function export($node = null, int $level = 0)
		{
		$txtstr = '';

		if (null == $node)
			{
			$node = $this;
			}

		if ($level > 5)
			{
			//die("levels nested too deep<br/>\n");
			throw new \Exception('levels nested too deep');
			}
		$txtstr .= 'BEGIN:' . $node->getName() . "\r\n";

		if (\property_exists($node, 'data'))
			{
			foreach ($node->data as $d)
				{
				if (\is_array($d))
					{
					foreach ($d as $c)
						{
						//$txtstr .= $node->export($c,$level + 1);
						$p = '';
						$params = @$c->getParameters();

						if (\count($params) > 0)
							{
							foreach ($params as $key => $value)
								{
								$p .= ';' . \strtoupper($key) . '=' . $value;
								}
							}
						$txtstr .= $this->printDataLine($c, $p);
						}
					}
				else
					{
					$p = '';
					$params = @$d->getParameters();

					if (\count($params) > 0)
						{
						foreach ($params as $key => $value)
							{
							$p .= ';' . \strtoupper($key) . '=' . $value;
							}
						}
					$txtstr .= $this->printDataLine($d, $p);
					}
				}
			}

		if (\property_exists($node, 'child'))
			{
			foreach ($node->child as $c)
				{
				$txtstr .= $node->export($c, $level + 1);
				}
			}
		$txtstr .= 'END:' . $node->getName() . "\r\n";

		return $txtstr;
		}

	/**
	 * Get Attribute
	 *
	 * @param int $i array id of attribute to get
	 *
	 */
	public function getAttrib(int $i) : string
		{
		return $this->attrib[$i];
		}

	/**
	 * Get the first child of this object
	 *
	 * @return object|null The first child
	 *
	 */
	public function getFirstChild()
		{
		if (\count($this->child) > 0)
			{
			return $this->child[0];
			}


		}

	/**
	 * Return the name of the object
	 *
	 */
	public function getName() : string
		{
		return $this->name;
		}

	/**
	 * Get the parent object of this object
	 *
	 * @return object parent of this object
	 */
	public function getParent()
		{
		return $this->parentnode;
		}

	/**
	 * print an attribute line
	 *
	 * @param object $d attributes
	 * @param object $p properties
	 *
	 */
	public function printDataLine($d, $p) : string
		{
		$txtstr = '';

		$values = $d->getValues();
		// don't think we need this, Sunbird does not like it in the EXDATE field
		//$values = str_replace(',', "\\,", $values);

		$line = $d->getName() . $p . ':' . $values;
		$line = \str_replace(['<br>', '<BR>', '<br/>', '<BR/'], '\\n', $line);
		$line = \str_replace(["\r\n", "\n\r", "\n", "\r"], '\n', $line);
		//$line = str_replace(array(',',';','\\'), array('\\,','\\;','\\\\'),$line);
		//$line =strip_tags($line);
		$linecount = 0;

		while (\strlen($line) > 0)
			{
			$linewidth = (0 == $linecount ? 75 : 74);
			$linesize = (\strlen($line) > $linewidth ? $linewidth : \strlen($line));

			if ($linecount > 0)
				{
				$txtstr .= ' ';
				}
			$txtstr .= \substr($line, 0, $linesize) . "\r\n";
			$linecount += 1;
			$line = \substr($line, $linewidth);
			}

		return $txtstr;
		}

	/**
	 * Print object tree in HTML for debugging purposes
	 *
	 * @param object $node select part of tree to print, or leave blank for full tree
	 * @param int $level Level of recursion (usually leave this blank)
	 *
	 * @return string - HTML formatted display of object tree
	 */
	public function printTree($node = null, int $level = 1) : string
		{
		$level += 1;
		$html = '';

		if (null == $node)
			{
			$node = $this->parentnode;
			}

		if ($level > 5)
			{
			exit("levels nested too deep<br/>\n");
			//return;
			}

		for ($i = 0; $i < $level; $i++)
			{
			$html .= '+';
			}
		$html .= $node->getName() . "<br/>\n";

		foreach ($node->child as $c)
			{
			$html .= $node->printTree($c, $level);
			}
		$level -= 1;

		return $html;
		}

	/**
	 * Set Attribute
	 *
	 * @param string $value value of attribute to set
	 *
	 * @return ZCiCalNode
	 */
	public function setAttrib(string $value) : \ICalendarOrg\ZCiCalNode
		{
		$this->attrib[] = $value;

		return $this;
		}
	}
