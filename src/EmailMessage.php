<?php
namespace Mattioli\EmailReader;

use Mattioli\EmailReader\Exception\EmailResourceException;
use Mattioli\EmailReader\Exception\EmailException;
use Mattioli\EmailReader\Defs\EmailFolder;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailMessage implements \JsonSerializable {
	public $index = 1;
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
		$this->index = $index;
	}

	public function get_headers() {
		if ($this->header === null) {
			$this->header = imap_headerinfo($this->__conn, $this->index);
		}
		return $this->header;
	}

	public function get_structure() {
		if ($this->structure === null) {
			$this->structure = imap_fetchstructure($this->__conn, $this->index);
		}
		return $this->structure;
	}

	public function get_body() {
		if ($this->body === null) {
			$this->body = imap_body($this->__conn, $this->index);
		}
		return $this->body;
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
			$this->__conn, $this->index, $prefix . $folder
		);
		imap_expunge($this->__conn);
		return $moved;
	}

	public function delete() {
		// delete on server
		$deleted = imap_delete($this->__conn, $this->index);
		imap_expunge($this->__conn);
		return $deleted;
	}

	public function set_flag($flag) {
		// flag on server
		$flaged = imap_setflag_full($this->__conn, $this->index, $flag);
		imap_expunge($this->__conn);
		return $flaged;
	}

	public function jsonSerialize() {
		$oClass = new \ReflectionClass(get_called_class());
		$props = $oClass->getProperties(
			\ReflectionProperty::IS_PUBLIC
		// |	\ReflectionProperty::IS_PROTECTED
		);

		$dados = [];
		foreach ($props as $prop) {
			$dados[$prop->name] = $this->{$prop->name};
		}
		return $dados;
	}

}