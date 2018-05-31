<?php require_once('bootstrap.php'); ?>
<!doctype html>
<html>
<head>
	<title></title>
	<meta charset="utf-8" />
</head>
<body>
<?php
	$server = 'yourmailserver';
	$user   = 'youraccount';
	$pass   = 'yourpassword';
	$port   = \Mattioli\EmailReader\EmailPort::POP;
	$type   = \Mattioli\EmailReader\EmailType::POP;

	$email = new \Mattioli\EmailReader\Mattioli\EmailReader(
		new \Mattioli\EmailReader\EmailConfig(
			$server, $user, $pass, $port, $type
		)
	);
	$inbox = $email->get_inbox();
	print_r($inbox);
?>
</body>
</html>