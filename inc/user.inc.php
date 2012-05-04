<?php

//call other generic file
require_once("common.inc.php");


/**********************
Script initialisation
**********************/

// Connect to the database
db_connect();

// Check and update the authentication.
check_auth();

// Get game info if not admin (loaded for admin in check_auth)
if($login_id != 1){
	// Get the game information
	db("select * from se_games where db_name = '$db_name'");
	$game_info = dbr(1);
}

db("select * from ${db_name}_users where login_id = '$login_id'");
$user = dbr(1);

if (!(is_array($game_info) && is_array($user))) { // Ensure game and user actually exists
	header('Location: login_form.php');
	exit();
}


db("select * from {$db_name}_user_options where login_id = " . $user['login_id']);
$user_options = dbr(1);


// update last request (so as know when user last requested a page in THIS game.
dbn("UPDATE `{$db_name}_users` SET `last_request` = " . time() .
 " WHERE `login_id` = " . $user['login_id']);

gameVars($db_name);

// load the ship present usership
$user_ship = userShip($user['ship_id']);
if ($user_ship === NULL && $user['ship_id'] !== NULL) {
	db("SELECT `ship_id` FROM `{$db_name}_ships` WHERE `login_id` = " .
	 $user['login_id']);
	$other = dbr(1);

	if ($other === false) {
		$user['ship_id'] = NULL;
		db("UPDATE `{$db_name}_users` SET `ship_id` = NULL WHERE `login_id` = " .
		 $user['login_id']);
	} else {
		$user['ship_id'] = $other['ship_id'];
		$user_ship = userShip($user['ship_id']);
		$user['location'] = $user_ship['location'];
		db("UPDATE `{$db_name}_users` SET `location` = " . $user['location'] .
		 'WHERE `login_id` = ' . $user['login_id']);
	}
}

// generic link to go back to the start system
$rs = "<p><a href=\"location.php\">Back to the Star System</a><br>";

// damage capacity of the silicon armour module
$upgrade_sa = 750;



function pageStart()
{
	$data = ob_get_contents();
	if (!empty($data)) {
		ob_end_clean();
	}

	ob_start();
	print $data;
}

function pageStop($title)
{
	$data = ob_get_contents();
	ob_end_clean();
	ob_start('ob_gzhandler');

	print_header($title);
?>
<div id="gameMenu">
<?php
	print statusBar();
?>
</div>
<div id="gameBody">
<?php
	print $data;
?>
</div>
<?php

	print_footer();

	ob_end_flush();
	exit();
}



