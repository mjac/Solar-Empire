<?php

require('inc/config.inc.php');
require(PATH_INC . '/state/member.inc.php');
require(PATH_INC . '/template.inc.php');

$gameList = array();

$gameInfoQuery = $db->query('SELECT l.game_id, l.game_name, l.game_summary, p.acc_id IS NULL FROM ([game]list AS l LEFT JOIN [game]player AS p ON l.game_id = p.game_id)');
if (!$db->hasError($gameInfoQuery)) {
	while ($gameRow = $db->fetchRow($gameInfoQuery, ROW_NUMERIC)) {
		$gameList[] = array(
		    'id' => (int)$gameRow[0],
		    'name' => $gameRow[1],
		    'summary' => $gameRow[2],
		    'joined' => (bool)$gameRow[3]
		);
	}
}
$tpl->assign('gameList', $gameList);

$tipQuery = $db->query('SELECT tip_content FROM [server]tip ORDER BY RAND()');
if (!($db->hasError($tipQuery) || $db->numRows($tipQuery) < 1)) {
	$tpl->assign('tip', current($db->fetchRow($tipQuery)));
}

$tpl->assign('serverNews', file_get_contents('inc/servernews.tpl.html'));

/*if (isset($_REQUEST['game_selected'])) {
	$gQuery = $db->query('SELECT db_name, admin, name, started, intro_message FROM se_games WHERE db_name = \'%[1]\' AND (status != \'paused\' OR admin = %[2])',
	 $_REQUEST['game_selected'], $account['login_id']);

	if ($db->numRows($gQuery) < 1) {
		header('Location: game_listing.php');
		exit;
	}

	$gameInfo = $db->fetchRow($gQuery);
	$db_name = $gameInfo['db_name'];
	$db->addVar('game', $db_name);

	$inGame = $db->query('SELECT COUNT(*) FROM [game]_users WHERE login_id = %[1]', 
	 $login_id);
	$userExists = current($db->fetchRow($inGame, ROW_NUMERIC)) > 0;

	// User logging into selected game
	if ($userExists) {
		// See if the user is already in the game
		$bannedInfo = $db->query('SELECT banned_time, banned_reason FROM [game]_users WHERE login_id = %[1]',
		 $login_id);
		$banned = $db->fetchRow($bannedInfo);

		// See if user is banned from the selected game
		if ($banned['banned_time'] > time() || $banned['banned_time'] == -1) {
			$tpl->assign('bannedUntil', $banned['banned_time']);
			$tpl->assign('banReason', $banned['banned_reason']);
			$tpl->display('game_banned.tpl.php');
			exit;
		}

		// Not banned from game, so may continue.
		insert_history($login_id, "Logged In");

		// Set the user in the game and increase login count by 1.
		$db->query('UPDATE [game]_users SET game_login_count = game_login_count + 1 WHERE login_id = %[1]',
		 $login_id);
		$db->query('UPDATE user_accounts SET in_game = \'[game]\' WHERE login_id = %[1]',
		 $login_id);

		header('Location: system.php');

		exit;
	} else { // User joining selected game
		$pCount = $db->query('SELECT COUNT(*) FROM [game]_users WHERE login_id != %[1]',
		 $gameInfo['admin']);
		$players = (int)current($db->fetchRow($pCount));

		// Get the vars for the game
		gameVars($db_name);

		$problems = array();

		if ($players >= $gameOpt['max_players']) {
			$problems[] = 'This game is full';
		}
		if ($gameOpt['new_logins'] == 0 || $gameOpt['sudden_death'] == 1) {
			$problems[] = 'The game admin has disabled logins for this game';
		}

		if (!empty($problems)) {
			$tpl->assign('problems', $problems);
			$tpl->assign('returnTo', 'game_listing.php');
			$tpl->display('game_join_problems.tpl.php');
			exit;
		}

		if (!(isset($_POST['in_game_name']) && isset($_POST['ship_name']))) {
			$tpl->assign('gameName', $gameInfo['name']);
			$tpl->assign('gameSelected', $db_name);
			$tpl->assign('accountName', $account['login_name']);
			$tpl->display('game_join.tpl.php');
			exit;
		} else {
			$in_game_name = trim($_POST['in_game_name']);
			if (!valid_name($in_game_name)) {
				$problems[] = 'Invalid login name';
			}

			$shipName = trim($_POST['ship_name']);
			if (!valid_name($shipName)) {
				$problems[] = 'Invalid ship name';
			}

			if (!empty($problems)) {
				$tpl->assign('problems', $problems);
				$tpl->assign('returnTo', 'game_listing.php?game_selected=' . 
				 $db_name);
				$tpl->display('game_join_problems.tpl.php');
				exit;
			}

			// Determine if that username is already in user by another player in the game, or another player as a server name.
			$nExists = $db->query('SELECT COUNT(*) FROM user_accounts AS p LEFT JOIN [game]_users AS u ON u.login_id = p.login_id WHERE p.login_id != %[1] AND (u.login_name = \'%[2]\' OR p.login_name = \'%[2]\')',
			 $account['login_id'], $in_game_name);

			if (current($db->fetchRow($nExists, ROW_NUMERIC)) > 0) {
				$problems[] = 'There is already an account in this game, or ' .
				 'on the server, using that username';
				$tpl->assign('problems', $problems);
				$tpl->assign('returnTo', 'game_listing.php?game_selected=' . 
				 $db_name);
				$tpl->display('game_join_problems.tpl.php');
				exit;
			}

			// Create user's first ship
			$startWith = $db->query('SELECT * FROM [game]_ship_types WHERE type_id = %[1]', $gameOpt['start_ship']);
			if (!$firstShip = $db->fetchRow($startWith)) {
				trigger_error('Ship #' . $gameOpt['start_ship'] . 
				 ' is missing; the user cannot join the game.', E_USER_ERROR);
				exit;
			}
	
			$firstShip['ship_name'] = $shipName;

			$ship_owner = array('login_id' => $account['login_id'], 
			 'login_name' => $in_game_name);
			$ship_id = make_ship($firstShip, $ship_owner);


			// Create user account within game
			$db->query('INSERT INTO [game]_users (login_id, login_name, joined_game, turns, cash, ship_id) VALUES (%[1], \'%[2]\', %[3], %[4], %[5], %[6])', 
			 $account['login_id'], $in_game_name, time(), 
			 $gameOpt['start_turns'], $gameOpt['start_cash'], $ship_id);

			// Insert user options
			$db->query('INSERT INTO [game]_user_options (login_id) VALUES (%[1])', 
			 $account['login_id']);

			// Send the intro message (if there is one to send).
			if(!empty($gameInfo['intro_message'])){
				$gameInfo['intro_message'] = nl2br($gameInfo['intro_message']);
				$newId = newId('[game]_messages', 'message_id');
				$db->query('INSERT INTO [game]_messages (message_id, sender_id, sender_name, text, login_id, timestamp) VALUES (%[1], %[2], \'Admin\', \'%[3]\', %[4], %[5])', 
				 $newId, $gameInfo['admin'], $gameInfo['intro_message'],
				 $account['login_id'], time());
			}

			insert_history($login_id, 'Joined Game');
			post_news(esc($in_game_name) . ' joined the game.');

			// Update user game counter, and in-game status
			$db->query('UPDATE user_accounts SET num_games_joined = num_games_joined + 1, in_game = \'%[1]\' WHERE login_id = %[2]',
			 $db_name, $account['login_id']);

			header('Location: system.php');

			exit;
		}
	}

	exit;
}

if (IS_OWNER && isset($_REQUEST['newGame']) && ctype_alnum($_REQUEST['newGame'])) {
	$query = '';
	$fp = fopen('inc/game.' . $db->type . '.sql', 'r');
	while (!feof($fp)) {
		$line = fgets($fp);
		if (strpos(ltrim($line), '--') === 0) {
			$db->query(str_replace('gamename', $_REQUEST['newGame'], $query));
			$query = '';
		} else {
			$query .= $line;
		}
	}
	$db->query(str_replace('gamename', $_REQUEST['newGame'], $query));

	$maps = PATH_BASE . '/img/maps/' . $_REQUEST['newGame'];
	if (is_dir($maps)) {
		clearImages($maps);
	} else {
		mkdir($maps);
	}
	if (is_dir($maps . '/local')) {
		clearImages($maps . '/local');
	} else {
		mkdir($maps . '/local');
	}

	$db->query('DELETE FROM se_games WHERE db_name = \'%[1]\'', 
	 $_REQUEST['newGame']);

	$db->query('INSERT INTO se_games (db_name, name, admin, `status`, description, intro_message, num_stars, started, finishes, processed_cleanup, processed_turns, processed_systems, processed_ships, processed_planets, processed_government) VALUES (\'%[1]\', \'Test Game!\', 1, \'paused\', \'\', \'\', 150, %[2], %[3], %[2], %[2], %[2], %[2], %[2], %[2])',
	 $_REQUEST['newGame'], time(), time() + 1728000);
}*/

$tpl->display('gamelisting.tpl.php');

?>
