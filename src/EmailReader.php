<?php
namespace Mattioli\EmailReader;

use Mattioli\EmailReader\Defs\EmailDef;
use Mattioli\EmailReader\Defs\EncodeType;
use Mattioli\EmailReader\Defs\ReadType;
use Mattioli\EmailReader\Defs\StructureType;
use Mattioli\EmailReader\EmailConfig;
use Mattioli\EmailReader\Exception\EmailResourceException;
use Mattioli\EmailReader\Exception\EmailException;
use Mattioli\EmailReader\EmailMessage;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailReader extends EmailDef {

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

	private function __format_email_array($msg, $header_field = 'to') {
		$call = "get_formated_{$header_field}";
		return $msg->$call();
	}

	public function get_formated_from($msg_index = 0) {
		return $this->__action($msg_index, function ($msg) {
			return $this->__format_email_array($msg, 'from');
		});
	}

	public function get_formated_to($msg_index = 0) {
		return $this->__action($msg_index, function ($msg) {
			return $this->__format_email_array($msg, 'to');
		});
	}

	public function get_formated_reply_to($msg_index = 0) {
		return $this->__action($msg_index, function ($msg) {
			return $this->__format_email_array($msg, 'reply_to');
		});
	}

	public function get_formated_sender($msg_index = 0) {
		return $this->__action($msg_index, function ($msg) {
			return $this->__format_email_array($msg, 'sender');
		});
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
			$moved = $msg->move_to($folder, '');

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
	 * @return array The inbox associative array
	 */
	public function get_inbox() {
		$this->__init__();
		$inbox = $this->__inbox;
		return $inbox;
	}

	/**
	 * [decode_email_string description]
	 * @param  [type]  $string  [description]
	 * @param  string  $charset [description]
	 * @param  boolean $trim    [description]
	 * @return [type]           [description]
	 */
	public function decode_email_string(
		$string, $charset = 'UTF-8', $trim = true
	) {
		$resp = ($trim ? trim($string) : $string);
		if (preg_match("/=\?/", $resp)) {
			$resp = iconv_mime_decode($resp, 0, $charset);
		} else {
			$resp = imap_utf8($resp);
		}
		$this->__utf8_Converter($resp);
		$resp = quoted_printable_decode($resp);
		$this->__utf8_Converter($resp);
		return $resp;
	}

	/**
	 * [get_full_structure description]
	 * @param  integer $msg_index [description]
	 * @param  string  $prefix    [description]
	 * @return [type]             [description]
	 */
	public function get_full_structure($msg_index = 0, $prefix = "") {
		return $this->__action($msg_index, function ($msg) use ($prefix) {
			return $this->__create_part_array($msg->get_structure(), $prefix);
		});
	}

	/**
	 * [__is_valid_base64 description]
	 * @param  [type]  $str [description]
	 * @return boolean      [description]
	 */
	private function __is_valid_base64($str) {
	   if (!is_string($str)) return false;
	    $_str = preg_replace("/\s+/", "", $str);
	    $decode = base64_decode($_str, true);
	    if (!$decode) return false;
	    $_b64 = base64_encode($decode);
	    return ($_b64 === $_str);
	}

	/**
	 * [get_body_by_structure description]
	 * @param  integer $msg_index      [description]
	 * @param  StructureType  $structure_part [description]
	 * @return mixed                  [description]
	 */
	public function get_body_by_structure(
		$msg_index = 0, $structure_part = StructureType::HTML
	) {
		if (!in_array($structure_part, StructureType::get_types())) {
			throw new EmailException("structure_part is not a StructureType");
		}

		$structure = $this->get_full_structure($msg_index);
		if ($structure) foreach ($structure as $key => $part) {
			if (strtoupper($part->part_object->subtype) == $structure_part) {
				$body = $this->__inbox[$msg_index]->get_body_section(
					$part->part_number
				);

				if (isset($part->part_object->encoding))
				switch ($part->part_object->encoding) {
					case EncodeType::BASE64:
						if ($this->__is_valid_base64($body)) {
							$body = preg_replace("/\s+/", "", $body);
							$body = base64_decode($body);
						}
						break;

					case EncodeType::PLAIN:
					default:
						break;
				}
				return $body;
			}
		}
		return null;
	}

	/**
	 * imap-fetchbody() will decode attached email messages inline with the
	 * rest of the email parts, however the way it works when handling attached
	 * email messages is inconsistent with the main email message.
	 *
	 * With an email message that only has a text body and does not have any
	 * mime attachments, imap-fetchbody() will return the following for each
	 * requested part number:
	 *
	 * (empty) - Entire message
	 * 0 - Message header
	 * 1 - Body text
	 *
	 * With an email message that is a multi-part message in MIME format, and
	 * contains the message text in plain text and HTML, and has a file.ext
	 * attachment, imap-fetchbody() will return something like the following
	 * for each requested part number:
	 *
	 * (empty) - Entire message
	 * 0 - Message header
	 * 1 - MULTIPART/ALTERNATIVE
	 * 1.1 - TEXT/PLAIN
	 * 1.2 - TEXT/HTML
	 * 2 - file.ext
	 *
	 * Now if you attach the above email to an email with the message text in
	 * plain text and HTML, imap_fetchbody() will use this type of part number
	 * system:
	 *
	 * (empty) - Entire message
	 * 0 - Message header
	 * 1 - MULTIPART/ALTERNATIVE
	 * 1.1 - TEXT/PLAIN
	 * 1.2 - TEXT/HTML
	 * 2 - MESSAGE/RFC822 (entire attached message)
	 * 2.0 - Attached message header
	 * 2.1 - TEXT/PLAIN
	 * 2.2 - TEXT/HTML
	 * 2.3 - file.ext
	 *
	 * Note that the file.ext is on the same level now as the plain text and
	 * HTML, and that there is no way to access the MULTIPART/ALTERNATIVE in
	 * the attached message.
	 *
	 * Here is a modified version of some of the code from previous posts that
	 * will build an easily accessible array that includes accessible attached
	 * message parts and the message body if there aren't multipart mimes.
	 *
	 * @param $structure The $structure variable is the output of the
	 * imap_fetchstructure() function.
	 *
	 * @return array The returned $part_array has the field 'part_number' which
	 * contains the part number to be fed directly into the imap_fetchbody()
	 * function.
	 */

	private function __create_part_array(\stdClass $structure, $prefix = "") {
		$part_array = [];
		if (isset($structure->parts) and $structure->parts) {
			// There some sub parts
			foreach ($structure->parts as $count => $part) {
				$this->__add_part_to_array(
					$part, $prefix . ($count + 1), $part_array
				);
			}
		} else {
			// Email does not have a seperate mime attachment for text
			$part_array[] = (object) array(
				'part_number' => $prefix . '1',
				'part_object' => $structure
			);
		}
		return $part_array;
	}

	/**
	 * Sub function for __create_part_array().
	 * Only called by __create_part_array() and itself.
	 */
	private function __add_part_to_array(
		\stdClass $obj, $partno, &$part_array
	) {
		$part_array[] = (object) array(
			'part_number' => $partno,
			'part_object' => $obj
		);
		if (isset($obj->type) and $obj->type == 2) {
			// Check to see if the part is an attached email message, as in the
			// RFC-822 type
			if (isset($obj->parts) and $obj->parts) {
				// Check to see if the email has parts
				foreach ($obj->parts as $count => $part) {
					// Iterate here again to compensate for the broken way that
					// imap_fetchbody() handles attachments
					if (isset($part->parts) and $part->parts == 2) {
						foreach ($part->parts as $count2 => $part2) {
							$this->__add_part_to_array(
								$part2,
								$partno . "." . ($count2 + 1),
								$part_array
							);
						}
					} else {
						// Attached email does not have a seperate mime
						// attachment for text
						$part_array[] = (object) array(
							'part_number' => $partno . '.' . ($count + 1),
							'part_object' => $obj
						);
					}
				}
			} else {
				// Not sure if this is possible
				$part_array[] = (object) array(
					'part_number' => $prefix . '.1',
					'part_object' => $obj
				);
			}
		} else {
			// If there are more sub-parts, expand them out.
			if (isset($obj->parts) and $obj->parts) {
				foreach ($obj->parts as $count => $p) {
					$this->__add_part_to_array(
						$p, $partno . "." . ($count + 1), $part_array
					);
				}
			}
		}
	}

}

