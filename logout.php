<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

if (checkAuth()) {
	if (isset($logout_single_game) || isset($comp_logout)) {
		if (isset($logout_single_game) && $db_name === NULL) {
			header('Location: game_listing.php');
			exit();
		}

		//Update score, and last_request
		score_func($login_id,0);

		$time_to_set = time() - 1800; //30 mins ago
		$db->query('UPDATE [game]_users SET last_request = %u WHERE ' .
		 'login_id = %u', array($time_to_set, $login_id));

		//only logging out to gamelisting
		if (isset($logout_single_game)) {
			insert_history($login_id, 'Logged out of ' . $db_name);
			$db->query('UPDATE user_accounts SET in_game = NULL WHERE ' .
			 'login_id = %u', array($login_id));
			header('Location: game_listing.php');
			exit();
		}
	}

	insert_history($login_id, 'Logged out completely');

	//unset session details.
	$db->query('UPDATE user_accounts SET session_id = \'\', ' .
	 'session_exp = 0, in_game = NULL WHERE login_id = %u',
	 array($login_id));
}

setcookie('session_id', '', 0);
setcookie('login_id', '', 0);

header('Location: index.php');
exit();

?>
