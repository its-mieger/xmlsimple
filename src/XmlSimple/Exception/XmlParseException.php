<?php

	namespace XmlSimple\Exception;

	use Exception;

	/**
	 * Thrown if XML file could not be parsed
	 * Class ParseXmlException
	 * @package Exception
	 */
	class XmlParseException extends \Exception {

		protected $filename;
		protected $content;

		/**
		 * Creates a new instance
		 * @param string $filename The file name
		 * @param string $content The file content
		 * @param string $message The message
		 * @param int $code The error code
		 * @param Exception $previous Previous exception
		 */
		public function __construct($filename, $content='', $message = "", $code = 0, Exception $previous = null) {
			$this->file = $filename;
			$this->content = '';

			if (empty($message))
				$message = 'Could not parse "' . $filename . '".';

			parent::__construct($message, $code, $previous);
		}

		public function getFilename() {
			return $this->file;
		}

		public function getContent() {
			return $this->getContent();
		}

	}