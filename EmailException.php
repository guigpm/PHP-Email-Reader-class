<?php
namespace EmailReader;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailException extends \Exception {

	private $imap_last_error = null;

	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0, Exception $previous = null) {
		$this->imap_last_error = imap_last_error();

		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n"
		. print_r($this->imap_last_error, true);
	}
}