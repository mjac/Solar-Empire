<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');
require_once('inc/template.inc.php');

//Function that will log a user into gamelisting
function login_to_server($handle, $password)
{
	global $db, $tpl;

	require_once('inc/external/sha256/sha256.class.php');
	$password = sha256::hash($password);

	$uQuery = $db->query('SELECT login_id, passwd, last_login FROM ' .
	 'user_accounts WHERE login_name = \'%s\'', array($db->escape($handle)));
	$userInfo = $db->fetchRow($uQuery);

	$problems = array();

	if (empty($userInfo)) { // Incorrect username
		$problems[] = 'That user does not exist on this Server: either you ' .
		 'typed in your user name wrong or your account no longer exists';

		$tpl->assign('problems', $problems);
		$tpl->display('login_problems.tpl.php');

		exit();
	}

	if ($password !== $userInfo['passwd']) { // Incorrect password
		insert_history($userInfo['login_id'], 'Login attempt failed');
		$problems[] = 'The password is incorrect';

		$tpl->assign('problems', $problems);
		$tpl->display('login_problems.tpl.php');

		exit();
	}

	/*****************User successfully logged in***********************/
	$session = create_rand_string(32);

	$expires = time() + COOKIE_LENGTH;

	setcookie('login_id', $userInfo['login_id'], $expires);
	setcookie('session_id', $session, $expires);

	$db->query('UPDATE user_accounts SET last_login = %u, ' .
	 'session_id = \'%s\', session_exp = %u, last_ip = \'%s\', ' .
	 'login_count = login_count + 1 WHERE login_id = %u', array(time(),
	 $db->escape($session), $expires, $db->escape($_SERVER['REMOTE_ADDR']),
	 $userInfo['login_id']));

	insert_history($userInfo['login_id'], 'Logged into game-list');

	if ($userInfo['last_login'] == 0) { // first login. show them the story.
		$tpl->display('story.tpl.php');
	} else {
	    header('Location: game_listing.php');
	}

	exit();
}

// User logging into server.
if (isset($_POST['handle']) && isset($_POST['password'])) {
	login_to_server($_POST['handle'], $_POST['password']);
	exit();
}


// User must be logged in to get past this point
if (!checkAuth()) {
	require_once('logout.php');
	exit();
}

// Logout of the game
if ($account['in_game'] !== NULL) {
	header('Location: logout.php?logout_single_game=1');
	exit();
}


// User has selected a game.
if (isset($_REQUEST['game_selected'])) {
	$gQuery = $db->query('SELECT db_name, admin, name, started, ' .
	 'intro_message FROM se_games WHERE db_name = \'%s\' AND ' .
	 '(status != \'paused\' OR admin = %u)',
	 array($db->escape($_REQUEST['game_selected']), $account['login_id']));

	if ($db->numRows($gQuery) < 1) {
		header('Location: game_listing.php');
		exit();
	}

	$gameInfo = $db->fetchRow($gQuery);
	$db->addVar('game', $db->escape($db_name = $gameInfo['db_name']));

	$inGame = $db->query('SELECT COUNT(*) FROM [game]_users WHERE ' .
	 'login_id = %u', array($login_id));
	$userExists = current($db->fetchRow($inGame, ROW_NUMERIC)) > 0;

	// User logging into selected game
	if ($userExists) {
		// See if the user is already in the game
		$bannedInfo = $db->query('SELECT banned_time, banned_reason FROM ' .
		 '[game]_users WHERE login_id = %u', array($login_id));
		$banned = $db->fetchRow($bannedInfo);

		// See if user is banned from the selected game
		if ($banned['banned_time'] > time() || $banned['banned_time'] == -1) {
			$tpl->assign('bannedUntil', $banned['banned_time']);
			$tpl->assign('banReason', $banned['banned_reason']);
			$tpl->display('game_banned.tpl.php');
			exit();
		}

		// Not banned from game, so may continue.
		insert_history($login_id, "Logged In");

		// Set the user in the game and increase login count by 1.
		$db->query('UPDATE [game]_users SET game_login_count = ' .
		 'game_login_count + 1 WHERE login_id = %u', array($login_id));
		$db->query('UPDATE user_accounts SET in_game = \'[game]\' WHERE ' .
		 'login_id = %u', array($login_id));

		header('Location: system.php');

		exit();
	} else { // User joining selected game
		$pCount = $db->query('SELECT COUNT(*) FROM [game]_users WHERE ' .
		 'login_id != %u', array($gameInfo['admin']));
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
			exit();
		}

		if (!(isset($_POST['in_game_name']) && isset($_POST['ship_name']))) {
			$tpl->assign('gameName', $gameInfo['name']);
			$tpl->assign('gameSelected', $db_name);
			$tpl->assign('accountName', $account['login_name']);
			$tpl->display('game_join.tpl.php');
			exit();
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
				exit();
			}

			// Determine if that username is already in user by another player in the game, or another player as a server name.
			$nExists = $db->query('SELECT COUNT(*) FROM user_accounts AS p ' .
			 'LEFT JOIN [game]_users AS u ON u.login_id = p.login_id WHERE ' .
			 'p.login_id != %u AND (u.login_name = \'%s\' OR ' .
			 'p.login_name = \'%s\')', array($account['login_id'],
			 $account['login_id'], $db->escape($in_game_name),
			 $db->escape($in_game_name)));

			if (current($db->fetchRow($nExists, ROW_NUMERIC)) > 0) {
				$problems[] = 'There is already an account in this game, or ' .
				 'on the server, using that username';
				$tpl->assign('problems', $problems);
				$tpl->assign('returnTo', 'game_listing.php?game_selected=' . 
				 $db_name);
				$tpl->display('game_join_problems.tpl.php');
				exit();
			}

			// Create user's first ship
			$startWith = $db->query('SELECT * FROM [game]_ship_types WHERE ' .
			 'type_id = %u', array($gameOpt['start_ship']));
			if (!$firstShip = $db->fetchRow($startWith)) {
				trigger_error('Ship #' . $gameOpt['start_ship'] . 
				 ' is missing; the user cannot join the game.', E_USER_ERROR);
				exit();
			}
	
			$firstShip['ship_name'] = $shipName;

			$ship_owner = array('login_id' => $account['login_id'], 
			 'login_name' => $in_game_name);
			$ship_id = make_ship($firstShip, $ship_owner);


			// Create user account within game
			$db->query('INSERT INTO [game]_users (login_id, login_name, ' .
			 'joined_game, turns, cash, ship_id) VALUES (%u, \'%s\', %u, %u, ' .
			 '%u, %u)', array($account['login_id'], $db->escape($in_game_name),
			 time(), $gameOpt['start_turns'], $gameOpt['start_cash'],
			 $ship_id));

			// Insert user options
			$db->query('INSERT INTO [game]_user_options (login_id) ' .
			 'VALUES (%u)', array($account['login_id']));

			// Send the intro message (if there is one to send).
			if(!empty($gameInfo['intro_message'])){
				$gameInfo['intro_message'] = nl2br($gameInfo['intro_message']);
				$newId = newId('[game]_messages', 'message_id');
				$db->query('INSERT INTO [game]_messages (message_id, ' .
				 'sender_id, sender_name, text, login_id, timestamp) VALUES ' .
				 '(%u, %u, \'Admin\', \'%s\', %u, %u)', array($newId, 
				 $gameInfo['admin'], $db->escape($gameInfo['intro_message']),
				 $account['login_id'], time()));
			}

			insert_history($login_id, 'Joined Game');
			post_news(esc($in_game_name) . ' joined the game.');

			// Update user game counter, and in-game status
			$db->query('UPDATE user_accounts SET num_games_joined = ' .
			 'num_games_joined + 1, in_game = \'%s\' WHERE login_id = %u',
			 array($db->escape($db_name), $account['login_id']));

			header('Location: system.php');

			exit();
		}
	}

	exit();
}

