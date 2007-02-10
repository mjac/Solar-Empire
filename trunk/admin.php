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
			$db->query('UPDATE se_games SET status = \'%[1]\', processed_cleanup = %[2], processed_systems = %[2], processed_turns = %[2], processed_ships = %[2], processed_planets = %[2], processed_government = %[2] WHERE db_name = \'[game]\'', $status, time());
			insert_history($user['login_id'], "Changed status to $status.");
	}
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
		exit;
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

?>
