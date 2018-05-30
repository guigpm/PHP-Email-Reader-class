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
	private $__port   = 110;
	private $__type = 'pop3';

	function __construct($server, $user, $pass, $port = 110, $type = EmailType::POP) {
		$this->__server = $server;
		$this->__user = $user;
		$this->__pass = $pass;
		$this->__port = $port;
		$this->__type = $type;
	}

	public function get_conn_string() {
		return '{'
		.	$this->__server
		.	':'
		.	$this->__port
		.(	$this->__type
		?	'/' . $this->__type
		:	''
		)
		.	'}';
	}

	public function get_user() {
		return $this->__user;
	}

	public function get_pass() {
		return $this->__pass;
	}
}