<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');
require_once('inc/template.inc.php');

$problems = array();

if (!(isset($_POST['handle']) && isset($_POST['name']) &&
	 isset($_POST['email']) && isset($_POST['email2']))) {
	$tpl->display('register.tpl.php');
	exit();
}

// check non-optionals
if (!valid_name($_POST['handle'])) {
	$problems[] = 'Invalid login name: 4-32 ASCII numeric, letters, or ' .
	 'punctuation characters';
}

if ($_POST['email'] !== $_POST['email2']) {
	$problems[] = 'The email addresses you entered did not match';
}

if (!(strlen($_POST['email']) < 65 && isEmailAddr($_POST['email']))) {
	$problems[] = 'Please enter a valid e-mail address';
}

if (!empty($problems)) {
	$tpl->assign('problems', $problems);
	$tpl->display('registration_problems.tpl.php');
	exit();
}

// check for existing username
$nameTaken = $db->query('SELECT COUNT(*) FROM user_accounts WHERE ' .
 'login_name = \'%s\'', array($db->escape($_POST['handle'])));

if (current($db->fetchRow($nameTaken)) > 0) {
	$problems[] = 'The account name is already taken';
}

// check for existing email_address
$emailUsage = $db->query('SELECT COUNT(*) FROM user_accounts WHERE ' .
 'email_address = \'%s\'', array($db->escape($_POST['email'])));

if (current($db->fetchRow($emailUsage)) > 0) {
	$problems[] = 'There is already an account with that email address';
}

if (!empty($problems)) {
	$tpl->assign('problems', $problems);
	$tpl->display('registration_problems.tpl.php');
	exit();
}

require_once('inc/external/sha256/sha256.class.php');
$password = create_rand_string(6);

$newId = newId('user_accounts', 'login_id');
$db->query('INSERT INTO user_accounts (login_id, login_name, passwd, ' .
 'signed_up, real_name, email_address) VALUES (%u, \'%s\', \'%s\', %u, ' .
 '\'%s\', \'%s\')', array($newId, $db->escape($_POST['handle']),
 $db->escape(sha256::hash($password)), time(), $db->escape($_POST['name']),
 $db->escape($_POST['email'])));

$location = URL_FULL;
$message = <<<END
SYSTEM WARS REGISTRATION
$location

You created a new account, here are the details.

Account name
	$_POST[handle]
Random password (change this after you sign-in)
	$password
Your name
	$_POST[name]

Welcome to the community; you can begin your adventure straight away.

You must sign-in once to stop your account being deleted in a few hours.
Do this now!
END;

mail("$_POST[name] <$_POST[email]>", "New account at $location", $message,
 "From: System Wars Mailer <game@$_SERVER[HTTP_HOST]>");

$tpl->display('registration_complete.tpl.php');
exit();

?>
