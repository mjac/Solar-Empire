<?php

require('inc/admin.inc.php');
require('inc/template.inc.php');

// Changes when the game ends
if (isset($finishes)) {
	$match = array();
	if (preg_match('/^([12][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|' .
	 '3[01]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $finishes,
	 $match)) {
		$newEnd = mktime($match[4], $match[5], $match[6], $match[2],
		 $match[3], $match[1]);

		$db->query('UPDATE se_games SET finishes = %[1] WHERE db_name = \'[game]\'', $newEnd);

		$out .= "<p>Game finishing date changed to " .
		 date('Y-m-d H:i:s', $newEnd) . "</p>\n";
	} else {
		$out .= "<p>Invalid format for the date: use YYYY-MM-DD HH:MM:SS</p>\n";
	}
}


// Change game status
if (isset($status)) {
	$status = strtolower($status);
	switch ($status) {
		case 'paused':
		case 'running':
			post_news("Game $status");
		case 'hidden':
			$db->query('UPDATE se_games SET status = \'%[1]\', processed_cleanup = %[2], processed_systems = %[2], processed_turns = %[2], processed_ships = %[2], processed_planets = %[2], processed_government = %[2] WHERE db_name = \'[game]\'', $status, time());
			insert_history($user['login_id'], "Changed status to $status.");
	}
}


// Change introduction message
$tpl->assign('gameIntroduction', $gameInfo['intro_message']);
if (isset($introduction)) {
	$db->query('UPDATE se_games SET intro_message = \'%[1]\' WHERE db_name = \'[game]\'',
	 $introduction);
	$out .= "<p>The introduction message has been changed.</p>\n";

	print_page('Change the introduction message', $out);
	insert_history($user['login_id'], 'Changed the introduction message.');
}

// Change game description
$tpl->assign('gameDescription', $gameInfo['description']);
if (isset($description)) {
	$db->query('UPDATE se_games SET description = \'%[1]\' WHERE db_name = \'[game]\'',
	 $description);
	$out .= "<p>The introduction message has been changed.</p>\n";

	insert_history($user['login_id'], 'Changed the game description.');
}


assignCommon($tpl);
$tpl->display('game/admin/settings.tpl.php');

?>
