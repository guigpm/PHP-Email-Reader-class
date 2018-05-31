<?php require_once(__DIR__ . '/test/bootstrap.php');

	$server = 'yourmailserver';
	$user   = 'youraccount';
	$pass   = 'yourpassword';
	$port   = \Mattioli\EmailReader\Defs\EmailPort::POP;
	$type   = \Mattioli\EmailReader\Defs\EmailType::POP;

	$email = new \Mattioli\EmailReader\EmailReader(
		new \Mattioli\EmailReader\EmailConfig(
			$server, $user, $pass, $port, $type
		)
	);
	$inbox = $email->get_inbox();
	print_r($inbox);