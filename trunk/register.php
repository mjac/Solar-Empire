<?php

require('inc/config.inc.php');
require(PATH_INC . '/template.inc.php');
require(PATH_INC . '/input.class.php');

$input = new input;

// Not attempting to register yet
if (!$input->exists('handle', 'email', 'email2')) {
	$tpl->assign('serverRules', file_get_contents('inc/rules.tpl.html'));
	$tpl->display('register.tpl.php');
	return;
}

// Validate strings
$regProblem = array();
if (!preg_match('/[a-z0-9]{4,16}/i', $input->std['handle'])) {
	$regProblem[] = 'invalidHandle';
}

if (!(strlen($input->std['email']) <= 255 &&
     input::isEmail($input->std['email']))) {
	$regProblem[] = 'invalidEmail';
}

if ($input->std['email'] !== $input->std['email2']) {
	$regProblem[] = 'confirmEmail';
}

// Database checks on data
if (empty($regProblem)) {
	require(PATH_INC . '/db.inc.php');

	// Duplicate handle?
	$nameDup = $db->query('SELECT COUNT(*) FROM [server]account WHERE acc_handle = %[1]', $input->std['handle']);
	if ($db->hasError($nameDup)) {
	    $regProblem[] = 'queryHandle';
	} elseif (current($db->fetchRow($nameDup)) > 0) {
		$regProblem[] = 'duplicateHandle';
	}

	// Duplicate e-mail address?
	$emailDup = $db->query('SELECT COUNT(*) FROM [server]account WHERE acc_email = %[1]', $input->std['email']);
	if ($db->hasError($emailDup)) {
	    $regProblem[] = 'queryEmail';
	} elseif (current($db->fetchRow($emailDup)) > 0) {
		$regProblem[] = 'duplicateEmail';
	}

	// Finding the new account ID
	if (($newId = $db->newId('[server]account', 'acc_id')) === false) {
		$regProblem[] = 'queryId';
	}
}

// Send mail
if (empty($regProblem)) {
	if (!class_exists('sha256')) {
		require(PATH_LIB . '/sha256/sha256.class.php');
	}
	$password = input::randomStr(6); // Six alphanumeric chars

	$location = URL_FULL;

	$message = <<<END
SYSTEM WARS REGISTRATION
$location

You created a new account, here are the details.

Account name
	$input->std[handle]
Random password (change this to something memorable)
	$password

Welcome to the community; you can begin your adventures straight away.

Remember to sign in to your account to prevent it being deleted after a few hours.
END;

	$mailCompleted = @mail($input->std['email'], 'System Wars account created', $message,
	 "From: System Wars Server <noreply@$_SERVER[HTTP_HOST]>\r\nReply-To: System Wars Server <noreply@$_SERVER[HTTP_HOST]>");
	if (!$mailCompleted) {
	    $regProblem[] = 'accountMail';
	}
}

// Insert into database
if (empty($regProblem)) {
	$newAccount = $db->query('INSERT INTO [server]account (acc_id, acc_handle, acc_password, acc_created, acc_email) VALUES (%[1], %[2], 0x' .
	 sha256::hash($password) . ', %[3], %[4])', $newId, $input->std['handle'],
	 time(), $input->std['email']);
	if ($db->hasError($newAccount)) {
	    $regProblem[] = 'accountCreate';
	}
}

// Everything went fine OR...
if (empty($regProblem)) {
	$tpl->display('registered.tpl.php');
	return;
}

// ... show problems
$tpl->assign('serverRules', file_get_contents('inc/rules.tpl.html'));
$tpl->assign('regProblem', $regProblem);
$tpl->display('register.tpl.php');

?>
