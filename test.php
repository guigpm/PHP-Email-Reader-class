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
	$port   = \EmailReader\EmailPort::POP;
	$type   = \EmailReader\EmailType::POP;

	$email = new \EmailReader\EmailReader(
		new \EmailReader\EmailConfig(
			$server, $user, $pass, $port, $type
		)
	);
	$inbox = $email->get_inbox();
	print_r($inbox);
?>
</body>
</html>