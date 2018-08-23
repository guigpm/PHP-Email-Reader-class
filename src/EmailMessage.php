<?php
namespace Mattioli\EmailReader;

use Mattioli\EmailReader\Exception\EmailResourceException;
use Mattioli\EmailReader\Exception\EmailException;
use Mattioli\EmailReader\Defs\EmailFolder;
use Mattioli\EmailReader\Defs\EmailDef;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailMessage extends EmailDef {
	public $uid = null;
	public $header = null;
	public $body = null;
	public $structure = null;

	private $__conn = null;

	public function __construct($conn, $index = 1) {
		if (!is_resource($conn)) {
			throw new EmailResourceException("conn is not a resource");
		}
		if (!is_integer($index)) {
			throw new EmailException("index is not a integer");
		}
		if ($index < 1) {
			throw new EmailException("index is less than 1");
		}
		$this->__conn = $conn;
		$this->uid = imap_uid($this->__conn, $index);
	}

	public function get_index() {
		return imap_msgno($this->__conn, $this->uid);
	}

	public function get_headers() {
		if ($this->header === null) {
			$this->header = imap_headerinfo($this->__conn, $this->get_index());
		}
		return $this->header;
	}

	public function get_structure() {
		if ($this->structure === null) {
			$this->structure = imap_fetchstructure(
				$this->__conn, $this->get_index()
			);
		}
		return $this->structure;
	}

	public function get_body() {
		if ($this->body === null) {
			$this->body = imap_body($this->__conn, $this->get_index());
		}
		return $this->body;
	}

	public function get_body_section($section) {
		return imap_fetchbody($this->__conn, $this->get_index(), $section);
	}

	public function get_all() {
		$this->get_headers();
		$this->get_structure();
		$this->get_body();
		return $this->jsonSerialize();
	}

	public function move_to($folder, $prefix = '.') {
		if ($prefix === '.') {
			$prefix = EmailFolder::INBOX . '.';
		}
		// move on server
		$moved = imap_mail_move(
			$this->__conn, $this->get_index(), $prefix . $folder
		);
		imap_expunge($this->__conn);
		return $moved;
	}

	public function delete() {
		// delete on server
		$deleted = imap_delete($this->__conn, $this->get_index());
		imap_expunge($this->__conn);
		return $deleted;
	}

	public function set_flag($flag) {
		// flag on server
		$flaged = imap_setflag_full($this->__conn, $this->get_index(), $flag);
		imap_expunge($this->__conn);
		return $flaged;
	}

	private function __format_email_array($header_field = 'to') {
		$headers = $this->get_headers();
		if (!isset($headers->$header_field)) {
			throw new EmailException("header_field is not a header");
		}

		$formated = [];
		if ($headers->$header_field and is_array($headers->$header_field)) {
			foreach ($headers->$header_field as $field) {
				if (!is_object($field)) continue;
				if (!isset($field->mailbox) or !isset($field->host)) continue;

				$obj = clone $field;
				if (!isset($obj->personal)) $obj->personal = '';

				$formated[] = (object) [
					'name' => trim($obj->personal),
					'email' => trim($obj->mailbox . '@' . $obj->host),
				];
			}

			EmailDef::__utf8_Converter($formated);
		}

		return $formated;
	}

	public function get_formated_from() {
		return $this->__format_email_array('from');
	}

	public function get_formated_to() {
		return $this->__format_email_array('to');
	}

	public function get_formated_reply_to() {
		return $this->__format_email_array('reply_to');
	}

	public function get_formated_sender() {
		return $this->__format_email_array('sender');
	}

}