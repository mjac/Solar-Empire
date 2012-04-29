<?php

require_once('inc/common.inc.php');

//Connect to the database
db_connect();

//user logging into server.
if (empty($_COOKIE['session_id']) || empty($_COOKIE['login_id']) ||
     isset($_POST['submit'])) {
	require_once('inc/session_funcs.inc.php');
	login_to_server();
} else {
	check_auth();

	if ($login_id == ADMIN_ID) { //admin trying to continue old session.
		require_once('logout.php');
		exit();
	}
}


//user has selected a game.
if (isset($_REQUEST['game_selected'])) {
	db('select db_name from se_games where game_id = ' . (int)$_REQUEST['game_selected']);
	$game_db = dbr(1);
	$db_name = $game_db['db_name'];

	//see if the user is already in the game
	db("select game_login_count, banned_time, banned_reason from ${db_name}_users where login_id = '$login_id'");
	$in_game = dbr(1);

	//user logging into selected game. update the db, and redirect to location.php
	if (!empty($in_game)) {
		//see if user is banned from the selected game
		if ($in_game['banned_time'] > time() || $in_game['banned_time'] == -1) {
			print_header("Banned");
			if($in_game['banned_time'] > time()){
				echo "The <b class=b1>Admin</b> of this game has banned you from it, until <b>".date( "D jS M - H:i",$in_game['banned_time'])."</b> or until the admin releases the ban.<br>During this period your fleets/planets are susceptable to the normal wiles of the game.<br><br>The reason given by the admin was:<br><br> ".stripslashes($in_game['banned_reason']);
			} elseif($in_game['banned_time'] < 0){
				echo "The <b class=b1>Admin</b> has banned you from the game until it resets, whenever that may be, or until the Admin releases the ban.<br><br>The reason given by the admin was:<br><br>".stripslashes($in_game['banned_reason']);
			}
			print_footer();
			exit();
		}

		//not banned from game, so may continue.
		insert_history($login_id, "Logged In");

		//set the user in the game, and increase login count by 1.
		dbn("update {$db_name}_users set game_login_count = game_login_count + 1 where login_id = '$login_id'");
		dbn("update user_accounts set in_game = '$db_name' where login_id = '$login_id'");

		//Update score
		score_func($login_id, 0);
		header('Location: location.php');
		exit();
	} else { //user joining selected game
		db("select count(login_id) from ${db_name}_users where login_id = '$login_id'");
		$check_count = dbr();

		db("select * from se_games where db_name = '$db_name'");
		$game_info = dbr(1);

		//get the vars for the game
		gameVars($db_name);

		//determine when retired if retired, and that isn't rejoining within illicit time.
		if ($retire_period != 0) {
			$time_starts = time() - ($retire_period * 3600);

			//if player retired just before reset, let player join the new game.
			if ($game_info['last_reset'] > $time_starts) {
				$time_starts = $game_info['last_reset'];
			}
			db("select timestamp from user_history where game_db = '$db_name' && login_id = $p_user[login_id] && timestamp > '$time_starts' && action = 'Retired From Game' order by timestamp desc limit 1");
			$candidate = dbr(1);
		}

		if ($check_count[0] >= $max_players) { //game full check
			print_header("Game Full");
			echo "<b class=b1>$game_info[name]</b> is Full. Try a Different Game.";
			print_footer();
			exit;
		} elseif ($new_logins == 0 || $sudden_death == 1) { //game allowing signups check
			print_header("Game Full");
			echo "Admin has disabled logins for this game (<b class=b1>$game_info[name]</b>). Try a Different Game";
			print_footer();
			exit;
		} elseif (isset($candidate['timestamp'])) { //allowed to re-join check
			$result = date( "M d - H:i",$candidate['timestamp'] + ($retire_period * 3600));
			print_header("Retired too recently");
			echo "You have retired from this game within the last <b>".$retire_period."</b> hours.<br>You must wait until <b>$result</b> before you may rejoin this game.";
			print_footer();
			exit;
		} elseif(!isset($_POST['in_game_name'])){ //fine to join
			print_header("Choose Username");
?>
<p>Please enter the name you would like to play under in
<strong><?php print $game_info['name']; ?></strong>.</p>
<form action="game_listing.php" method="post">
	<p><input name="in_game_name" value="<?php print esc($p_user[login_name]); ?>" size="10" />
	<input type="hidden" name="game_selected" value="<?php print esc($_GET[game_selected]); ?>" /></p>
	<p>Enter the name for your first ship.</p>
	<p><input name="ship_name" size="10" /></p>
	<p><input type="submit" value="Join" /></p>
</form>
<?php
			print_footer();
			exit;

		} else { //confirming details, then adding to game.

			//validate proposed username
			$in_game_name = trim((string)$_POST['in_game_name']);
			if((strcmp($in_game_name,htmlspecialchars($in_game_name))) || (strlen($in_game_name) < 3) || (eregi("[^a-z0-9~!@#$%&*_+-=��������׀�� ]",$in_game_name))) {
				print_header("New Account - $game_info[name]");
				echo "Invalid login name. No slashes, no spaces and minimum of three characters.";
				echo "<p><a href=javascript:history.back()>Back to Joining Form</a>";
				print_footer();
				exit();
			}

			#determine if that username is already in user by another player in the game, or another player as a server name.
			db("select pu.login_name, u.login_name as alternate_name from ${db_name}_users u, user_accounts pu where u.login_id != '$p_user[login_id]' && pu.login_id != '$p_user[login_id]' && (u.login_name = '$in_game_name' || pu.login_name = '$in_game_name')");
			$test_name = dbr(1);
			if($test_name['login_name'] || $test_name['alternate_name']){
				print_header("Choose Username");
				echo "There is already a user in this game, or on the server, with that username.";
				$rs = "<br><br><a href=\"javascript:history.back()\">Try a different name.</a>";
				print_footer();
				exit;
			}

				$show_sigs = 1;
				$show_config = 1;
				$show_clan_ships = 1;

			//create user's first ship
			$startWith = db("SELECT * FROM `{$db_name}_ship_types` WHERE `type_id` = " .
			 (int)$start_ship);
			if (!$firstShip = dbr(1)) {
				trigger_error('Ship #' . $start_ship . ' is missing, the user ' .
				 'cannot join the game.', E_USER_ERROR);
				exit();
			}

			$firstShip['ship_name'] = correct_name($_POST['ship_name']);
			if (empty($firstShip['ship_name'])){
				$firstShip['ship_name'] = "Un-named";
			}

			$ship_owner = array('login_id' => $p_user['login_id'], 'login_name' => $in_game_name, 'clan_id' => 0);
			$ship_id = make_ship($firstShip, $ship_owner);


			//create user account within game
			dbn("insert into ${db_name}_users (login_id, login_name, joined_game, turns, cash, ship_id, location, tech) VALUES ('$p_user[login_id]', '$in_game_name', '".time()."', '$start_turns', '$start_cash', '$ship_id', '1', '$start_tech')");

			//insert user options
			dbn("insert ${db_name}_user_options (login_id, show_sigs, show_config, show_clan_ships) VALUES('$p_user[login_id]','$show_sigs','$show_config','$show_clan_ships')");

			//send the intro message (if there is one to send).
			if(!empty($game_info['intro_message'])){
				$game_info['intro_message'] = nl2br($game_name['intro_message']);
				dbn("insert into ${db_name}_messages (sender_id,sender_name,text,login_id,timestamp) values ('1','Admin','$game_name[intro_message]','$p_user[login_id]','".time()."')");
			}

			insert_history($login_id, "Joined Game");
			post_news("<b class=b1>$in_game_name</b> joined the game.");

			//update user game counter, and in-game status
			dbn("update user_accounts set num_games_joined = num_games_joined + 1, in_game = '$db_name' where login_id = '$p_user[login_id]'");

			header('Location: location.php');
			exit();
		}//end join process
	}


//list games
} else {
	#get tip of the day
	print_header("Game Listings");
	db("select tip_content from daily_tips dt, se_games se where dt.tip_id = se.todays_tip LIMIT 1");
	$tip_today = dbr(1);

?>
<div id="logo"><img src="img/se_logo.jpg" alt="Solar Empire" /></div>

<div id="gameExtras">
	<h2>Tip of the day</h2>
	<p><?php print $tip_today['tip_content']; ?></p>

	<h2>Recent news</h2>
<?php require_once('inc/server_news.inc.html'); ?>
</div>

<h1>Game Listing for <?php print $p_user['login_name']; ?></h1>
<p>To enter or join a game, click its name below:</p>
<div id="gameList">
<?php
	$joined = array();
	$unjoined = array();

	//cycle through the games that are running.
	$games = mysql_query('SELECT `name`, `db_name`, `paused`, `game_id` FROM `se_games` WHERE `status` = \'1\' ORDER BY `name` ASC');
	while ($game = mysql_fetch_row($games)) {
		$inGame = mysql_query('SELECT COUNT(*) FROM `' . $game[1] .
		 '_users` WHERE `login_id` = ' . (int)$p_user['login_id']);
		if (mysql_result($inGame, 0) > 0) { //player already in that game
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
	<li><a href="<?php print esc($_SERVER['PHP_SELF'] . '?game_selected=' . $game[3])
	?>"><?php print esc($game[0]); ?></a> <?php
			if ($game[2] == 1) {
				?> (paused)<?php
			} else {
				$sd = mysql_query('SELECT `value` FROM `' . $game[1] .
				 '_db_vars` WHERE `name` = \'sudden_death\'');
				if (mysql_result($sd, 0) == 1) {
					?> (sudden death)<?php
				}
			}
			print ' - ' . popup_help('game_info.php?db_name=' . $game[1], 600, 450);
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
	<li><a href="<?php print esc($_SERVER['PHP_SELF'] . '?game_selected=' . $game[3])
	?>"><?php print esc($game[0]); ?></a> <?php
			if ($game[2] == 1) {
				?> (paused)<?php
			} else {
				$sd = mysql_query('SELECT `value` FROM `' . $game[1] .
				 '_db_vars` WHERE `name` = \'sudden_death\'');
				if (mysql_result($sd, 0) == 1) {
					?> (sudden death)<?php
				}
			}
			print ' - ' . popup_help('game_info.php?db_name=' . $game[1], 600, 450);
		}
?>
</ul>
<?php
	}
?>
</div>

<h2>Options</h2>
<ul>
<?php if ($p_user['login_id'] != 1) { ?>
	<li><a href=logout.php?logout_gamelist=1>Logout Completely</a></li>
<?php } ?>
	<li><a href="bugs_tracker.php">Bug Tracking</a></li>
	<li><a href="credits.php" target="_blank">Credits</a></li>
</ul>

<h2>Places to go</h2>
<ul>
	<li><a href="http://home.solar-empire.net/" target="_blank">Home of Solar Empire</a></li>
	<li><a href="http://forum.solar-empire.net/" target="_blank">Solar Empire forum</a></li>
</ul>
<?php
	print_footer();
}

?>
