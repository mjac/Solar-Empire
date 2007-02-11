<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');

$out = '';


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

assignCommon($tpl);
$tpl->display('game/admin/panel.tpl.php');

?>
