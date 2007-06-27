<?php

require('inc/config.inc.php');
require(PATH_INC . '/template.inc.php');
require(PATH_INC . '/input.class.php');

$input = new input;
$input->parse();

if (!(isset($_REQUEST['handle']) && isset($_REQUEST['name']) &&
	 isset($_REQUEST['email']) && isset($_REQUEST['email2']))) {
	$tpl->assign('serverRules', file_get_contents('inc/rules.tpl.html'));
	$tpl->display('register.tpl.php');
	exit;
}

$regProblem = array();
if (!preg_match('/[a-z0-9]{4,16}/i', $_REQUEST['handle'])) {
	$regProblem[] = 'handle';
}

if ($_REQUEST['email'] !== $_REQUEST['email2']) {
	$regProblem[] = 'emailConfirm';
}

if (!(strlen($_REQUEST['email']) < 65 && isEmailAddr($_REQUEST['email']))) {
	$regProblem[] = 'Please enter a valid e-mail address';
}

$password = '';

if (empty($regProblem)) {
	require(PATH_INC . '/db.inc.php');

	// Duplicate handle?
	$nameDup = $db->query('SELECT COUNT(*) FROM [server]account WHERE acc_handle = %[1]', $_REQUEST['handle']);
	if ($db->hasError($nameDup)) {
	    $regProblem[] = 'handleQuery';
	} elseif (current($db->fetchRow($nameDup)) > 0) {
		$regProblem[] = 'handleDuplicate';
	}

	// Duplicate e-mail address?
	$emailDup = $db->query('SELECT COUNT(*) FROM [server]account WHERE acc_email = %[1]', $_REQUEST['email']);
	if ($db->hasError($emailDup)) {
	    $regProblem[] = 'emailQuery';
	} elseif (current($db->fetchRow($emailDup)) > 0) {
		$regProblem[] = 'emailDuplicate';
	}

	if (($newId = $db->newId('[server]account', 'acc_id')) === false) {
		$regProblem[] = 'newId';
	}
}

if (!empty($regProblem)) {
	$tpl->assign('serverRules', file_get_contents('inc/rules.tpl.html'));
	$tpl->assign('regProblem', $regProblem);
	$tpl->display('register.tpl.php');
	exit;
}

if (!class_exists('sha256')) {
	require(PATH_LIB . '/sha256/sha256.class.php');
}

$db->query('INSERT INTO [server]account (acc_id, acc_handle, acc_password, acc_created, acc_email) VALUES (%[1], %[2], 0x' .
 sha256::hash($password) . ', %[3], %[4])', $newId, $_REQUEST['handle'],
 time(), $_REQUEST['email']);

$location = URL_FULL;

$message = <<<END
SYSTEM WARS REGISTRATION
$location

You created a new account, here are the details.

Account name
	$_REQUEST[handle]
Random password (change this to something memorable)
	$password

Welcome to the community; you can begin your adventures straight away.

Remember to sign in to your account to prevent it being deleted after a few hours.
END;

mail($_REQUEST['email'], "New account at $location", $message,
 "From: System Wars Mailer <game@$_SERVER[HTTP_HOST]>");

$tpl->display('registered.tpl.php');
exit;

?>