// function that will print the left column
function statusBar()
{
	global $user, $user_ship, $count_days_left_in_game, $db_name,$max_turns, $enable_politics, $turns_safe, $flag_research, $max_clans, $game_info;

	$menu = "<h1>" . popup_help('game_info.php?db_name=' . $db_name, 600,
	 450, $game_info['name']) . ($game_info['paused'] == 1 ? ' (paused)' : '') .
	 "</h1>\n";

	db("SELECT COUNT(`login_id`) FROM `{$db_name}_users` WHERE `login_id` > 1 && last_request > " . (time() - 300));
	$lr_result = dbr();
	if($user['login_id'] == ADMIN_ID || $user['login_id'] == OWNER_ID) {
		$start = '<a href="admin.php?show_active=1">';
		$end = '</a>';
	} else {
		$start = $end = '';
	}
	$menu .= "<p>{$start}{$lr_result[0]} active user(s){$end}</p>\n";

	$menu .= "<p>" . date('<\a \t\i\t\l\e="T">M d - H:i</\a>') . "</p>\n";

	if ($game_info['paused'] != 1) {
		$menu .= "<p>$count_days_left_in_game days left</p>\n";
	}

	$menu .= "<h2>" . formatName($user['login_id'], $user['login_name'],
	 $user['clan_id'], $user['clan_sym'], $user['clan_sym_color']) . "</h2>\n";

	if ($user['login_id'] != ADMIN_ID && $user['login_id'] != OWNER_ID) {
		if($user['turns_run'] < $turns_safe){
			$s_turns = $turns_safe - $user['turns_run'];
			$menu .= "<p><em>$s_turns</b> safe turn(s) left</em></p>\n";
		} else {
			$menu .= "<p><em>Leaving</em> newbie safety!</p>\n";
			dbn("update ${db_name}_users set turns_run = turns_run + 1 where login_id = '$user[login_id]'");
			dbn("insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values('".time()."','$user[login_name]','$user[login_id]','$user[login_id]','You have just left Newbie safety.<p>This means that you are now attackable by any player who can attack. <p>Good Luck.')");
		}
	}

	$menu .= "<table>\n\t<tr>\n\t\t<th>Turns</th>\n\t\t<td>" . $user['turns'] .
	 ' / ' . $max_turns . "</td>\n\t</tr>\n\t<tr>\n\t\t<th>Credits</th>\n\t\t<td>" .
	 number_format($user['cash']) . "</td>\n\t</tr>\n";

	if ($flag_research != 0) {
		$menu .= "\t<tr>\n\t\t<th>Tech Units</th>\n\t\t<td>" .
		 number_format($user['tech']) . "</td>\n\t</tr>\n";
	}

	/**************
	* Print Ship Info
	**************/
	$menu .= "\t<tr>\n\t\t<th>Ships Killed</th>\n\t\t<td> " . $user['ships_killed'] .
	 "</td>\n\t</tr>\n\t<tr>\n\t\t<th>Ships Lost</th>\n\t\t<td>" .
	 $user['ships_lost'] . "</td>\n\t</tr>\n\t<tr>\n\t\t<th>Score</th>\n\t\t<td>" .
	 number_format($user['score']) . "</td>\n\t</tr>\n</table>\n";

	if ($user['ship_id'] == NULL) {
		$menu .= "<h2>Your ship is destroyed!</h2>\n";
	} else {
		$menu .= "<h2>" . popup_help('help.php?popup=1&ship_info=1&shipno=' .
		 $user_ship['shipclass'], 300, 600, $user_ship['ship_name']) .
		 "</h2>\n<table>\n\t<tr>\n\t\t" .
		 "<th>Class</th>\n\t\t<td>{$user_ship['class_name']}</td>\n\t</tr>\n\t" .
		 "<tr>\n\t\t<th>Fighters</th>\n\t\t<td>" . $user_ship['fighters'] .
		 ' / ' . $user_ship['max_fighters'] . "</td>\n\t</tr>\n\t<tr>\n\t\t".
		 "<th>Shields</th>\n\t\t<td>" . $user_ship['shields'] . ' / ' .
		 $user_ship['max_shields'] . "</td>\n\t</tr>\n\t<tr>\n\t\t" .
		 "<th>Specials</th>\n\t\t<td>" . (empty($user_ship['config']) ? 'none' :
		 $user_ship['config']) . "</td>\n\t</tr>\n\t<tr>\n\t\t" .
		 "<th>Cargo Bays</th>\n\t\t<td>" . bay_storage($user_ship) .
		 "</td>\n\t</tr>\n</table>\n";
	}


	/**************
	* Left Links
	**************/
	$menu .= "<h2>Menu</h2>\n<ul>\n" .
	 "\t<li><a href=\"location.php\">Star System</a></li>\n" .
	 "\t<li><a href=\"diary.php\">Fleet Diary</a></li>\n" .
	 "\t<li><a href=\"news.php\">Game News</a></li>\n";

	if ($max_clans > 1 || $user['login_id'] == ADMIN_ID) {
		$menu .= "\t<li><a href=\"clan.php\">Clan Control</a></li>\n";
	}

	if ($enable_politics == 1) {
		$menu .= "\t<li><a href=\"politics.php\">Politics</a></li>\n";
	}

	$menu .= "\t<li><a href=\"player_stat.php\">Player Ranking</a></li>\n</ul>\n<ul>\n";

	db("select count(message_id) from ${db_name}_messages where login_id = " . $user['login_id']);
	list($counted) = dbr();

	$menu .= "\t<li><a href=\"mpage.php\">$counted Message" . ($counted === 1 ? '' : 's') . "</a></li><li><a href=\"message.php\">Send Message</a></li>\n";

	db("select count(message_id) as new_messages from ${db_name}_messages where timestamp > '$user[last_access_forum]' && login_id = -1 && sender_id != '$user[login_id]'");
	$messg_count_forum = dbr();
	$temp_forum_text = "";
	if($messg_count_forum['new_messages'] > 0){
		$temp_forum_text = " ($messg_count_forum[new_messages] <a href=\"forum.php?last_time=$user[last_access_forum]&find_last=1\">new</a>)";
	}

	$menu .= "\t<li><a href=\"forum.php\">Forum</a>$temp_forum_text</li>\n";

	if($user['login_id'] == ADMIN_ID || $user['login_id'] == OWNER_ID) {

		if($user['login_id'] == ADMIN_ID){
			db("select last_access_admin_forum from se_games where db_name = '${db_name}'");
			$l_view = dbr();
			$time_from = $l_view['last_access_admin_forum'];
		} else {
			$time_from = time();
		}
		db("select count(message_id) as new_messages from se_central_forum where timestamp > '$time_from'");
		$messg_count_forum = dbr();
		$adminForumNew = "";
		if($messg_count_forum['new_messages'] > 0){
			$adminForumNew = " ($messg_count_forum[new_messages] <a href=forum.php?last_time=$time_from&view_a_forum=1>new</a>)";
		}
		$menu .= "\t<li><a href=\"forum.php?view_a_forum=1\">Admin Forum</a> $adminForumNew</li>\n";
	}
	if ($user['login_id'] == ADMIN_ID || $user['login_id'] == OWNER_ID) {
		$menu .= "\t<li><a href=\"forum.php?clan_forum=1\">Clan Forums</a></li>\n";
	} elseif($user['clan_id'] > 0) {
		db("select count(message_id) as new_messages from ${db_name}_messages where timestamp > '$user[last_access_clan_forum]' && login_id = -5 && clan_id = '$user[clan_id]' && sender_id != '$user[login_id]'");
		$messg_count_clan_forum = dbr();
		$temp_clan_forum_text_var = "";
		if($messg_count_clan_forum['new_messages'] > 0){
			 $temp_clan_forum_text_var = " ($messg_count_clan_forum[new_messages] <a href=forum.php?clan_forum=1&last_time=$user[last_access_clan_forum]&find_last=1>new</a>)";
		}
		$menu .= "\t<li><a href=\"forum.php?clan_forum=1\"><span style=\"color: #" .
		 $user['clan_sym_color'] . "\">" . $user['clan_sym'] .
		 "</span> Forum</a>$temp_clan_forum_text_var</li>\n";
	}

	$menu .= "\n\t</ul>\n</ul>\n";

	//admin lower sidebar
	if ($user['login_id'] == ADMIN_ID || $user['login_id'] == OWNER_ID) {
		$menu .= "\t<li><a href=\"admin.php\">Admin</a></li>\n";
		if ($user['login_id'] == OWNER_ID) {
			$menu .= "\t<li><a href=\"developer.php\">Server</a>\n";
		}
	}

	$menu .= "\t<li><a href=\"help.php\">Help</a></li>\n" .
	 "\t<li><a href=\"options.php\">Options</a></li>\n";

	if ($user['login_id'] != ADMIN_ID) {
		$menu .= "\t<li><a href=\"logout.php?logout_single_game=1\">Game List</a></li>\n";
	}

	$menu .= "\t<li><a href=\"logout.php?comp_logout=1\">Logout</a></li>\n</ul>\n";
	return $menu;
}


