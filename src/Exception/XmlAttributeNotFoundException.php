<?php
	namespace XmlSimple\Exception;

	use Exception;
	use SimpleXMLElement;

	class XmlAttributeNotFoundException extends Exception
	{
		private $xml;
		private $dom;
		private $attributeName;

		/**
		 * Creates a new instance.
		 * @param string $attributeName The attribute name
		 * @param SimpleXMLElement $dom The dom object
		 */
		public function __construct($attributeName, SimpleXMLElement $dom) {
			parent::__construct('XML attribute ' . $attributeName . ' not found');

			$this->xml      = ((!empty($dom)) ? $dom->asXml() : null);
			$this->dom      = $dom;
			$this->attributeName = $attributeName;
		}

		public function getDom() {
			return $this->dom;
		}

		public function getAttributeName() {
			return $this->attributeName;
		}

		public function getXml() {
			return $this->xml;
		}
	}