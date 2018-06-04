<?php
namespace Mattioli\EmailReader;

use Mattioli\EmailReader\EmailConfig;
use Mattioli\EmailReader\Exception\EmailResourceException;
use Mattioli\EmailReader\Exception\EmailException;
use Mattioli\EmailReader\Defs\ReadType;
use Mattioli\EmailReader\EmailMessage;

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
			$this->__connect()->__inbox();
		}
	}

	public function set_config(EmailConfig $config) {
		$this->__config = $config;
	}

	/**
	 * Closes the connection
	 *
	 * @param None
	 * @return Void
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
	 * Opens a connection
	 *
	 * @param None
	 * @return Void
	 */
	private function __connect() {
		if (!$this->__config) return null;

		try {
			$this->conn = imap_open(
				$this->__config->get_conn_string(),
				$this->__config->get_user(),
				$this->__config->get_pass()
			);
		} catch (\Exception $e) {
			throw new EmailException("{$e}", $e->getCode(), $e);
		}

		if (!$this->conn) {
			throw new EmailException(
				"Não foi possível conectar com "
				. $this->__config->get_conn_string()
			);
		}
		return $this;
	}

	private function __action($msg_index, $callback) {
		if (!$this->__config) return null;
		if (!is_integer($msg_index)) {
			throw new EmailException("index is not a integer");
		}
		if ($msg_index < 0) {
			throw new EmailException("index is less than 0");
		}
		$this->__init__();
		if (count($this->__inbox) <= 0) {
			return null;
		} elseif (!is_null($msg_index) and isset($this->__inbox[$msg_index])) {
			$msg = $this->__inbox[$msg_index];
			return $callback($msg);
		}

		return null;
	}

	/**
	 * Moves a message to a new folder
	 *
	 * @param int $msg_index Index of a message
	 * @param string $folder The name of a folder
	 * @return boolean If was moved
	 */
	public function move($msg_index, $folder = null) {
		return $this->__action($msg_index, function ($msg) use ($folder) {
			if ($folder === null) {
				$folder = $this->__config->get_inbox_folder() . '.Processed';
			} elseif ($folder === false) {
				$folder = $this->__config->get_inbox_folder();
			}
			$moved = $msg->move_to($folder);

			// re-read the inbox
			$this->__inbox();
			return $moved;
		});
	}

	/**
	 * Gets a specific message
	 *
	 * @param int $mgsIndex Index of a message
	 * @return array The associative array of a message
	 */
	public function get($msg_index = 0) {
		return $this->__action($msg_index, function ($msg) {
			return $msg->get_all();
		});
	}

	/**
	 * Delete a specific message
	 *
	 * @param int $mgsIndex Index of a message
	 * @return boolean If was deleted
	 */
	public function delete($msg_index = 0) {
		return $this->__action($msg_index, function ($msg) {
			$deleted = $msg->delete();

			// re-read the inbox
			$this->__inbox();
			return $deleted;
		});
	}

	/**
	 * Reads the inbox
	 *
	 * @param None
	 * @return array The associative array of each message
	 */
	private function __inbox() {
		if (!$this->conn) return null;
		$this->__msgCnt = imap_num_msg($this->conn);

		$this->__inbox = array();
		for ($i = 1; $i <= $this->__msgCnt; $i++) {
			$mail = new EmailMessage($this->conn, $i);
			$r_type = $this->__config->get_read_type();
			if ($r_type !== ReadType::ALL) {
				$headers = $mail->get_headers();
				if ($headers->Unseen !== $r_type) {
					continue;
				}
			}

			$this->__inbox[] = $mail;
		}
		return $this;
	}

	/**
	 * Gets the inbox associative array
	 *
	 * @param None
	 * @return array The inbox associative array
	 */
	public function get_inbox() {
		$this->__init__();
		$inbox = $this->__inbox;
		return $inbox;
	}

	public function decode_email_string($string, $charset = 'UTF-8', $trim = true) {
		$resp = ($trim ? trim($string) : $string);
		if (preg_match("/=\?/", $resp)) {
			$resp = iconv_mime_decode($resp, 0, $charset);
		} else {
			$resp = imap_utf8($resp);
		}
		if (json_encode($resp) === false) {
			$resp = utf8_encode($resp);
		}
		return $resp;
	}

}

