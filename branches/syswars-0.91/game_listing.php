<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

//Function that will log a user into gamelisting
function login_to_server($handle, $password)
{
	global $db;

	require_once('inc/external/sha256/sha256.class.php');
	$password = sha256::hash($password);

	/*************************User Login************************/
	$uQuery = $db->query('SELECT login_id, passwd, last_login FROM ' .
	 'user_accounts WHERE login_name = \'%s\'', array($db->escape($handle)));
	$userInfo = $db->fetchRow($uQuery);

	if (empty($userInfo)) { //incorrect username
		print_header('Login Problem');
?>
<h1>Failed to authenticate</h1>
<p>That user does not exist on this Server - either you typed in your user
name wrong, or your account no longer exists.</p>
<p><a href="register.php">Register</a> or <a href="index.php">Try Again</a></p>
<?php
		print_footer();
		exit();
	}

	if ($password !== $userInfo['passwd']) { //incorrect password
		print_header("Bad password");
?>
<h1>Failed to authenticate</h1>
<p>The password you entered is incorrect.</p>
<p><a href="register.php">Register</a> or <a href="index.php">Try Again</a></p>
<?php
		insert_history($userInfo['login_id'], 'Login attempt failed');
		print_footer();
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
		print_header('The Solar Empire Story');
		$storyText = include('inc/story.inc.php');
		echo <<<END
<h1>The Solar Empire Story</h1>
<p><a href="game_listing.php">Skip Story</a></p>
{$storyText['The_Solar_Empire_Story']}
<p><a href="game_listing.php">Skip Story</a></p>

END;
		print_footer();
	} else {
	    header('Location: game_listing.php');
	}

	exit();
}

//user logging into server.
if (isset($_POST['handle']) && isset($_POST['password'])) {
	login_to_server($_POST['handle'], $_POST['password']);
	exit();
}

if (!checkAuth()) {
	require_once('logout.php');
	exit();
}

if ($db_name !== NULL) {
	header('Location: logout.php?logout_single_game=1');
	exit();
}

