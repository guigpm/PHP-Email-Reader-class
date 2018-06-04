<?php
namespace Mattioli\EmailReader;

use Mattioli\EmailReader\Defs\EmailPort;
use Mattioli\EmailReader\Defs\EmailType;
use Mattioli\EmailReader\Defs\ReadType;
use Mattioli\EmailReader\Defs\EmailFolder;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailConfig {

	private $__server = 'yourmailserver';
	private $__user = 'youraccount';
	private $__pass = 'yourpassword';
	private $__port = EmailPort::POP;
	private $__type = EmailType::POP;
	private $__read_type = ReadType::ALL;
	private $__inbox_folder = EmailFolder::INBOX;

	function __construct(
		$server,
		$user,
		$pass,
		$port = EmailPort::POP,
		$type = EmailType::POP,
		$inbox_folder = null,
		$read_type = ReadType::ALL
	) {
		$this->__server = $server;
		$this->__user = $user;
		$this->__pass = $pass;
		$this->__port = $port;

		$this->set_email_type($type);
		$this->set_read_type($read_type);
		$this->set_inbox_folder($inbox_folder);
	}

	public function set_inbox_folder($inbox_folder) {
		if ($inbox_folder === null) return null;
		$this->__inbox_folder = $inbox_folder;
	}

	public function get_inbox_folder() {
		return $this->__inbox_folder;
	}

	// --

	public function set_read_type($read_type) {
		if (in_array($read_type, ReadType::get_types())) {
			$this->__read_type = $read_type;
		}
	}

	public function get_read_type() {
		return $this->__read_type;
	}

	// --

	public function set_email_type($type) {
		if (in_array($type, EmailType::get_types())) {
			$this->__type = $type;
		}
	}

	public function get_email_type() {
		return $this->__type;
	}

	// --

	public function get_conn_string() {
		return '{'
		.	"{$this->__server}:{$this->__port}"
		.(	$this->__type
		?	'/' . $this->__type
		:	''
		)
		.	'}'
		.	$this->__inbox_folder
		;
	}

	public function get_user() {
		return $this->__user;
	}

	public function get_pass() {
		return $this->__pass;
	}
}