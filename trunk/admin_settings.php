<?php

require('inc/admin.inc.php');
require('inc/template.inc.php');

$changed = array();

// Changes when the game ends
$finMatch = array();
if (isset($finishes) && preg_match('/^([12][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|3[01]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/',
     $finishes, $finMatch)) {
	$changed['finishes'] = false;

	$newEnd = mktime($finMatch[4], $finMatch[5], $finMatch[6], $finMatch[2],
	 $finMatch[3], $finMatch[1]);

	if ($newEnd !== -1 && $newEnd !== false) { // php > < 5.1
		$finQuery = $db->query('UPDATE se_games SET finishes = %[1] WHERE db_name = \'[game]\'',
		 $newEnd);
		if ($db->affectedRows($finQuery) > 0) {
			$changed['finishes'] = $newEnd;
		}
	}
}


// Change game status
if (isset($status)) {
	$changed['status'] = false;
	$status = strtolower($status);
	$sExtra = '';

	switch ($status) {
		case 'paused':
		case 'running':
			post_news("Game $status");
			$sExtra = ', processed_cleanup = %[2], processed_systems = %[2], processed_turns = %[2], processed_ships = %[2], processed_planets = %[2], processed_government = %[2]';
		case 'hidden':
			$statQuery = $db->query('UPDATE se_games SET status = \'%[1]\'' .
			 $sExtra . ' WHERE db_name = \'[game]\'', $status, time());

			if ($db->affectedRows($statQuery) > 0) {
				insert_history($user['login_id'], "Changed status to $status.");
				$changed['status'] = $status;
			}
	}
}


// Change introduction message
$tpl->assign('gameIntroduction', $gameInfo['intro_message']);
if (isset($introduction)) {
	$changed['introduction'] = false;

	$introQuery = $db->query('UPDATE se_games SET intro_message = \'%[1]\' WHERE db_name = \'[game]\'',
	 $introduction);

	if ($db->affectedRows($introQuery) > 0) {
		insert_history($user['login_id'], 'Changed the introduction message.');
		$changed['introduction'] = true;
	}
}

// Change game description
$tpl->assign('gameDescription', $gameInfo['description']);
if (isset($description)) {
	$changed['description'] = false;

	$descQuery = $db->query('UPDATE se_games SET description = \'%[1]\' WHERE db_name = \'[game]\'',
	 $description);

	if ($db->affectedRows($descQuery) > 0) {
		insert_history($user['login_id'], 'Changed the game description.');
		$changed['description'] = true;
	}
}

if (!empty($changed)) {
    $gameInfo = selectGame($account['in_game']); // re-load variables
	$tpl->assign('changed', $changed);
}

assignCommon($tpl);
$tpl->display('game/admin/settings.tpl.php');

?>
