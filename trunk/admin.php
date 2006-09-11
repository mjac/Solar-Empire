<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');

$out = '';


// Changes when the game ends
if (isset($finishes)) {
	$match = array();
	if (preg_match('/^([12][0-9]{3})-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|' .
	 '3[01]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $finishes, 
	 $match)) {
		$newEnd = mktime($match[4], $match[5], $match[6], $match[2], 
		 $match[3], $match[1]);

		$db->query('UPDATE se_games SET finishes = %u WHERE db_name = ' .
		 '\'[game]\'', array($newEnd));

		$out .= "<p>Game finishing date changed to " .
		 date('Y-m-d H:i:s', $newEnd) . "</p>\n";
	} else {
		$out .= "<p>Invalid format for the date: use YYYY-MM-DD HH:MM:SS</p>\n";
	}
}


// Give players money
if (isset($more_money)) {
	if (!isset($money_amount)) {
		get_var('Increase Money','admin.php','How much money do you want to ' .
		 'give to each player?','money_amount','');
	} else {
		$db->query('UPDATE [game]_users SET cash = cash + %d', 
		 array($money_amount));
		insert_history($user['login_id'], 
		 'Gave $money_amount credits to all players.');
		$out .= "<p>Every player has been given $money_amount credits.</p>\n";
	}
}


// Post news
if (isset($post_game_news) && empty($text)) {
	get_var('Post News', $self, 'What do you want to post in the News?', 
	 'text', '');
} elseif (isset($post_game_news)) {
	$out = "<p>News Posted.</p>\n";
	post_news($text);
}

// Active user listing
if (isset($show_active)) {
	$out = "<h1>Users active within the past 5 minutes</h1>\n<p>Time Loaded: " .
	 date("H:i:s (M d)") . " <a href=\"admin.php?show_active=1\">Reload</a></p>";

	$players = $db->query('SELECT last_request, login_name, login_id, ' .
	 'u.clan_id, c.symbol AS clan_sym,c.sym_color AS ' .
	 'clan_sym_colour FROM [game]_users AS u LEFT JOIN ' .
	 '[game]_clans AS c ON u.clan_id = c.clan_id WHERE ' .
	 'last_request > %u ORDER BY last_request DESC', array(time() - 300));
	if ($db->numRows($players) < 1) {
		$out .= "<p>There are no active users.</p>";
	} else {
		$out .= "<table class=\"simple\">\n\t<tr>\n\t\t<th>Login Name</th>\n" .
		 "\t\t<th>Last Request</th>\n\t</tr>\n";
		while ($player = $db->fetchRow($players)) {
			$out .= "\t<tr>\n\t\t<td>" . print_name($player) . "</td>\n" .
			 "\t\t<td>" . date( "H:i:s (M d)", $player['last_request']) . 
			 "</td>\n\n\t</tr>\n";
			$player = dbr();
		}
		$out .= "</table>";
	}

	$rs = "<p><a href=admin.php>Back to Admin Page</a>";
	print_page("Active Users",$out);
}


// Set game rating
if (isset($difficulty)) {
	if (!isset($set_dif)) {
		$out = <<<END
<p>This will have no effect upon the game itself but guides people, 
especially new players, to join certain games depending on their experience.</p>
<form action="$self" method="post">
	<ul>
		<li><input type="radio" name="set_dif" value="1" /> Training</li>
		<li><input type="radio" name="set_dif" value="2" /> Beginner</li>
		<li><input type="radio" name="set_dif" value="3" /> Intermediate</li>
		<li><input type="radio" name="set_dif" value="4" /> Challenge</li>
		<li><input type="radio" name="set_dif" value="5" /> Advanced</li>
		<li><input type="radio" name="set_dif" value="6" /> All Levels</li>
	</ul>
	<p><input type="submit" class="button" value="Change difficulty" />
	<input type="hidden" name="difficulty" value="1" /></p>
</form>

END;
		print_page('Select difficulty', $out);
	} elseif (!(is_numeric($set_dif) && $set_dif >= 1 && $set_dif <= 6)) {
		$out .= "<p>Invalid difficulty.</p>\n";
	} else {
		$db->query('UPDATE se_games SET difficulty = %u where db_name = ' .
		 '\'[game]\'', array($set_dif));
		$out .= "<p>Stated difficulty updated.</p>\n";
		insert_history($user['login_id'], 'Game difficulty changed.');
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
			$db->query('UPDATE se_games SET status = \'%s\', ' .
			 'processed_cleanup = %u, processed_systems = %u, ' .
			 'processed_turns = %u, processed_ships = %u, ' .
			 'processed_planets = %u, processed_government = %u WHERE ' .
			 'db_name = \'[game]\'', array($db->escape($status), time(), 
			 time(), time(), time(), time(), time()));
			$out .= "<p>Game is now $status.</p>\n";
			insert_history($user['login_id'], "Changed status to $status.");
	}
}


