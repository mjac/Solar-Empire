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
	header('Location: ' . URL_FULL . '/gamelisting.php');
	return;
}

// User logging into server -> to go into login.php
if (isset($_REQUEST['handle']) && isset($_REQUEST['password'])) {
	if (!class_exists('sha256')) {
		require(PATH_LIB . '/sha256/sha256.class.php');
	}

	$uQuery = $db->query('SELECT acc_id FROM [server]account WHERE acc_handle = %[1] AND acc_password = 0x' . 
	 sprintf('%X', sha256::hash($_REQUEST['password'])), $handle);
	if ($db->hasError($uQuery)) {
		$authProblem[] = 'existQuery';
	} elseif ($db->numRows($uQuery) < 1) {
		$authProblem[] = 'accountMissing';
		//insert_history($userInfo['login_id'], 'Login attempt failed');
	} else {
		$accId = (double)$db->fetchField($uQuery);

		$updateQuery = $db->query('UPDATE [server]account SET acc_accessed = %[1], acc_accesses = acc_accesses + 1, acc_ip = %[4] WHERE acc_id = %[5]', 
		 time(), $session->ipToUlong($session->ipAddress()), $accId);
		if (!$db->hasError($updateQuery)) {
			$session->switchAccount($accId);
    		header('Location: gamelisting.php');
			return;
		}
		//insert_history($userInfo['login_id'], 'Authenticated');
	}

	if (!empty($authProblem)) {
		$tpl->assign('authProblem', $authProblem);
	}
}

$tpl->display('index.tpl.php');

?>