if (IS_OWNER && isset($_REQUEST['newGame'])) {
	require_once('inc/generator.inc.php');
	$query = '';
	$fp = fopen('inc/game.' . $db->type . '.sql', 'r');
	while (!feof($fp)) {
		$line = fgets($fp);
		if (strpos(ltrim($line), '--') === 0) {
			$db->query('%s', array(str_replace('gamename', $_REQUEST['newGame'], $query)));
			$query = '';
		} else {
			$query .= $line;
		}
	}
	$db->query('%s', array(str_replace('gamename', $_REQUEST['newGame'], $query)));

	clearImages('img/' . $_REQUEST['newGame'] . '_maps');

	$db->query('INSERT INTO gamename_stars (star_id, star_name, x, y, ' .
	 'link_1, link_2, link_3, link_4, link_5, link_6, metal, fuel, wormhole, ' .
	 'planetary_slots) VALUES (1, \'Sol\', 250, 250, 0, 0, 0, 0, 0, 0, 0, 0, ' .
	 '0, 0)');

	$db->query('DELETE FROM se_games WHERE db_name = \'%s\'', 
	 array($db->escape($_REQUEST['newGame'])));

	$time = time();
	$db->query('INSERT INTO se_games (db_name, name, admin, `status`, ' .
	 'description, intro_message, num_stars, difficulty, started, finishes, ' .
	 'processed_cleanup, processed_turns, processed_systems, ' .
	 'processed_ships, processed_planets, processed_government) VALUES '.
	 '(\'%s\', \'Test Game!\', 1, \'paused\', \'\', \'\', 150, 3, ' .
	 '%u, %u, %u, %u, %u, %u, %u, %u)', 
	 array($db->escape($_REQUEST['newGame']), $time, $time + 1728000, $time, 
	 $time, $time, $time, $time, $time));
}


$tipQuery = $db->query('SELECT tip_content FROM daily_tips ORDER BY RAND()');
$tip = $db->fetchRow($tipQuery, ROW_NUMERIC);
$tpl->assign('tip', $tip[0]);
$tpl->assign('accountName', $account['login_name']);

$gameList = array();

// Cycle through the games that are not hidden
$games = $db->query('SELECT name, db_name, status FROM ' .
 'se_games WHERE status != \'hidden\' OR admin = %u ORDER BY name ASC', 
 array($account['login_id']));

while ($game = $db->fetchRow($games, ROW_ASSOC)) {
	$inGame = $db->query('SELECT COUNT(*) FROM ' . $game['db_name'] .
	 '_users WHERE login_id = %u', array($account['login_id']));
	$result = $db->fetchRow($inGame, ROW_NUMERIC);

	$game['in'] = $result[0] > 0;

	$gameList[] = $game;
}

$tpl->assign('gameList', $gameList);
$tpl->assign('canCreateGame', IS_OWNER);

$tpl->display('game_listing.tpl.php');
exit();

?>
