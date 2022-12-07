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
	 * @var array<ZCiCalNode> $child
	 */
	public array $child = [];

	/**
	 * Array of $data for this node
	 *
	 * @var array<string, mixed> $data
	 */
	public array $data = [];

	/**
	 * The name of the node
	 */
	public string $name = '';

	/**
	 * Next sibling of this node
	 */
	public ?ZCiCalNode $next = null;

	/**
	 * The parent of this node
	 */
	public ?ZCiCalNode $parentnode = null;

	/**
	 * Previous sibling of this node
	 */
	public ?ZCiCalNode $prev = null;

	/**
	 * @var array<int, string> Array of attributes for this node
	 */
	protected array $attrib = [];

	/**
	 * Create ZCiCalNode
	 *
	 * @param string $_name Name of node
	 * @param ?ZCiCalNode $_parent Parent node for this node
	 * @param bool $first Is this the first child for this parent?
	 */
	public function __construct(string $_name, ?ZCiCalNode $_parent, bool $first = false)
		{
		$this->name = $_name;
		$this->parentnode = $_parent;

		if (null !== $_parent)
			{
			if (\count($this->parentnode->child) > 0)
				{
				if ($first)
					{
					$first = &$this->parentnode->child[0];
					$first->prev = &$this;
					$this->next = &$first;
					}
				else
					{
					$prev = &$this->parentnode->child[\count($this->parentnode->child) - 1];
					$prev->next = &$this;
					$this->prev = &$prev;
					}
				}

			if ($first)
				{
				\array_unshift($this->parentnode->child, $this);
				}
			else
				{
				$this->parentnode->child[] = &$this;
				}
			}
		}

	/**
	 * Add node to list
	 */
	public function addNode(ZCiCalNode $node) : ZCiCalNode
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
	 * @param ?ZCiCalNode $node Top level node to export
	 * @param int $level Level of recursion (usually leave this blank)
	 *
	 * @throws \Exception
	 * @return string iCalendar formatted output
	 *
	 */
	public function export(?ZCiCalNode $node = null, int $level = 0) : string
		{
		$txtstr = '';

		if (null === $node)
			{
			$node = $this;
			}

		if ($level > 5)
			{
			//die("levels nested too deep<br/>\n");
			throw new \Exception('levels nested too deep');
			}
		$txtstr .= 'BEGIN:' . $node->getName() . "\n";

		if (\property_exists($node, 'data'))
			{
			foreach ($node->data as $d)
				{
				if (\is_array($d))
					{
					foreach ($d as $c)
						{
						$txtstr .= $c;
						}
					}
				else
					{
					$txtstr .= $d;
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
		$txtstr .= 'END:' . $node->getName() . "\n";

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
	 * @return ?ZCiCalNode The first child
	 *
	 */
	public function getFirstChild() : ?ZCiCalNode
		{
		if (\count($this->child) > 0)
			{
			return $this->child[0];
			}

		return null;
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
	 * @return ?ZCiCalNode parent of this object
	 */
	public function getParent() : ?ZCiCalNode
		{
		return $this->parentnode;
		}

	/**
	 * print an attribute line
	 *
	 * @param ZCiCalDataNode $d attributes
	 * @param string $p properties
	 *
	 */
	public function printDataLine(ZCiCalDataNode $d, string $p) : string
		{
		return "{$d}";
		}

	/**
	 * Print object tree in HTML for debugging purposes
	 *
	 * @param ?ZCiCalNode $node select part of tree to print, or leave blank for full tree
	 * @param int $level Level of recursion (usually leave this blank)
	 *
	 * @return string - HTML formatted display of object tree
	 */
	public function printTree(?ZCiCalNode $node = null, int $level = 1) : string
		{
		$level += 1;
		$html = '';

		if (null === $node)
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
	 */
	public function setAttrib(string $value) : ZCiCalNode
		{
		$this->attrib[] = $value;

		return $this;
		}
	}
