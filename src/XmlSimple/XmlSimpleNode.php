<?php

namespace XmlSimple;


class XmlSimpleNode {
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $value = null;


	/**
	 * @var XmlSimpleNode[]
	 */
	protected $children = array();


	public function __construct($name, $value = null) {
		$this->name =  $name;
		$this->value = $value;
	}

	/**
	 * Returns the node XML
	 * @param bool $outputEmptyNodes False if to ignore empty nodes in output
	 * @param int $indentionLevel The indention level
	 * @return string The node XML or NULL if nothing to output
	 */
	public function toXml($outputEmptyNodes = true, $indentionLevel = 0) {
		if (!$this->isEmpty()) {
			return str_pad('', $indentionLevel, "\t") . '<' . $this->name . ">" . $this->innerToXml($outputEmptyNodes, $indentionLevel) . "</" . $this->name .">\n";
		}
		elseif ($outputEmptyNodes) {
			return str_pad('', $indentionLevel, "\t") . '<' . $this->name . " />\n";
		}

		return null;
	}

	/**
	 * Returns the inner XML of the node as string
	 * @param bool $outputEmptyNodes False if to ignore empty nodes in output
	 * @param int $indentionLevel The indention level
	 * @return null|string The inner XML or NULL if node is empty
	 */
	public function innerToXml($outputEmptyNodes = true, $indentionLevel = 0) {

		if (!$this->isEmpty()) {
			$ret = '';

			if (!empty($this->children)) {
				$ret .= "\n";
				foreach($this->children as $currChild) {
					$ret .= $currChild->toXml($outputEmptyNodes, $indentionLevel + 1);
				}
				$ret .= str_pad('', $indentionLevel, "\t");
			}
			else {
				$ret .= $this->xmlEntities($this->value . '');
			}

			return $ret;
		}
		return null;
	}

	/**
	 * Returns if the node has no inner data
	 * @return bool True if node has no inner data. Else false
	 */
	public function isEmpty() {
		return (empty($this->children) && $this->value === null);
	}

	/**
	 * Creates a new child node
	 * @param string $name The child node name
	 * @param mixed|null $value The node value
	 * @return XmlSimpleNode The generated child node
	 */
	public function createChild($name, $value = null) {
		$child = new XmlSimpleNode($name, $value);

		$this->addChild($child);

		return $child;
	}

	/**
	 * Adds a new child node to this node
	 * @param XmlSimpleNode $node The node to add as child
	 */
	public function addChild(XmlSimpleNode $node) {
		$this->children[] = $node;
	}

	/**
	 * Gets all child nodes
	 * @return XmlSimpleNode[]
	 */
	public function getChildren() {
		return $this->children;
	}

	/**
	 * Gets the node name
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the node name
	 * @param string $name The node name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Gets the node value
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Sets the node value
	 * @param string $value The node value
	 */
	public function setValue($value) {
		$this->value = $value;
	}




	private function xmlEntities($string) {
		$inList = "&<>\"'";

		return preg_replace_callback("/[{$inList}]/", array($this, 'get_xml_entity_at_index_0'), $string);
	}

	private function get_xml_entity_at_index_0($CHAR) {
		if (!is_string($CHAR[0]) || (strlen($CHAR[0]) > 1)) {
			throw new \Exception("function: 'get_xml_entity_at_index_0' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type.");
		}
		switch ($CHAR[0]) {
			case "'":
			case '"':
			case '&':
			case '<':
			case '>':
				return htmlspecialchars($CHAR[0], ENT_QUOTES);
				break;
			default:
				return "&#" . str_pad(ord($CHAR[0]), 3, '0', STR_PAD_LEFT) . ";";
				break;
		}
	}

} 