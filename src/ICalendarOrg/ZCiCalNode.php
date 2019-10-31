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
	 * @var array
	 */
	public $child = [];

	/**
	 * Array of $data for this node
	 *
	 * @var array
	 */
	public $data = [];
	/**
	 * The name of the node
	 *
	 * @var string
	 */
	public $name = '';


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
	public function __construct($_name, & $_parent, $first = false)
		{
		$this->name = $_name;
		$this->parentnode = $_parent;
		if ($_parent != null)
			{
			if (count($this->parentnode->child) > 0)
				{
				if ($first)
					{
					$first = & $this->parentnode->child[0];
					$first->prev = & $this;
					$this->next = & $first;
					}
				else
					{
					$prev = & $this->parentnode->child[count($this->parentnode->child) - 1];
					$prev->next = & $this;
					$this->prev = & $prev;
					}
				}
			if ($first)
				{
				array_unshift($this->parentnode->child, $this);
				}
			else
				{
				$this->parentnode->child[] = & $this;
				}
			}
		/*
		echo 'creating ' . $this->getName();
		if($_parent != null)
			echo ' child of ' . $_parent->getName() . '/' . count($this->parentnode->child);
		echo '<br/>';
		*/
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
		if (array_key_exists($node->getName(), $this->data))
			{
			if (! is_array($this->data[$node->getName()]))
				{
				$this->data[$node->getName()] = [];
				$this->data[$node->getName()][] = $node;
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
	 * @return string iCalendar formatted output
	 * @throws Exception
	 */
	public function export($node = null, $level = 0)
		{
		$txtstr = '';
		if ($node == null)
			{
			$node = $this;
			}
		if ($level > 5)
			{
			//die("levels nested too deep<br/>\n");
			throw new \Exception('levels nested too deep');
			}
		$txtstr .= 'BEGIN:' . $node->getName() . "\r\n";
		if (property_exists($node, 'data'))
			{
			foreach ($node->data as $d)
				{
				if (is_array($d))
					{
					foreach ($d as $c)
						{
						//$txtstr .= $node->export($c,$level + 1);
						$p = '';
						$params = @$c->getParameters();
						if (count($params) > 0)
							{
							foreach ($params as $key => $value)
								{
								$p .= ';' . strtoupper($key) . '=' . $value;
								}
							}
						$txtstr .= $this->printDataLine($c, $p);
						}
					}
				else
					{
					$p = '';
					$params = @$d->getParameters();
					if (count($params) > 0)
						{
						foreach ($params as $key => $value)
							{
							$p .= ';' . strtoupper($key) . '=' . $value;
							}
						}
					$txtstr .= $this->printDataLine($d, $p);
					}
				}
			}
		if (property_exists($node, 'child'))
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
	 * @return string
	 */
	public function getAttrib($i)
		{
		return $this->attrib[$i];
		}

	/**
	 * Get the first child of this object
	 *
	 * @return object|null The first child
	 *
	 * @return mixed
	 */
	public function getFirstChild()
		{
		if (count($this->child) > 0)
			{
			//echo 'moving from ' . $this->getName() . ' to ' . $this->child[0]->getName() . '<br/>';
			return $this->child[0];
			}

		return null;
		}

	/**
	* Return the name of the object
	*
	* @return string
	*/
	public function getName()
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
	 * @return string
	 */
	public function printDataLine($d, $p)
		{
		$txtstr = '';

		$values = $d->getValues();
		// don't think we need this, Sunbird does not like it in the EXDATE field
		//$values = str_replace(',', "\\,", $values);

		$line = $d->getName() . $p . ':' . $values;
		$line = str_replace(['<br>', '<BR>', '<br/>', '<BR/'], '\\n', $line);
		$line = str_replace(["\r\n", "\n\r", "\n", "\r"], '\n', $line);
		//$line = str_replace(array(',',';','\\'), array('\\,','\\;','\\\\'),$line);
		//$line =strip_tags($line);
		$linecount = 0;
		while (strlen($line) > 0)
			{
			$linewidth = ($linecount == 0 ? 75 : 74);
			$linesize = (strlen($line) > $linewidth ? $linewidth : strlen($line));
			if ($linecount > 0)
				{
				$txtstr .= ' ';
				}
			$txtstr .= substr($line, 0, $linesize) . "\r\n";
			$linecount += 1;
			$line = substr($line, $linewidth);
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
	public function printTree($node = null, $level = 1)
		{
		$level += 1;
		$html = '';
		if ($node == null)
			{
			$node = $this->parentnode;
			}
		if ($level > 5)
			{
			die("levels nested too deep<br/>\n");
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
	public function setAttrib($value)
		{
		$this->attrib[] = $value;

		return $this;
		}
	}