#function that calls many other functions to result in a printed out page..
function print_page($title, $text)
{
	pageStart();
	print $text;
	pageStop($title);
}



//function that can be used create a viable input form. Adds hidden vars.
function get_var($title, $page_name, $text, $var_name, $var_default)
{
	pageStart();
	print <<<END
<p>$text</p>
<form name="get_var_form" action="$page_name" method=\"post\">
END;
	foreach ($_GET as $var => $value) {
		print "\n<input type=hidden name=$var value='$value'>";
	}
	foreach ($_POST as $var => $value) {
		print "\n<input type=hidden name=$var value='$value'>";
	}
	if($var_name == 'sure') {
		print "\n<input type=hidden name=sure value=yes>";
		print "\n<input type=submit name=submit value=Yes> - <input type='Button' width='30' value='No' onclick=\"javascript: history.back()\">\n</form>";
	} elseif(($var_name == 'passwd') || ($var_name == 'passwd_verify') || $var_name == 'passwd2') {
		print "\n<input type=password name=$var_name value='$var_default' size=20> - ";
		print "\n<input type=submit value=Submit>\n</form>";
	} elseif($var_name == 'text') {
		print "\n<textarea name=$var_name cols=50 rows=20 wrap=soft>".stripslashes($var_default)."</textarea>";
		print "\n<p><input type=submit value=Submit></form>";
	} else {
		print "\n<input name=$var_name value='$var_default' size=20> - ";
		print "\n<input type=submit value=Submit></form>";
	}

	if($var_name != 'sure') {
		print "\n<script> document.get_var_form.$var_name.focus(); </script>";
	} else {
		print "\n<script> document.get_var_form.submit.focus(); </script>";
	}

	pageStop($title);
	exit();
}


/********************
Account updating functions
*********************/

//function that charges turns for something. Admin is exempt.
function charge_turns($amount)
{
	global $db_name, $user;
	if ($user['login_id'] == ADMIN_ID || $user['login_id'] == OWNER_ID) {
		return;
	}

	$amount = round($amount);
	dbn("update ${db_name}_users set turns = turns - '$amount', turns_run = turns_run + '$amount' where login_id = '$user[login_id]'");
	$user['turns'] -= $amount;
	$user['turns_run'] += $amount;
}


//function that can give a user cash. Admin is exempt.
function give_cash($amount)
{
	global $db_name,$user;
	if ($user['login_id'] != ADMIN_ID) {
		dbn("update ${db_name}_users set cash = cash + '$amount' where login_id = '$user[login_id]'");
		$user['cash'] += $amount;
	}
}

//function takes cash from a player. Admin is exempt.
function take_cash($amount)
{
	global $db_name,$user;
	if ($user['login_id'] != ADMIN_ID) {
		dbn("update ${db_name}_users set cash = cash - '$amount' where login_id = '$user[login_id]'");
		$user['cash'] -= $amount;
	}
}


//take tech support units from a player. Admin is exempt.
function take_tech($amount)
{
	global $db_name,$user;
	if ($user['login_id'] != ADMIN_ID) {
		dbn("update ${db_name}_users set tech = tech - '$amount' where login_id = '$user[login_id]'");
		$user['tech'] -= $amount;
	}
}

//Give tech support units to a player. Admin is exempt.
function give_tech($amount)
{
	global $db_name,$user;
	if ($user['login_id'] != ADMIN_ID) {
		dbn("update ${db_name}_users set tech = tech + '$amount' where login_id = '$user[login_id]'");
		$user['tech'] += $amount;
	}
}


/********************
Message Functions
*********************/

//sends $text to $to, from global $user
function send_message($to,$text)
{
	global $db_name,$user;
	if($to == -5 && $user['clan_id'] > 0){//message to the clan
		dbn("insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text, clan_id) values(".time().",'$user[login_name]','$user[login_id]','$to','$text','$user[clan_id]')");
	} else {
		dbn("insert into ${db_name}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$to','$text')");
	}
}