//preview a universe
if (isset($preview)) {
	$out = <<<END
<h1><a href="$self?preview=1">Universe preview</a></h1>
<p><img src="admin_build_universe.php?preview=1&amp;process=1"
 title="Generating universe; this may take some time." /></p>
<p>The above image uses <strong>only</strong> the following variables.</p>
<ul>
	<li>uv_map_layout</li>
	<li>uv_max_link_dist</li>
	<li>uv_min_star_dist</li>
	<li>uv_num_stars</li>
	<li>uv_show_warp_numbers</li>
	<li>uv_universe_size</li>
	<li>wormholes</li>
</ul>
<p>Changing any of these variables will have some sort of effect on the
image/universe generated.</p>
<p>There is no way to save the previewd universe and use it in a game.
It is only an example of what can be created.</p>
<p>If no image appears, then there is a pretty big bug somewhere in the 
universe generation process. Report it to the Server Admin.</p>

END;

	print_page('Universe preview', $out);
}


// Reset game
if (isset($reset)) {
	if ($reset == 2) {
		require_once('inc/generator.inc.php');
		$out .= "<h1>Game reset started</h1>\n<ul>\n";

		clearImages('img/' . $gameInfo['db_name'] . '_maps');
		$out .= "\t<li>Map images deleted</li>\n";

		$db->query('DELETE FROM [game]_users');
		$db->query('DELETE FROM [game]_user_options');
		$out .= "\t<li>Users deleted (including you)</li>\n";

		$db->query('DELETE FROM [game]_news');
		$out .= "\t<li>News erased</li>\n";

		$db->query('DELETE FROM [game]_planets');
		$out .= "\t<li>Planets erased</li>\n";

		$db->query('DELETE FROM [game]_messages WHERE login_id != %u AND ' .
		 'login_id != %u', array($gameInfo['admin'], OWNER_ID));
		$out .= "\t<li>Messages deleted.</li>\n";

		$db->query('DELETE FROM [game]_diary WHERE login_id != %u AND ' .
		 'login_id != %u', array($gameInfo['admin'], OWNER_ID));
		$out .= "\t<li>Diaries erased.</li>\n";

		$db->query('DELETE FROM [game]_ships');
		$out .= "\t<li>Ships deleted</li>\n";

		$db->query('DELETE FROM [game]_clans');
		$db->query('DELETE FROM [game]_clan_invites');
		$out .= "\t<li>Clans deleted</li>\n";

		$db->query('DELETE FROM [game]_bilkos');
		$out .= "\t<li>Auction house emptied</li>\n";

		$db->query('UPDATE se_games SET started = %u, finishes = %u WHERE ' .
		 'db_name = \'[game]\'', array(time(), time() + 1728000));
		$out .= "\t<li>Last reset date updated to now</li>\n</ul>\n";

		post_news('Game reset');
		
		insert_history($user['login_id'], 'Reset game');
		header('Location: game_listing.php');
		exit();
	}

	print_page('Reset game', "<p>Are you sure you want to reset the game? " .
	 "<a href=$self?reset=2>Yes</a> or <a href=$self>no</a>?</p>\n");
}


// Change introduction message
if (isset($messag)) {
	if (isset($new_mess)) {
		$db->query('UPDATE se_games SET intro_message = \'%s\' WHERE ' .
		 'db_name = \'[game]\'', array($db->escape($new_mess)));
		$out .= "<p>The introduction message has been changed.</p>\n";
	}

	$msg = esc($gameInfo['intro_message']);
	$out .= <<<END
<h1>Change the introduction message</h1>
<p>Enter a message that all new players will recieve when they join. XHTML can 
be used, ensure that it is valid.</p>
<form action="$self" method="post">
	<p><input type="hidden" name="messag" value="1" />
	<textarea name="new_mess" cols="50" rows="20">$msg</textarea></p>
	<p><input type="submit" value="Change" class="button" /></p>
</form>
END;

	print_page('Change the introduction message', $out);
	insert_history($user['login_id'], 'Changed the introduction message.');
}

// Change game description
if (isset($descr)) {
	if (isset($new_mess)) {
		$db->query('UPDATE se_games SET description = \'%s\' WHERE ' .
		 'db_name = \'[game]\'', array($db->escape($new_mess)));
		$out .= "<p>The introduction message has been changed.</p>\n";
	}

	$msg = esc($gameInfo['description']);
	$out .= <<<END
<h1>Change the game description</h1>
<p>Enter a message that explains the purpose of this specific game. XHTML can 
be used, ensure that it is valid.</p>
<form action="$self" method="post">
	<p><input type="hidden" name="descr" value="1" />
	<textarea name="new_mess" cols="50" rows="20">$msg</textarea></p>
	<p><input type="submit" value="Change" class="button" /></p>
</form>
END;

	print_page('Change the game description', $out);
	insert_history($user['login_id'], 'Changed the game description.');
}

assignCommon($tpl);

$tpl->display('game/admin/panel.tpl.php');
exit();

?>
