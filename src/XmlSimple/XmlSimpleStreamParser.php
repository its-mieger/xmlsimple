<?php

	namespace XmlSimple;

	class XmlSimpleStreamParser
	{
		private $uri;
		private $processPath = null;
		private $processFn;


		/**
		 * Creates a new instance
		 * @param string $uri Path to input file.
		 * @param string $processPath Path of the nodes to process (dot-notation).
		 * @param callable $processFn The function to be called on nodes to process
		 */
		public function __construct($uri, $processPath, $processFn) {
			$this->uri         = $uri;
			$this->processPath = $processPath;
			$this->processFn = $processFn;
		}

		/**
		 * Starts the streaming and parsing of the XML file
		 */
		public function parse() {
			$currTreePath = array();

			// create reader
			$reader = new \XMLReader();
			$reader->open($this->uri);

			// init parser for parsing filtered nodes
			$nodeParser = $this->initParser();
			$fn = $this->processFn;

			while ($reader->read()) {
				switch ($reader->nodeType) {
					case (\XMLReader::ELEMENT):
						// add path segment
						$currTreePath[] = $reader->name;

						// check if node to process
						if (implode('.', $currTreePath) == $this->processPath) {
							$nodeParser->loadString($reader->readOuterXml());
							$fn($nodeParser);
						}

						// remove path segment if empty node
						if (!$reader->hasValue && $reader->isEmptyElement)
							array_pop($currTreePath);

						break;
					case \XMLReader::END_ELEMENT:
						// remove current path segment
						array_pop($currTreePath);
				}
			}
		}

		/**
		 * Initializes a new parsers instance for node processing. This may be overwritten by derived classes
		 * to configure the parser instance.
		 * @return XmlSimpleParser The parser instance
		 */
		public function initParser() {
			return new XmlSimpleParser(null, 'UTF-8');
		}
	}