//user has selected a game.
if (isset($_REQUEST['game_selected'])) {
	$gQuery = $db->query('SELECT db_name, admin, name, started, ' .
	 'intro_message FROM se_games WHERE db_name = \'%s\' AND ' .
	 '(status != \'paused\' OR admin = %u)',
	 array($db->escape($_REQUEST['game_selected']), $p_user['login_id']));

	if ($db->numRows($gQuery) < 1) {
		header('Location: game_listing.php');
		exit();
	}

	$gameInfo = $db->fetchRow($gQuery);
	$db->addVar('game', $db->escape($db_name = $gameInfo['db_name']));

	$inGame = $db->query('SELECT COUNT(*) FROM [game]_users WHERE ' .
	 'login_id = %u', array($login_id));
	$userExists = current($db->fetchRow($inGame, ROW_NUMERIC)) > 0;

	//user logging into selected game. update the db, and redirect to star-system
	if ($userExists) {
		//see if the user is already in the game
		$bannedInfo = $db->query('SELECT banned_time, banned_reason FROM ' .
		 '[game]_users WHERE login_id = %u', array($login_id));
		$banned = $db->fetchRow($bannedInfo);

		//see if user is banned from the selected game
		if ($banned['banned_time'] > time() || $banned['banned_time'] == -1) {
			print_header("Banned");
			$until = $banned['banned_time'] < 0 ?
			 date( "D jS M - H:i", $banned['banned_time']) : 'it resets';
			$reason = esc($banned['banned_reason']);

				echo <<<END
<h1>You are banned!</h1>
<p>The Admin of this game has banned you from it, until <b>$until</b> or until
the admin releases the ban. During this period your fleets/planets are
susceptible to the usual woes of the game.</p>
<p>The reason given by the <cite>admin</cite> was that:
<q>$reason</q></p>
END;

			print_footer();
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


		if ($players >= $gameOpt['max_players']) { //game full check
			print_header("Game Full");
			echo "<b class=b1>$gameInfo[name]</b> is full. Try a Different Game.";
			print_footer();
			exit;
		} elseif ($gameOpt['new_logins'] == 0 || $gameOpt['sudden_death'] == 1) {
			print_header("Game Full");
			echo "Admin has disabled logins for this game (<b class=b1>$gameInfo[name]</b>). Try a Different Game";
			print_footer();
			exit;
		} elseif(!isset($_POST['in_game_name'])){ //fine to join
			print_header("Choose Username");
?>
<h1>Join <?php echo esc($gameInfo['name']); ?></h1>
<form action="<?php echo esc(URL_SELF); ?>" method="post">
	<p><input name="in_game_name" value="<?php echo esc($p_user['login_name']); ?>" size="10" class="text" />
	Name you would like to play under</p>
	<p><input name="ship_name" size="10" class="text" /> First ship title</p>
	<p><input type="submit" value="Join" class="button" />
	<input type="hidden" name="game_selected" value="<?php echo esc($_GET['game_selected']); ?>" /></p>
</form>
<?php
			print_footer();
			exit;
		} else { //confirming details, then adding to game.
			//validate proposed username
			$in_game_name = trim($_POST['in_game_name']);
			if (!valid_name($in_game_name)) {
				print_header('New Account - ' . $gameInfo['name']);
?>
<p>Invalid login name. No slashes, no spaces and minimum of three characters.</p>
<p><a href="game_listing.php" onclick="history.back();">Try again</a></p>
<?php
				print_footer();
				exit();
			}

			#determine if that username is already in user by another player in the game, or another player as a server name.
			$nExists = $db->query('SELECT COUNT(*) FROM [game]_users AS u, ' .
			 'user_accounts AS p WHERE u.login_id != %u AND ' .
			 'p.login_id != %u AND (u.login_name = \'%s\' OR ' .
			 'p.login_name = \'%s\')', array($p_user['login_id'],
			 $p_user['login_id'], $db->escape($in_game_name),
			 $db->escape($in_game_name)));

			if (current($db->fetchRow($nExists)) > 0) {
				print_header("Choose Username");
				echo "There is already a user in this game, or on the server, with that username.";
				$rs = "<br /><br /><a href=\"javascript:history.back()\">Try a different name.</a>";
				print_footer();
				exit;
			}

			//create user's first ship
			$startWith = $db->query('SELECT * FROM [game]_ship_types WHERE ' .
			 'type_id = %u', array($gameOpt['start_ship']));
			if (!$firstShip = $db->fetchRow($startWith)) {
				trigger_error('Ship #' . $gameOpt['start_ship'] . 
				 ' is missing; the user cannot join the game.', E_USER_ERROR);
				exit();
			}

			$firstShip['ship_name'] = correct_name($_POST['ship_name']);
			if (empty($firstShip['ship_name'])){
				$firstShip['ship_name'] = "Un-named";
			}

			$ship_owner = array('login_id' => $p_user['login_id'], 'login_name' => $in_game_name);
			$ship_id = make_ship($firstShip, $ship_owner);


			//create user account within game
			$db->query('INSERT INTO [game]_users (login_id, login_name, ' .
			 'joined_game, turns, cash, ship_id) VALUES (%u, \'%s\', %u, %u, ' .
			 '%u, %u)', array($p_user['login_id'], $db->escape($in_game_name),
			 time(), $gameOpt['start_turns'], $gameOpt['start_cash'],
			 $ship_id));

			//insert user options
			$db->query('INSERT INTO [game]_user_options (login_id) ' .
			 'VALUES (%u)', array($p_user['login_id']));

			//send the intro message (if there is one to send).
			if(!empty($gameInfo['intro_message'])){
				$gameInfo['intro_message'] = nl2br($gameInfo['intro_message']);
				$newId = newId('[game]_messages', 'message_id');
				$db->query('INSERT INTO [game]_messages (message_id, ' .
				 'sender_id, sender_name, text, login_id, timestamp) VALUES ' .
				 '(%u, %u, \'Admin\', \'%s\', %u, %u)', array($newId, 
				 $gameInfo['admin'], $db->escape($gameInfo['intro_message']),
				 $p_user['login_id'], time()));
			}

			insert_history($login_id, 'Joined Game');
			post_news(esc($in_game_name) . ' joined the game.');

			//update user game counter, and in-game status
			$db->query("UPDATE user_accounts SET num_games_joined = num_games_joined + 1, in_game = '$db_name' where login_id = '$p_user[login_id]'");

			header('Location: system.php');
			exit();
		}//end join process
	}
	exit();
}

print_header("Game Listings");

if (IS_OWNER && isset($_REQUEST['newGame'])) {
	$gametitle = $_GET['newGame'];
	$gamename = str_replace(' ', '', $gametitle);
	$find = array('gamename', 'gametitle');
	$replace = array($gamename, $gametitle);
	require_once('inc/generator.inc.php');
	$query = '';
	$fp = fopen('inc/game.' . $db->type . '.sql', 'r');
	while (!feof($fp)) {
		$line = fgets($fp);
		if (strpos(ltrim($line), '--') === 0) {
			$db->query('%s', array(str_replace($find, $replace, $query)));
			$query = '';
		} else {
			$query .= $line;
		}
	}
	$db->query('%s', array(str_replace($find, $replace, $query)));
	clearImages('img/' . $gamename . '_maps');
}

if (IS_OWNER && isset($_REQUEST['deleteGame'])) {
	$gamename = $_GET['deleteGame'];
	$deleteGame = $gamename.'_bilkos, '.$gamename.'_clans, '.$gamename.'_clan_invites, '.$gamename.'_db_vars, '.$gamename.'_diary, '.$gamename.'_messages, '.$gamename.'_news, '.$gamename.'_planets, '.$gamename.'_ports, '.$gamename.'_ships, '.$gamename.'_ship_types, '.$gamename.'_stars, '.$gamename.'_users, '.$gamename.'_user_options';
	
	$removegameindex = "DELETE FROM se_games WHERE db_name='$gamename'";
	$removegametables = "DROP TABLE $deleteGame";

	$result = mysql_query($removegameindex);
	$result = mysql_query($removegametables);
	
	echo 'Deleted game '.$gamename;

}

?>
<div id="logo"><img src="img/se_logo.jpg" alt="Solar Empire" /></div>

<div id="gameExtras">
	<h2>Random tip</h2>
	<p><?php

$tipQuery = $db->query('SELECT tip_content FROM daily_tips ORDER BY RAND()');
list($tip) = $db->fetchRow($tipQuery, ROW_NUMERIC);
echo $tip;

?></p>

	<h2>Recent news</h2>
<?php

require_once('inc/server_news.inc.html');

?>
</div>

<h1>Game Listing for <?php echo $p_user['login_name']; ?></h1>
<p>To enter or join a game, click its name below:</p>
<div id="gameList">
<?php
$joined = array();
$unjoined = array();

// Cycle through the games that are not hidden
$games = $db->query('SELECT name, db_name, status FROM ' .
 'se_games WHERE status != \'hidden\' OR admin = %u ORDER BY name ASC', 
 array($p_user['login_id']));
while ($game = $db->fetchRow($games, ROW_NUMERIC)) {
	$inGame = $db->query('SELECT COUNT(*) FROM ' . $game[1] .
	 '_users WHERE login_id = %u', array($p_user['login_id']));
	list($count) = $db->fetchRow($inGame, ROW_NUMERIC);
	if ($count > 0) { //player already in that game
		$joined[] = $game;
	} else { //player not in that game.
		$unjoined[] = $game;
	}
}

if (!empty($joined)) {
?>
<h2>You are currently playing in</h2>
<ul>
<?php
	foreach ($joined as $game) {
?>
	<li><a href="<?php echo esc(URL_SELF . '?game_selected=' . $game[1]);
	?>"><?php echo esc($game[0]); ?></a> <?php
		if ($game[2] === 'paused') {
				?> (paused)<?php
		} else {
			$sd = $db->query('SELECT value FROM ' . $game[1] .
			 '_db_vars WHERE name = \'sudden_death\'');
			if (current($db->fetchRow($sd)) == 1) {
				echo ' (sudden death)';
			}
		}
		echo ' - ' . popup_help('game_info.php?db_name=' . $game[1], 600, 450);
	}
?>
</ul>
<?php
}

if (!empty($unjoined)) {
?>
<h2>Unjoined games</h2>
<ul>
<?php
	foreach ($unjoined as $game) {
?>
	<li><a href="<?php echo esc(URL_SELF . '?game_selected=' . $game[1])
	?>"><?php echo esc($game[0]); ?></a> <?php
		if ($game[2] === 'paused') {
			?> (paused)<?php
		} else {
			$sd = $db->query('SELECT value FROM ' . $game[1] .
			 '_db_vars WHERE name = \'sudden_death\'');
			if (current($db->fetchRow($sd)) == 1) {
				echo ' (sudden death)';
			}
		}
		echo ' - ' . popup_help('game_info.php?db_name=' . $game[1], 600, 450);
	}
?>
</ul>
<?php
}

if (empty($joined) && empty($unjoined)) {
?>
<p>There are no games running.</p>
<?php
}
?>
</div>

<h2>Options</h2>
<ul>
	<li><a href="logout.php">Logout Completely</a></li>
	<li><a href="credits.php">Credits</a></li>
</ul>
<?php
if (IS_OWNER) {
?>
<h2>Add Game</h2>
	<form action="<?php echo $self; ?>" method="get">
		<input type="text" name="newGame" class="text" />
		<input type="submit" class="button" value="Add game" />
	</form>
<h2>Delete Game</h2>
			<?php
				$query = 'SELECT db_name, name FROM thetenthpl2.se_games ORDER BY `name`';
				$result = mysql_query($query);
				if(mysql_num_rows($result)!=0){
					echo '<p>Please note this action is permanent!</p><form action="'.$self.'" method="get"><select name="deleteGame">';
					while($row = mysql_fetch_row($result)) {
						echo '<option value="' . $row[0] . '">' . $row[1] . '</option>';
					}
					echo '</select><input type="submit" class="button" value="Delete game" /></form>';
				}else{
					echo '<p>No games running to delete</p>';
				}
			?>
	
<?php
}
?>
</ul>

<h2>Places to go</h2>
<ul>
	<li><a href="http://home.solar-empire.net/">Solar Empire Home</a></li>
	<li><a href="http://forum.solar-empire.net/">Solar Empire Forum</a></li>
	<li><a href="http://sourceforge.net/projects/solar-empire/">Sourceforge Project</a></li>
</ul>
<?php
print_footer();

?>
