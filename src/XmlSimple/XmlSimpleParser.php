<?php
	namespace XmlSimple;

	use XmlSimple\Exception\XmlAttributeNotFoundException;
	use XmlSimple\Exception\XmlNodeNotFoundException;
	use XmlSimple\Exception\XmlParseException;

	class XmlSimpleParser {

		/**
		 * @var \SimpleXMLElement
		 */
		protected $node = null;

		protected $decimalSeparator = '.';
		protected $returnCharset = 'UTF-8';

		/**
		 * Creates a new instance
		 * @param \SimpleXMLElement|null $node The node to load for the parser
		 * @param string $returnCharset The charset returned values are to be encoded in
		 */
		public function __construct(\SimpleXMLElement $node = null, $returnCharset = 'UTF-8') {
			if (!is_null($node) && $node instanceof \SimpleXMLElement)
				$this->node = $node;

			$this->setReturnCharset($returnCharset);
		}


		/**
		 * Creates a new parser instance for a specified child not. All parsing settings are copied to the new parser
		 * @param \SimpleXMLElement|string $node The parser root node (Either the object or the node path as string)
		 * @return \XmlSimple\XmlSimpleParser The new parser instance
		 * @throws XmlNodeNotFoundException
		 */
		public function createChildParser($node) {
			if (is_string($node)) {
				$node = $this->getNode($node);
			}

			$newParser = new self($node, $this->returnCharset);
			$newParser->decimalSeparator = $this->decimalSeparator;

			return $newParser;
		}

		/**
		 * Gets the parser's root node
		 * @return \SimpleXMLElement|null
		 */
		public function getRoot() {
			return $this->node;
		}

		/**
		 * Gets the default decimal separator for parsing float values
		 * @return string
		 */
		public function getDecimalSeparator() {
			return $this->decimalSeparator;
		}

		/**
		 * Sets the default decimal separator for parsing float values
		 * @param string $decimalSeparator The decimal separator
		 */
		public function setDecimalSeparator($decimalSeparator) {
			$this->decimalSeparator = $decimalSeparator;
		}

		/**
		 * Gets the charset returned values are to be encoded in
		 * @return string The charset
		 */
		public function getReturnCharset() {
			return $this->returnCharset;
		}

		/**
		 * Sets the charset returned values are to be encoded in
		 * @param string $returnCharset The charset name
		 */
		public function setReturnCharset($returnCharset) {
			$this->returnCharset = strtoupper($returnCharset);
		}


		/**
		 * Loads the specified file to this instance
		 * @param string $filename Name of the file to open
		 * @throws XmlParseException
		 * @return \SimpleXMLElement The object representing the document
		 */
		public function loadFile($filename) {
			$dom = @simplexml_load_file($filename);
			if ($dom instanceof \SimpleXMLElement) {
				$this->node = $dom;

				return $dom;
			}
			else {
				throw new XmlParseException($filename, @file_get_contents($filename));
			}
		}

		/**
		 * Loads the specified string to this instance
		 * @param string $string The xml content
		 * @throws XmlParseException
		 * @return \SimpleXMLElement The object representing the document
		 */
		public function loadString($string) {
			$dom = @simplexml_load_string($string);
			if ($dom instanceof \SimpleXMLElement) {
				$this->node = $dom;

				return $dom;
			}
			else {
				throw new XmlParseException('', $string);
			}
		}

		/**
		 * Loads a DOMNode node
		 * @param \DOMNode $dom The DOMNode to load
		 * @throws XmlParseException
		 * @throws \DOMException
		 */
		public function loadDom(\DOMNode $dom) {
			$doc = new \DOMDocument();

			$node = simplexml_import_dom($doc->importNode($dom, true));

			if ($node) {
				$this->node = $node;

				return $node;
			}
			else {
				throw new XmlParseException('', $doc->saveXML(), 'Could not import DOM node to simple xml');
			}
		}

		/**
		 * Gets all children with specified tag name
		 * @param string $tagName The tag name
		 * @param \SimpleXMLElement|string|null $node The parent node (Either the object or the node path as string). If empty the current node of this parser instance will be used.
		 * @throws XmlNodeNotFoundException
		 * @return \SimpleXMLElement[] The child nodes
		 */
		public function getChildren($tagName, $node = null) {
			$ret = array();

			if ($node == null) {
				$node = $this->node;
			}
			elseif (is_string($node)) {
				$node = $this->getNode($node);
			}

			// check for namespace
			$nsSplit = explode(':', $tagName);
			if (count($nsSplit) == 2) {
				$node = $node->children($nsSplit[0], true);
				$tagName = $nsSplit[1];
			}

			if ($node) {
				$children = $node->children();
				foreach ($children as $curr) {

					/** @var \SimpleXMLElement $curr */
					if ($curr->getName() == $tagName) {
						$ret[] = $curr;
					}
				}
			}

			return $ret;
		}

		/**
		 * Gets all children with tag name matching specified regular expression
		 * @param string $tagNamePattern Regular expression pattern for matching tag name
		 * @param \SimpleXMLElement|string|null $node The parent node (Either the object or the node path as string). If empty the current node of this parser instance will be used.
		 * @throws XmlNodeNotFoundException
		 * @return \SimpleXMLElement[] The child nodes
		 */
		public function getChildrenMatch($tagNamePattern, $node = null) {
			$ret = array();

			if ($node == null) {
				$node = $this->node;
			}
			elseif (is_string($node)) {
				$node = $this->getNode($node);
			}

			$children = $node->children();
			foreach ($children as $curr) {
				/** @var \SimpleXMLElement $curr */
				if (preg_match($tagNamePattern, $curr->getName()) === 1) {
					$ret[] = $curr;
				}
			}

			return $ret;
		}

		/**
		 * Gets the node attributes
		 * @param \SimpleXMLElement|string|null $node The node to get attributes of (Either the object or the node path as string).
		 * If empty the current node of this parser instance will be used.
		 * @return string[] Array containing the attribute value (key is attribute name)
		 * @throws XmlNodeNotFoundException
		 */
		public function getAttributes($node = null) {
			$ret = array();

			if ($node == null) {
				$node = $this->node;
			}
			elseif (is_string($node)) {
				$node = $this->getNode($node);
			}

			foreach($node->attributes() as $name => $value) {
				$ret[$name] = $this->encodeForReturn($value);
			}

			return $ret;
		}

		/**
		 * Gets the value of an attribute
		 * @param string $name The attribute name
		 * @param \SimpleXMLElement|string|null $node The node to get the attribute of (Either the object or the node path as string).
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if attribute does not exist
		 * @param null $defaultValue The default value to use
		 * @throws XmlAttributeNotFoundException
		 * @throws XmlNodeNotFoundException
		 * @return string The attribute value
		 */
		public function getAttributeValue($name, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			if ($node == null) {
				$node = $this->node;
			}
			elseif (is_string($node)) {
				$node = $this->getNode($node);
			}

			$attributes = $this->getAttributes($node);

			if (!isset($attributes[$name])) {
				if (!$useDefaultValueInsteadOfException)
					throw new XmlAttributeNotFoundException($name, $node);
				else
					return $defaultValue;
			}

			return $attributes[$name];
		}

		/**
		 * Gets a node from DOM by it's path
		 * @param string $path The path. Use dot or "->" as separator. Empty string or single dot as path will return current node
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null $defaultValue The default value to use
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @throws XmlNodeNotFoundException
		 * @return \SimpleXMLElement The XML-node DOM object
		 */
		public function getNode($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			if ($node == null)
				$node = $this->node;

			if ($path == '' || $path == '.')
				return $node;

			$res  = $node;
			$path = str_replace('->', '.', $path);
			$p    = explode('.', $path);
			foreach ($p as $curr) {
				$nsSplit = explode(':', $curr);
				if (count($nsSplit) == 2) {
					$res  = $res->children($nsSplit[0], true);
					$curr = $nsSplit[1];
				}

				if (!empty($res) && isset($res->{$curr})) {
					$res = $res->{$curr};
				}
				else {
					if (!$useDefaultValueInsteadOfException)
						throw new XmlNodeNotFoundException($path, $node);
					else
						return $defaultValue;
				}
			}

			return $res;
		}

		/**
		 * Gets a node value by it's path
		 * @param string $path The path. Use dot or "->" as separator
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null $defaultValue The default value to use
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @throws XmlNodeNotFoundException
		 * @return string|mixed The XML-node DOM object
		 */
		public function getNodeValue($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			if ($node == null)
				$node = $this->node;

			$node = self::getNode($path, $useDefaultValueInsteadOfException, $defaultValue, $node);

			if ($node !== $defaultValue) {
				$node = $this->encodeForReturn($node);
			}

			return $node;
		}

		/**
		 * Gets a timestamp from a specified date node
		 * @param string $path The path. Use dot or "->" as separator
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null|mixed $defaultValue True if to use default value if node does not exist
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @return int|mixed|null
		 */
		public function getNodeValueTimestamp($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			$v = self::getNodeValue($path, $useDefaultValueInsteadOfException, $defaultValue, $node);

			if ($v !== $defaultValue)
				$v = strtotime($v);

			return $v;
		}

		/**
		 * Gets a timestamp from a specified date node
		 * @param string $path The path. Use dot or "->" as separator
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null|mixed $defaultValue True if to use default value if node does not exist
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @return int|mixed|\DateTime
		 */
		public function getNodeValueDateTime($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			$v = self::getNodeValue($path, $useDefaultValueInsteadOfException, $defaultValue, $node);

			if ($v !== $defaultValue)
				$v = new \DateTime($v);

			return $v;
		}

		/**
		 * Gets a boolean value from a specified date node.
		 * @param string $path The path. Use dot or "->" as separator
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null|mixed $defaultValue True if to use default value if node does not exist
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @return bool|mixed|null
		 */
		public function getNodeValueBool($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			$v = self::getNodeValue($path, $useDefaultValueInsteadOfException, $defaultValue, $node);

			if ($v !== $defaultValue) {
				$v = in_array(strtolower($v . ''), array('true', '1'));
			}

			return $v;
		}

		/**
		 * Gets a float value from a specified date node.
		 * @param string $path The path. Use dot or "->" as separator
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null|mixed $defaultValue True if to use default value if node does not exist
		 * @param null|string $decimalSeparator The decimal separator to use. If null parser's default separator will be used
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @return bool|mixed|null
		 */
		public function getNodeValueFloat($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $decimalSeparator = null, $node = null) {
			$v = self::getNodeValue($path, $useDefaultValueInsteadOfException, $defaultValue, $node);

			if (is_null($decimalSeparator))
				$decimalSeparator = $this->decimalSeparator;

			if ($v !== $defaultValue) {
				$v = trim(str_replace($decimalSeparator, '.', $v)) * 1;
			}

			return $v;
		}

		/**
		 * Gets a int value from a specified date node.
		 * @param string $path The path. Use dot or "->" as separator
		 * @param bool $useDefaultValueInsteadOfException True if to use default value if node does not exist
		 * @param null|mixed $defaultValue True if to use default value if node does not exist
		 * @param \SimpleXMLElement|null $node The parent node. If the current node of this parser instance will be used.
		 * @return bool|mixed|null
		 */
		public function getNodeValueInt($path, $useDefaultValueInsteadOfException = false, $defaultValue = null, $node = null) {
			$v = self::getNodeValue($path, $useDefaultValueInsteadOfException, $defaultValue, $node);


			if ($v !== $defaultValue) {
				$v = trim($v) * 1;
			}

			return $v;
		}

		/**
		 * Applies the set return encoding
		 * @param mixed $value The value to be encoded (will be cast as string)
		 * @return string The encoded string
		 */
		protected function encodeForReturn($value) {
			if ($this->returnCharset !== 'UTF-8')
				return mb_convert_encoding((string)$value, $this->returnCharset, 'UTF-8');
			else
				return (string)$value;
		}
	}