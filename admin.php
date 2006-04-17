<?php

require_once('inc/admin.inc.php');

$out = '';

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

#give players money
if (isset($more_money)) {
	if (!isset($money_amount)) {
		get_var('Increase Money','admin.php','How much money do you want to give to each player?','money_amount','');
	} elseif($money_amount < 1) {
		$out .= "You can't decrease the players money.<br /><br />";
	} else {
		$db->query('UPDATE [game]_users SET cash = cash + %d WHERE ' .
		 'login_id != %u', array($money_amount, $gameInfo['admin']));
		insert_history($user['login_id'], 'Gave $money_amount credits to all players.');
		$out .= "<p>Every player has been given $money_amount credits.</p>\n";
	}
}

#news post
if (isset($post_game_news) && empty($text)) {
	get_var('Post News', $self, 'What do you want to post in the News?', 'text', '');
} elseif (isset($post_game_news)) {
	post_news($text);
	$out = "News Posted.<p>";
}

//active user listing
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


#admin sets difficulty
if(isset($difficulty)){
	if(!isset($set_dif)){
		$out = <<<END
<p>This will have no effect upon the game itself, but will serve simply to 
inform new joiners to the game what to expect.</p>
<form action="$self" name="get_dif_form" method="post">
	<p><input type="radio" name="set_dif" value="1" />Beginner
	<br /><input type="radio" name="set_dif" value="2" />Beginner -> Intermediate
	<br /><input type="radio" name="set_dif" value="3" />Intermediate
	<br /><input type="radio" name="set_dif" value="4" />Intermediate - > Advanced
	<br /><input type="radio" name="set_dif" value="5" />Advanced
	<br /><input type="radio" name="set_dif" value="6" />All Skill Levels</p>
	<p><input type="submit" class="button" />
	<input type="hidden" name="difficulty" value="1" /></p>
</form>

END;
		print_page("Select Difficulty",$out);
	} else {
		$db->query("update se_games set difficulty = '$set_dif' where db_name = '$db_name'");
		$out .= "Stated Difficulty updated<p>";
		insert_history($user['login_id'],"Game difficulty changed.");
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
	print_header("Universe Preview");
?>
<script>
function refresh(){
	var now = new Date();
	document.images.preview_uni_img.src = 'admin_build_universe.php?preview=1&process=1&rand=' + now.getTime();
}
</script>
<a href="javascript:refresh();">Generate New Universe</a><br />
<img name="preview_uni_img" src="admin_build_universe.php?preview=1&process=1"
 title="Generating universe and loading image. This may take some time." /><br />
<a href='javascript:refresh();'>Generate New Universe</a>
<p>The above image uses the following variables <b>only</b>.</p>
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
<h3>Warning</h3>
<p>If you change <em>uv_universe_size</em>, during a game that has
<em>uv_explored</em> set to 0, players may experience some very strange maps
getting created. So be sure to set uv_universe_size back to what it was when
you finished messing around if you are not about to create a new game.</p>
<p>There is no way to save the present universe and use it in a game.
It is only an example of what can be created.</p>

<p>If no image appears, then there is a pretty big bug somewhere in the universe generation process. Report it to the Server Admin.

<?php

	print_footer();
	exit;
}


// reset game
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


#list all planets in game
if (isset($planet_list) || isset($sort_planets)) {
	if (isset($sorted) && $sorted == 1) {
		$going = "ASC";
		$sorted = 2;
	} else {
		$going = "DESC";
		$sorted = 1;
	}
	if(!empty($sort_planets)){
		db("select login_name,planet_name,location,fighters,colon, p.cash,metal,fuel,elect,organ from [game]_planets AS p LEFT JOIN [game]_users AS u ON p.login_id = u.login_id where location != 1 AND planet_type >= 0 order by $sort_planets $going");
	} else {
		db("select login_name,planet_name,location,fighters,colon, p.cash,metal,fuel,elect,organ from [game]_planets AS p LEFT JOIN [game]_users AS u ON p.login_id = u.login_id where location != 1 AND planet_type >= 0 order by login_name asc, fighters desc, planet_name asc");
	}

	$clan_planet = dbr(1);
	if($clan_planet) {
		$out .= make_table(array("<a href=$self?sort_planets=login_name&sorted=$sorted>Planet Owner</a>","<a href=$self?sort_planets=planet_name&sorted=$sorted>Planet Name</a>","<a href=$self?sort_planets=location&sorted=$sorted>Location</a>","<a href=$self?sort_planets=fighters&sorted=$sorted>Fighters</a>","<a href=$self?sort_planets=colon&sorted=$sorted>Colonists</a>","<a href=$self?sort_planets=cash&sorted=$sorted>Cash</a>","<a href=$self?sort_planets=metal&sorted=$sorted>Metal</a>","<a href=$self?sort_planets=fuel&sorted=$sorted>Fuel</a>","<a href=$self?sort_planets=elect&sorted=$sorted>Electronics</a>","<a href=$self?sort_planets=organ&sorted=$sorted>Organics</a>"));
		while($clan_planet) {
			$out .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$out .= "</table>";
		print_page("Planet List", $out);
	} else {
		$out .= "There are no planets in the game.<p>";
	}
}


#change intro message
if(isset($messag)){
	if($messag == 1){
		db("select intro_message from se_games where db_name = '$db_name'");
		$present_mess = dbr();
		$present_mess[0] = stripslashes($present_mess[0]);
		$out .= "Please enter a message that all new players will recieve when they join. <p>Notes: HTML is enabled. Message codes are not used.";
		$out .= "<form name=get_var_form action=$self method=POST>";
		$out .= "<input type=hidden name=messag value='2'>";
		$out .= "<textarea name=new_mess cols=50 rows=20 wrap=soft>$present_mess[0]</textarea>";
		$out .= '<p><input type=submit value=Change /></form>';
	} else {
		$db->query("update se_games set intro_message = '%s' where db_name = '[game]'", array($db->escape($new_mess)));
		$out .= "The Intro message has been changed.";
	}
	print_page("Change Intro Message",$out);
	insert_history($user['login_id'],"Intro Message Changed.");
}



#change game description
if(isset($descr)){
	if($descr == 1){
		db("select description from se_games where db_name = '$db_name'");
		$present_desc = dbr();
		$out .= "Please enter some words that explain the game.<p>Note: HTML is enabled, but does not use the message codes. <br />(Leave empty if you don't want to use it)";
		$out .= "<form name=get_var_form action=$self method=POST>";
		$out .= "<input type=hidden name=descr value='2'>";
		$out .= "<textarea name=new_descr cols=50 rows=20 wrap=soft>$present_desc[description]</textarea>";
		$out .= '<p><input type=submit value=Change></form>';
	} else {
		$db->query("update se_games set description = '$new_descr' where db_name = '$db_name'");
		$out .= "The description of the game has been changed.";
	}
	print_page("Change Description",$out);
	insert_history($user['login_id'],"Game description changed.");
}

$out .= <<<END
<h1>Administration</h1>

<h2>Game Functions</h2>
<ul>
	<li><a href="admin_edit_vars.php">Edit variables</a></li>
	<li>Set status to <a href="$self?status=hidden">hidden</a>, 
	<a href="$self?status=paused">paused</a> or 
	<a href="$self?status=running">running</a></li>
	<li><a href="$self?reset=1">Reset game</a></li>
	<li><a href="$self?difficulty=1">Change stated difficulty</a></li>
	<li><form method="post" action="$self">
		<p><input type="text" name="finishes" value="YYYY-MM-DD HH:MM:SS"
		 class="text" />
		<input type="submit" value="Change finish date" class="button" /></p>
	</form></li>
</ul>

<h2>Godlike Abilities</h2>
<ul>
	<li><a href="admin_build_universe.php?build_universe=1&amp;process=1">Create the universe</a></li>
	<li><a href="$self?preview=1">Preview a universe</a></li>
	<li><a href="admin_build_universe.php?gen_new_maps=1&amp;process=1">Generate maps</a></li>
	<li><a href="admin_edit_links.php">Edit star links</a></li>
	<li><a href="admin_unlink_scan.php">Link star islands</a></li>
</ul>

<h2>Communications</h2>
<ul>
	<li><a href="message.php?target=-4">Message everyone</a></li>
	<li><a href="$self?post_game_news=1">Post news</a></li>
</ul>

<h2>Players</h2>
<ul>
	<li><a href="admin_ban_player.php">Ban player</a></li>
	<li><a href="$self?show_active=1">List online players</a></li>
	<li><a href="$self?planet_list=1">List all planets</a></li>
	<li><a href="$self?update_scores=1">Update scores</a></li>
	<li><a href="$self?more_money=1">Give money</a></li>
</ul>

<h2>General</h2>
<ul>
	<li><a href="$self?descr=1">Change game description</a></li>
	<li><a href="$self?messag=1">Change intro message</a></li>
</ul>
END;

print_page("Admin", $out);

?>
