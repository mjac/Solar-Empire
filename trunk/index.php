<?php

require('inc/config.inc.php');

if (!class_exists('swDatabase')) {
	require(PATH_INC . '/db.inc.php');
}
$db = new swDatabase;
$db->start();

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

	$uQuery = $db->query('SELECT acc_id FROM [server]account WHERE acc_handle = %[1] AND acc_password = 0x' . sha256::hash($_REQUEST['password']),
	 $_REQUEST['handle']);
	if ($db->hasError($uQuery)) {
		$authProblem[] = 'existQuery';
	} elseif ($db->numRows($uQuery) < 1) {
		$authProblem[] = 'accountMissing';
		// Log invalid attempt
	} else {
		$accRow = $db->fetchRow($uQuery, ROW_NUMERIC);
		$accId = (double)$accRow[0];

		$updateQuery = $db->query('UPDATE [server]account SET acc_accessed = %[1], acc_accesses = acc_accesses + 1, acc_ip = %[2] WHERE acc_id = %[3]',
		 time(), $session->ipToUlong($session->ipAddress()), $accId);
		if (!$db->hasError($updateQuery)) {
			$session->create($accId);
    		header('Location: gamelisting.php');
			return;
		}
		// Record login
	}

	if (!empty($authProblem)) {
		$tpl->assign('authProblem', $authProblem);
	}
}

$tpl->display('index.tpl.php');

?>
