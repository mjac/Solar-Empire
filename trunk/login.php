<?php

require('inc/config.inc.php');
require(PATH_INC . '/state/guest.inc.php');

$authProblem = array();

// User logging into server
if ($input->exists('handle', 'password')) {
	if (!class_exists('sha256')) {
		require(PATH_LIB . '/sha256/sha256.class.php');
	}

	$uQuery = $db->query('SELECT acc_id FROM [server]account WHERE acc_handle = %[1] AND acc_password = 0x' . sha256::hash($input->std['password']),
	 $input->std['handle']);
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
    		header('Location:  ' . URL_BASE . '/gamelisting.php');
			return;
		}
		// Record login
	}

}

require(PATH_INC . '/template.inc.php');

if (!empty($authProblem)) {
	$tpl->assign('authProblem', $authProblem);
}

$tpl->display('index.tpl.php');

?>
