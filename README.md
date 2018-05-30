# What is this?
##### Base to access e-mail via IMAP in PHP

# How to?
	$server = 'yourmailserver';
	$user   = 'youraccount';
	$pass   = 'yourpassword';
	$port   = \EmailReader\EmailPort::POP;
	$type   = \EmailReader\EmailType::POP;

	$email = new \EmailReader\EmailReader(
		new \EmailReader\EmailConfig(
			$server, $user, $pass, $port, $type
		)
	);
	$inbox = $email->get_inbox();
	print_r($inbox);