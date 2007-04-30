<?php

require('inc/config.inc.php');
if (!class_exists('sda')) {
	require(PATH_INC . '/db.inc.php');
}
if (!class_exists('session')) {
	require(PATH_INC . '/session.class.php');
}
require(PATH_INC . '/template.inc.php');

// Send to game listing if already logged in
$session = new session($db);
if ($session->authenticated()) {
	header('Location: ' . URL_FULL . '/game_listing.php');
	return;
}

// User logging into server
if (isset($_REQUEST['handle']) && isset($_REQUEST['password'])) {
	$uQuery = $db->query('SELECT login_id, passwd, login_count FROM user_accounts WHERE login_name = \'%[1]\'', 
	 $handle);
	$userInfo = $db->fetchRow($uQuery);

	$problems = array();

	if (empty($userInfo)) { // Incorrect username
		$problems[] = 'That user does not exist on this Server: either you ' .
		 'typed in your user name wrong or your account no longer exists';

		$tpl->assign('problems', $problems);
		$tpl->display('login_problems.tpl.php');
		exit;
	}

	if ($password !== $userInfo['passwd']) { // Incorrect password
		insert_history($userInfo['login_id'], 'Login attempt failed');
		$problems[] = 'The password is incorrect';

		$tpl->assign('problems', $problems);
		$tpl->display('login_problems.tpl.php');
		exit;
	}

	if (!class_exists('sha256')) {
		require(PATH_LIB . '/sha256/sha256.class.php');
	}
	$password = sha256::hash($password);

	$db->query('UPDATE user_accounts SET last_login = %[1], session_id = \'%[2]\', session_exp = %[3], last_ip = \'%[4]\', login_count = login_count + 1 WHERE login_id = %[5]', 
	 time(), $session, $expires, $_SERVER['REMOTE_ADDR'], 
	 $userInfo['login_id']);

	insert_history($userInfo['login_id'], 'Logged into game-list');

    header('Location: game_listing.php');
	return;
}

// Display default page
$tpl->display('index.tpl.php');

?>
