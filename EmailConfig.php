<?php
namespace EmailReader;

/**
 * @author Guilherme Mattioli
 * @version 1.0
 */
class EmailConfig {

	private $__server = 'yourmailserver';
	private $__user   = 'youraccount';
	private $__pass   = 'yourpassword';
	private $__port   = null;
	private $__type = null;
	private $__inbox_folder = 'INBOX';

	function __construct($server, $user, $pass, $port = EmailPort::IMAP, $type = EmailType::IMAP, $inbox_folder = null) {
		$this->__server = $server;
		$this->__user = $user;
		$this->__pass = $pass;
		$this->__port = $port;

		if (in_array($type, EmailType::get_types())) {
			$this->__type = $type;
		}
		$this->set_inbox_folder($inbox_folder);
	}

	public function set_inbox_folder($inbox_folder) {
		if ($inbox_folder === null) return null;
		$this->__inbox_folder = $inbox_folder;
	}

	public function get_inbox_folder() {
		return $this->__inbox_folder;
	}

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