function print_messages($full)
{
	global $db_name, $user, $error_str, $user_options, $last_time, $prevdays,
	 $admin_forum, $bug_board, $allow_signatures, $find_last, $smTImplode,
	 $smSImplode, $login_id;

	$sig = $allow_signatures && $user_options['show_sigs'] ? ', `u`.`sig`' : '';
	$prev = isset($prevdays);

	$gForum = $user['login_id'] == -1;
	$cForum = $user['login_id'] == -5;
	$forum = $gForum ? 'forum' : ($cForum ? 'clan_forum' : '');
	$isForum = $forum !== '';

	db("SELECT COUNT(`message_id`) FROM `{$db_name}_messages` WHERE `login_id` = " . $user['login_id']);
	$checkboxes = 4 < (int)implode('', dbr());

	if ($prev) {
		$forum_secs = $user_options['forum_back'] * 5000;
		$last_time -= $forum_secs;
	} else if ($isForum && !$find_last) {
		$forum_secs = $user_options['forum_back'] * 3600;
		$last_time = time() - $forum_secs;
	}

	/* Display */
	db("SELECT `m`.`message_id`, `m`.`timestamp`, `m`.`text`, `m`.`sender_id`, " .
	 "`u`.`login_id`, `u`.`clan_id`, `u`.`clan_sym_color`, `u`.`clan_sym`, " .
	 "`m`.`sender_name`$sig FROM `{$db_name}_messages` as `m` " .
	 "LEFT JOIN `{$db_name}_users` as `u` ON `m`.`sender_id`=`u`.`login_id` " .
	 "LEFT JOIN `user_accounts` as `a` ON `u`.`login_id`=`a`.`login_id` " .
	 "WHERE `m`.`login_id`={$user['login_id']} " . ($prev ? ("and `timestamp`>" .
	 ($last_time - $forum_secs) . " and `timestamp`<=$last_time") : ($find_last ?
	 "and `timestamp`>$last_time" : ($gForum ? ('and `timestamp`>' .
	 ($last_time - $forum_secs)) : ''))) . ($cForum ? (" and `m`.`clan_id`=" .
	 $user['clan_id']) : '') . " ORDER BY `timestamp` DESC");

	$msgStr = '<dl>';
	//cursing filter

	$filter = array();
	switch ((int)$user_options['cursing_filter']) {
		case 2: // all rudeness
			$filter = array_merge($filter, array('gay', 'crap', 'damn',
			 'hore', 'bastard', 'cock', 'faggot'));
		case 1: // worse words
			$filter = array_merge($filter, array('fuck', 'cunt', 'dick', 'piss',
			 'nigger', 'bitch', 'shit', 'wank', 'bugger'));
	}

	while ($msg = dbr()) {
		if (!empty($filter)) {
			$msg['text'] = preg_replace('/(\w*(?:' . implode('|', $filter) . ')\w*)/ie',
			 'str_repeat(\'*\', strlen(\'\1\'))', $msg['text']);
		}
		$msg['text'] = preg_replace('/\[(' . $smSImplode . ')(' . $smTImplode .
		 ')\]/i', '<img src="img/smiles/\1\2.gif" alt="\1 \2" />', $msg['text']);

		$msgStr .= "\n\t<dt><b>" . date("M d - H:i", $msg['timestamp']) .
		 "</b> " . formatName($msg['login_id'], $msg['sender_name'],
		 $msg['clan_id'], $msg['clan_sym'], $msg['clan_sym_color']) .
		 "</dt>\n\t<dd>" . $msg['text'] . "<br /><br />\n\t" .
		 (empty($msg['sig']) ? '' : $msg['sig'] . ' - ') .
		 "<a href=\"message.php?target=" . $msg['sender_id'] . "&amp;reply_to=" .
		 $msg['message_id'] . "\">Reply</a> - \n\t<a href=\"diary.php?log_ent=" .
		 $msg['message_id'] . "\">Log</a>";

		if ($admin_forum && $forum) {
			$msgStr .= ' - <a href="' . esc('forum.php?killmsg=' .
			 $msg['message_id'] . ($user['login_id'] == -5 ? '&clan_forum=1' :
			 '')) . '">Remove</a>';
		}

		if ($full) {
			$msgStr .= " - <a href=\"mpage.php?killmsg={$msg['message_id']}\">Delete</a>";
			if ($checkboxes) {
				$msgStr .= " - <input type=\"checkbox\" name=\"del_mess[{$msg['message_id']}]\" value=\"{$msg['message_id']}\" />";
			}
		}

		$msgStr .= "</dd>";
	}
	$msgStr .= "\n</dl>";


	if ($isForum) {
		dbn("UPDATE `{$db_name}_users` SET `last_access_$forum` = '" . time() .
		 "' WHERE `login_id` = " . $login_id);
		$user["last_access_$forum"] = time();
	}

	if($gForum) {
		db("select count(message_id) from ${db_name}_messages where login_id = -1 && timestamp < '$last_time'");
		$num_mes_prev = dbr();
		$msgStr .= empty($num_mes_prev[0]) ? "<p>End of Forum</p>" : "<p><a href=\"forum.php?last_time=$last_time&prevdays=yes\">Previous $user_options[forum_back] Hours</a></p>";
	}

	$error_str .= ($full && $checkboxes ? ("<p><a href=\"mpage.php?killallmsg=1\">Delete All Messages</a></p>
<form method=\"post\" action=\"mpage.php\" name=\"messag_form\">
	<input type=\"hidden\" name=\"clear_messages\" value=\"1\">$msgStr
	<a href=\"#\" onclick=\"TickAll('messag_form');\">Invert Message Selection</a> -
	<input type=\"submit\" value=\"Delete Selected\" /></form>") : $msgStr) . ($admin_forum && $target == -1 ? '
	<p><a href="forum.php?randomvarallmsg=1">Delete All Forum Messages</a></p>' : '');
}


function &formatName($id, $name, $clanId, $clanSym, $clanCol)
{
	static $cache = array();

	if (!isset($cache[$name])) {
		$cache[$name] = esc($name);

		if ($id !== NULL) {
			$cache[$name] = '<a href="' . esc('player_info.php?target=' . $id) .
			 '">' . $cache[$name] . '</a>';
		} else {
			$cache[$name] = "<s>{$cache[$name]}</s>";
		}

		if ($clanId !== NULL && $clanId != 0) {
			$cache[$name] = "(<span style=\"color: #$clanCol;\">" .
			 htmlentities($clanSym) . "</span>) " . $cache[$name];
		}
	}

	return $cache[$name];
}


//print clickable name of $player
function print_name($player)
{
	global $db_name, $user_options;
	static $cache = array();

	if (!isset($cache[$player['login_name']])) {//this user not cached
		$pQuery = mysql_query("select u.login_id, u.login_name, u.clan_id, u.clan_sym_color,u.clan_sym from ${db_name}_users u where u.login_id = '$player[login_id]'");
		$player = mysql_fetch_assoc($pQuery);

		$cache[$player['login_name']] = formatName($player['login_id'],
		 $player['login_name'], $player['clan_id'], $player['clan_sym'],
		 $player['clan_sym_color']);
	}

	return $cache[$player['login_name']];
}

//function that damages a ship with a specified amount of damage.
//send a negative number as the first arguement to destroy a ship outright.

function damage_ship($amount,$fig_dam,$s_dam,$from,$target,$target_ship) {
	global $db_name,$query;

	//set the shields down first off (if needed).
	if($s_dam > 0){
		$target_ship['shields'] -= $s_dam;
		if($target_ship['shields'] < 0){
			$target_ship['shields'] == 0;
		}
		dbn("update ${db_name}_ships set shields = shields - '$s_dam' where ship_id = '$target_ship[ship_id]'");
	}

	//take the fighters down next (if needed).
	if($fig_dam > 0){
		$target_ship['fighters'] -= $fig_dam;
		if($target_ship['fighters'] < 0){
			$target_ship['fighters'] == 0;
		}
		dbn("update ${db_name}_ships set fighters = fighters - '$fig_dam' where ship_id = '$target_ship[ship_id]'");
	}

	//don't want to hurt the admin now do we?
	if($target['login_id'] != ADMIN_ID) {

		$shield_damage = 0;

		//only play with the amount distribution if there is no value to amount
		if($amount > 0){

			$shield_damage = $amount;
			if($shield_damage > $target_ship['shields']) {
				$shield_damage = $target_ship['shields'];
			}
			$amount -= $shield_damage;

		}
		if($amount >= $target_ship['fighters'] || $amount < 0) {	//destroy ship
			//Minerals go to the system
			if($from['location'] != 1 && ($target_ship['fuel'] > 0 || $target_ship['metal']) > 0){
				dbn("update ${db_name}_stars set fuel = fuel + ".round($target_ship['fuel']*(mt_rand(20,80)/100)).", metal = metal + ".round($target_ship[metal]*(mt_rand(40,90)/100))." where star_id = $target_ship[location]");
			}

			dbn("delete from ${db_name}_ships where ship_id = '$target_ship[ship_id]'");
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + '1', ships_killed_points = ships_killed_points + '$target_ship[point_value]' where login_id = '$from[login_id]'");

			dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$target_ship[fighters]', ships_lost = ships_lost + '1', ships_lost_points = ships_lost_points + '$target_ship[point_value]' where login_id = '$target[login_id]'");

			if (stristr($target_ship['class_name'], "escape") !== false) { // escape pod lost
				dbn("update ${db_name}_users set location = 1, ship_id = NULL, last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
				return 1;
			} else { // normal ship lost
				if($target['ship_id'] != $target_ship['ship_id']) {
					$new_ship_id = $target['ship_id'];

				} else {
					db("select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' LIMIT 1");
					$other_ship = dbr();

					if(!empty($other_ship['ship_id'])) { // jump to other ship
						$new_ship_id = $other_ship['ship_id'];
					} else {	// build the escape pod
						create_escape_pod($target);
						return 2;
					}
				}
				// set ships_killed

				if($target['login_id'] > 5) {
					db("select location from ${db_name}_ships where ship_id = '$new_ship_id'");
					$other_ship = dbr(1);
				} else {
					$other_ship['location'] = 1;
				}

				dbn("update ${db_name}_users set ship_id = '$new_ship_id', location = '$other_ship[location]', last_attack =".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
			}
			return 1;
		} else { // ship not destroyed
			dbn("update ${db_name}_users set last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
			dbn("update ${db_name}_ships set fighters = fighters - '$amount', shields = shields - '$shield_damage' where ship_id = '$target_ship[ship_id]'");
			dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$amount' where login_id = '$target[login_id]'");
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$amount' where login_id = '$from[login_id]'");
		}
	}

	return 0;
}


//Retires $target
function retire_user($target)
{
	global $user, $db_name;
	if ($target == OWNER_ID || $target == ADMIN_ID || $target != $user['login_id']) {
		print_page("Retire", "Unable to retire this Player.");
		return false;
	}

	db("select login_name from ${db_name}_users where login_id = '$target'");
	$target_user = dbr(1);

	post_news("<b class=b1>$target_user[login_name]</b> Retired from the Game.");
	dbn("delete from ${db_name}_ships where login_id = $target");
	dbn("update ${db_name}_bilkos set bidder_id = 0, timestamp = ".time()." where bidder_id = $target");
	dbn("update ${db_name}_planets set login_name = 'Retired Player', login_id=0, pass='' where login_id = '$target'");
	dbn("delete from ${db_name}_diary where login_id = $target");
	dbn("delete from ${db_name}_user_options where login_id = $target");
	dbn("delete from ${db_name}_users where login_id = $target");
	dbn("update ${db_name}_politics set login_id = 0, login_name = 0, timestamp = 0 where login_id = '$target'");

	return true;
}


/********************
Get Information
*********************/

// retrieve the star data
function get_star()
{
	global $user, $star, $db_name;
	db("select * from {$db_name}_stars where star_id = '$user[location]'");
	return $star = dbr();
}


//get distance between stars $s1 and $s2
function get_star_dist($s1,$s2) {
	global $db_name;
	if(!isset($s1) || !isset($s2)){
		return 0;
	}
	db("select x_loc,y_loc from ${db_name}_stars where star_id = '$s1' || star_id = '$s2'");
	$star1 = dbr(1);
	$star2 = dbr(1);
	$dist = round(sqrt(abs(($star1['x_loc'] - $star2['x_loc']) * 2) + abs(($star1['y_loc'] - $star2['y_loc'])*2)));
	return $dist;
}

//function to check if a player is dead and out during sudden death.
function sudden_death_check($user)
{
	global $sudden_death,$db_name,$rs;
	if ($sudden_death && $user['login_id'] != ADMIN_ID && $user['login_id'] != OWNER_ID) {
		db("select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]'");
		$numships = dbr();
		if ($numships[0] <= 0) {
			print_page("Sudden Death", "You have no ship, and this game is Sudden Death. <br>As such you are out of the game. <br>You may still access the Forum, and send/recieve private messages though.");
		}
	}
}

//Choose a system at random
function random_system_num()
{
	global $db_name, $user;

	db("SELECT `star_id` FROM `{$db_name}_stars` AS `s` LEFT JOIN " .
	 "`{$db_name}_planets` AS `p` ON `s`.`star_id` = `p`.`location` " .
	 "WHERE `p`.`planet_id` IS NULL OR `p`.`login_id` = " . $user['login_id'] .
	 " OR `p`.`clan_id` = " . $user['clan_id'] . " ORDER BY RAND() LIMIT 1");

	if (!$sys = dbr(1)) {
		return 1;
	}

	return (int)$sys['star_id'];

	/*db("select count(star_id) from ${db_name}_stars");
	$total = dbr();
	return round(mt_rand(1,$total[0]));*/
}


/********************
Create an Escape Pod Function
*********************/

//function to create an escape pod
function create_escape_pod($target)
{
	global $db_name;
	$rand_star = random_system_num(); #make a random system number up.
#	$rand_star = 1;
	$ship_types = load_ship_types(); #load ship data
	$ship_stats = $ship_types[2]; #ep is num 2
	$q_string = "insert into ${db_name}_ships (ship_name, login_id, login_name, shipclass, class_name, class_name_abbr, fighters, max_fighters, max_shields, cargo_bays, mine_rate_metal, mine_rate_fuel, move_turn_cost, location, config,clan_id";
	$q_string .= ") values('Escape Pod',$target[login_id],'$target[login_name]',2,'$ship_stats[name]','$ship_stats[class_abbr]',$ship_stats[fighters],$ship_stats[max_fighters],$ship_stats[max_shields],$ship_stats[cargo_bays],$ship_stats[mine_rate_metal],$ship_stats[mine_rate_fuel],$ship_stats[move_turn_cost],$rand_star,'$ship_stats[config]','$target[clan_id]')";
	dbn($q_string);
	$ship_id = mysql_insert_id();
	dbn("update ${db_name}_users set location = '$rand_star', ship_id ='$ship_id' where login_id = '$target[login_id]'");
	$target['location'] = $rand_star;
	$target['ship_id'] = $ship_id;
	return $target;
}



//function that returns a hostile planet checking query
function attack_planet_check($db_name,$user)
{
	return "select * from ${db_name}_planets where fighter_set = 1 && fighters > 0 && login_id != '$user[login_id]' && (clan_id != '$user[clan_id]' && clan_id != 0) && location = '$user[location]' order by fighter_set desc, fighters desc limit 1";
}



//load ship types from database.
function load_ship_types()
{
	global $db_name, $fighter_cost_earth;
	$ship_types = array();

	db("select * from ${db_name}_ship_types where auction != 1 order by type_id");

	while($this_type = dbr(1)) {
		$this_type['cost'] += $this_type['fighters'] * $fighter_cost_earth;
		$ship_types[$this_type['type_id']] = $this_type;
	}

	return $ship_types;
}


#Function to figure out the bonuses offered by weapon upgrades
function bonus_calc($ship)
{
	global $upgrade_sa;

	$dam = array();

	#defensive turret : lvl 1
	$dam['dt'] = round(330 * (mt_rand(75, 125) / 100)) * $ship['num_dt'];

	#offensive turret : lvl 1
	$dam['ot'] = round(200 * (mt_rand(80, 120) / 100)) * $ship['num_dt'];

	#silicon armour : lvl 2
	$dam['sa'] = round($upgrade_sa * (mt_rand(90, 110) / 100)) * $ship['num_sa'];

	#plasma cannon : lvl 2
	$dam['pc'] = round(420 * (mt_rand(92, 108) / 100)) * $ship['num_pc'];

	#electronic warfare module : lvl 1
	$dam['ewd'] = round(325 * (mt_rand(85, 115) / 100)) * $ship['num_ew'];
	$dam['ewa'] = round(225 * (mt_rand(80, 120) / 100)) * $ship['num_ew'];

	return $dam;
}

//A function that gets all the details for the user's new ship, and returns the completed user_ship array.
function userShip($id)
{
	global $db_name;

	db2("select * from {$db_name}_ships where ship_id = " . ($id === NULL ? 'NULL' : $id));
	$user_ship = dbr2(1);
	if ($user_ship == false) {
		return NULL;
	}

	empty_bays($user_ship);

	return $user_ship;
}


//a function that allows a message to be sent to all players.
function message_all_players($text, $game_db, $recipients, $sender)
{
	global $user;

	db2("select login_id from ${game_db}_users");
	while($players = dbr2(1)) {
		dbn("insert into {$game_db}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$players[login_id]','Message to <b class=b1>$recipients</b> from $sender:<p> $text')");
	}

	return "Message sent to all players in <b>$game_db</b>.";
}



//a function to allow for easy addition of upgrades.
function make_standard_upgrade($upgrade_str, $config_addon, $cost,
 $developement_id, $tech_cost = 0)
{
	global $user, $user_ship, $db_name;
	if($user['cash'] < $cost) {
		return "You can not afford to buy a <b class=b1>$upgrade_str</b>.<p>";
	} elseif($user['tech'] < $tech_cost && $tech_cost > 0) {
		return "Ignorant Planet Dweller. You don't have enough tech points.<p>";
	} elseif (strstr($user_ship['config'], $config_addon) !== false){
		return "Your ship is already equipped with a <b class=b1>$upgrade_str</b>.<br>There is no point in having more than one on a ship.<p>";
	} elseif ($user_ship['upgrades'] < 1){
		return "";
	} else {
		take_cash($cost);
		take_tech($tech_cost);
		$user_ship['config'] .= ":".$config_addon;
		dbn("update ${db_name}_ships set config = '$user_ship[config] ', upgrades = upgrades - 1 where ship_id = '$user[ship_id]'");
		--$user_ship['upgrades'];

		return "<b class=b1>$upgrade_str</b>, purchased and fitted to the <b class=b1>$user_ship[ship_name]</b> for <b>$cost</b> Credits.<p>";
	}
}


/*
This function will select fill as many ships in a fleet as possible with whatever is requested.

- 1st arguement sent to it is the sql name for whatever is to be loaded.
- 2nd arguement is the name of the sql entry for the most of that material that any one ship can hold.
- 3rd arguement contains the textual string
- 4th arguement holds the cost per unit of the item.
- 5th arguement is the name of the orginating script

*/
function fill_fleet($item_sql, $item_max_sql, $item_str, $item_cost, $script_name, $cargo_run = 0){
	global $user, $user_ship, $db_name, $sure, $fill_dir;

	$ret_str = "";
	$taken = 0; //item taken from earth far.
	$ship_counter = 0; //ships passed through

	if($cargo_run == 1){ //cargo
		$sql_max_check = $item_max_sql;
		$sql_where_clause = " location = '$user[location]' && login_id='$user[login_id]' && $item_max_sql > 0 ";
		$cargo_run = 1;

	} else {//not cargo
		$sql_max_check = "($item_max_sql - $item_sql)";
		$sql_where_clause = " location = '$user[location]' && login_id='$user[login_id]' && $item_max_sql > 0 && $item_sql < $item_max_sql ";
	}

	//elect all viable ships
	db("select sum($sql_max_check) as total_capacity, count(ship_id) as total_ships from ${db_name}_ships where ".$sql_where_clause);
	$maths = dbr(1);

	//insufficient cash
	if($user['cash'] < $item_cost){
		$ret_str .= "You do not have enough money for even 1 unit of <b class=b1>$item_str</b>. You certainly can't afford to fill a fleet.";
	} elseif(empty($maths) || $maths['total_ships'] < 1) { //ensure there are some ships.
		$ret_str .= "This operation failed as there are no ships that have any free capacity to hold <b class=b1>$item_str</b> in this system that belong to you.";
	} else {
		//work out the total value of them all.
		$total_cost = $maths['total_capacity'] * $item_cost;

		//user CAN afford to fill the whole fleet
		if($total_cost <= $user['cash']) {

			if(empty($sure)){ //confirmation
				get_var('Load ships',$script_name,"There is capacity for <b>$maths[total_capacity]</b> <b class=b1>$item_str</b> in <b>$maths[total_ships]</b> ships in this system. <p>You have enough money to fill all the ships with <b class=b1>$item_str</b>. Do you wish to do that?",'sure','yes');
			} else { //process.
				dbn("update ${db_name}_ships set $item_sql = $item_max_sql where ".$sql_where_clause);
				take_cash($total_cost);

				if($cargo_run == 0){ //not cargo bay stuff
					$user_ship[$item_sql] = $user_ship[$item_max_sql];
				} else { //cargo bay stuff
					$user_ship[$item_sql] += $user_ship['empty_bays'];
				}

				$ret_str .= "<b>$maths[total_capacity]</b> <b class=b1>$item_str</b> were added to <b>$maths[total_ships]</b> ships.<br>All ships are now at maximum capacity.";
			}

		//user CANNOT afford to fill the whole fleet, so we'll have to do it the hard way.
		} else {
			$total_can_afford = floor($user['cash'] / $item_cost); //work out amount can afford.

			if(empty($sure)) { //confirmation
				$extra_text = "<p><input type=radio name=fill_dir value=1 CHECKED> - Fill highest capacity ships ships first.";
				$extra_text .= "<br><input type=radio name=fill_dir value=2> - Fill lowest capacity ships first.";
				get_var('Load ships',$script_name,"There is capacity for <b>$maths[total_capacity]</b> <b class=b1>$item_str</b> in <b>$maths[total_ships]</b> ships in this system. <br>However, you can only afford <b>$total_can_afford</b> $item_str.<p>Do you want to fill as many ships as you can afford to fill?".$extra_text,'sure','yes');
			} else { //process
				if($fill_dir == 1){
					$order_dir = "desc";
				} else {
					$order_dir = "asc";
				}

				if($total_can_afford < 1){ //error checking
					return "Unable to fill any ships with anything.";
				}

				$used_copy_afford = $total_can_afford; //make copy of the above.
				$final_cost = $item_cost * $total_can_afford; //work out the final cash cost of it all.
				$fill_ships_sql = ""; //intiate sql string to load a bunch of ships at once
				$temp_str = "";

				db2("select ship_id, $item_sql, $item_max_sql as max, ship_name from ${db_name}_ships where ".$sql_where_clause." order by $item_max_sql $order_dir");

				while($ships = dbr2(1)) { //loop through the ships

					$ship_counter++; //increment counter
					$free_space = $ships['max'] - $ships[$item_sql]; //capacity of present ship

					if($free_space < $used_copy_afford) { //can load ship
						$used_copy_afford -= $free_space; //num to use
						$fill_ships_sql .= "ship_id = '$ships[ship_id]' || ";

						$temp_str .= "<br><b class=b1>$ships[ship_name]</b> had its $item_str cargo increased by <b>$free_space</b> to maximum capacity.";

						if($ships['ship_id'] == $user_ship['ship_id']){ //do the user ship too.
							if($cargo_run == 0){ //not cargo bay stuff
								$user_ship[$item_sql] = $user_ship[$item_max_sql];
							} else { //cargo bay stuff
								$user_ship[$item_sql] += $user_ship['empty_bays'];
							}
						}

					} else { //cannot load ship whole ship.
						dbn("update ${db_name}_ships set $item_sql = $item_sql + '$used_copy_afford' where ship_id = '$ships[ship_id]'");

						if($ships['ship_id'] == $user_ship['ship_id'] && $cargo_run == 0){ //do the user ship too.
							$user_ship[$item_sql] += $used_copy_afford;
						} elseif($ships['ship_id'] == $user_ship['ship_id']) { //cargo bay stuff
							$user_ship[$item_sql] += $used_copy_afford;
						}
						$temp_str .= "<br><b class=b1>$ships[ship_name]</b>s <b class=b1>$item_str</b> count was increased by <b>$used_copy_afford</b>.";
						break 1;
					}
				} //end of while

				$ret_str .= "<b>$ship_counter</b> ships had their <b class=b1>$item_str</b> count augmented by more $item_str.<br>Total increase in $item_str = <b>$total_can_afford</b>; Cost = <b>$final_cost</b><p>More Detailed Statistics :".$temp_str;

				//update DB with fully loaded ships.
				if(!empty($fill_ships_sql)){
					$fill_ships_sql = preg_replace("/\|\| $/", "", $fill_ships_sql);
					dbn("update ${db_name}_ships set $item_sql = $item_max_sql where ".$fill_ships_sql);
				}
				take_cash($final_cost); //charge the cash
			}
		}
	}
	return $ret_str; //return the result string.
}


//function that will return a list of the contents of the ships cargo bays.
function bay_storage($ship){
	if(empty($ship['cargo_bays'])) {
		return "\n<b>None</b>";
	}
	$ret_str = "";
	if(!empty($ship['metal'])) {
		$ret_str .= "\n<b>$ship[metal]</b> Metals";
	}
	if(!empty($ship['fuel'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br>";
		}
		$ret_str .= "\n<b>$ship[fuel]</b> Fuels";
	}
	if(!empty($ship['organ'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br>";
		}
		$ret_str .= "\n<b>$ship[organ]</b> Organics";
	}
	if(!empty($ship['elect'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br>";
		}
		$ret_str .= "\n<b>$ship[elect]</b> Electronics";
	}
	if(!empty($ship['colon'])) {
		if(!empty($ret_str)){
			$ret_str .= "<br>";
		}
		$ret_str .= "\n<b>$ship[colon]</b> Colonists";
	}
	empty_bays($ship);
	if($ship['empty_bays'] > 0) {
		if(!empty($ret_str)){
			$ret_str .= "<br>";
		}
		$ret_str .= "\n<b>$ship[empty_bays]</b> Empty";
	}
	return $ret_str;
}

//function that will work out how many flagships this player has got through.
function num_flagships ($num_ships){
	if($num_ships == 0){
		return 0;
	}

	$result_num = 0;
	while($num_ships > 1){
		$num_ships = $num_ships / 2;
		$result_num ++;
	}
	return $result_num;
}

?>
