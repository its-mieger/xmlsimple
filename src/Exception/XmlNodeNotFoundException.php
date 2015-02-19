<?php
	namespace XmlSimple\Exception;

	use Exception;
	use SimpleXMLElement;

	class XmlNodeNotFoundException extends Exception
	{
		private $xml;
		private $dom;
		private $nodePath;

		/**
		 * Creates a new instance.
		 * @param string $nodePath The node path as string
		 * @param SimpleXMLElement $dom The dom object
		 */
		public function __construct($nodePath, SimpleXMLElement $dom) {
			parent::__construct('XML node ' . $nodePath . ' not found');

			$this->xml      = ((!empty($dom)) ? $dom->asXml() : null);
			$this->dom      = $dom;
			$this->nodePath = $nodePath;
		}

		public function getDom() {
			return $this->dom;
		}

		public function getNodePath() {
			return $this->nodePath;
		}

		public function getXml() {
			return $this->xml;
		}
	}