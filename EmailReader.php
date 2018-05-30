<?php
namespace EmailReader;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailReader {

	public $conn = null;

	private $__inbox = array();
	private $__msgCnt = 0;
	private $__config = null;

	public function __construct(EmailConfig $config = null) {
		if ($config) {
			$this->set_config($config);
			$this->__init__();
		}

	}

	private function __init__() {
		if ($this->conn === null and $this->__config) {
			$this->__connect();
			$this->__inbox();
		}
	}

	public function set_config(EmailConfig $config) {
		$this->__config = $config;
	}

	/**
	 * @param None
	 * @return Void
	 * Closes the connection
	 */
	private function __close() {
		$this->__inbox = array();
		$this->__msgCnt = 0;

		if ($this->conn) {
			imap_close($this->conn);
		}
		$this->conn = null;
	}

	/**
	 * @param None
	 * @return Void
	 * Opens a connection
	 */
	private function __connect() {
		if (!$this->__config) return null;
		$this->conn = imap_open(
			$this->__config->get_conn_string(),
			$this->__config->get_user(),
			$this->__config->get_pass()
		);
	}

	/**
	 * @param int $msg_index Index of a message
	 * @param string $folder The name of a folder
	 * @return Void
	 * Moves a message to a new folder
	 */
	public function move($msg_index, $folder='INBOX.Processed') {
		$this->__init__();
		// move on server
		imap_mail_move($this->conn, $msg_index, $folder);
		imap_expunge($this->conn);

		// re-read the inbox
		$this->__inbox();
	}

	/**
	 * @param int $mgsIndex Index of a message
	 * @return array The associative array of a message
	 * Gets a specific message
	 */
	public function get($msg_index = null) {
		$this->__init__();
		if (count($this->__inbox) <= 0) {
			return array();
		}
		elseif ( ! is_null($msg_index) && isset($this->__inbox[$msg_index])) {
			return $this->__inbox[$msg_index];
		}

		return $this->__inbox[0];
	}

	/**
	 * @param None
	 * @return array The associative array of each message
	 * Reads the inbox
	 */
	private function __inbox() {
		if (!$this->conn) return null;
		$this->__msgCnt = imap_num_msg($this->conn);

		$in = array();
		for($i = 1; $i <= $this->__msgCnt; $i++) {
			$in[] = array(
				'index'     => $i,
				'header'    => imap_headerinfo($this->conn, $i),
				'body'      => imap_body($this->conn, $i),
				'structure' => imap_fetchstructure($this->conn, $i)
			);
		}

		$this->__inbox = $in;
	}

	/**
	 * @param None
	 * @return array The inbox associative array
	 * Gets the inbox associative array
	 */
	public function get_inbox() {
		$this->__init__();
		$inbox = $this->__inbox;
		return $inbox;
	}

}

