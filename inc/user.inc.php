<?php

//call other generic file
require_once('inc/common.inc.php');
require_once('inc/db.inc.php');

// Check and update the authentication.
if (!(checkAuth() && $db_name !== NULL)) {
	require('logout.php');
	exit();
}


checkPlayer($login_id);

// update last request (so as know when user last requested a page in THIS game.
$db->query('UPDATE [game]_users SET last_request = %u WHERE ' .
 'login_id = %u', array(time(), $user['login_id']));

// load the ship present usership
checkShip();

function pageStart($title)
{
	$data = ob_get_contents();
	if (!empty($data)) {
		ob_end_clean();
	}

	ob_start('ob_gzhandler');

	echo $data;
	print_header($title);

	echo "<div id=\"gameMenu\">\n" . statusBar(). "</div>\n" .
	 "<div id=\"gameBody\">\n";
}

function pageStop()
{
	echo "</div>\n";
	print_footer();
	exit();
}



// function that will print the left column
function statusBar()
{
	global $db, $user, $userShip, $gameInfo, $gameOpt;

	$menu = "<h1><em>" . popup_help("game_info.php?db_name=$gameInfo[db_name]", 600,
	 450, $gameInfo['name']) . ($gameInfo['paused'] == 1 ? ' (paused)' : '') .
	 "</em></h1>\n";

	$uAmount = $db->query('SELECT COUNT(login_id) FROM [game]_users WHERE ' .
	 'login_id > 1 AND last_request > %u', array(time() - 300));
	$activeUsers = (int)current($db->fetchRow($uAmount));
	if (IS_ADMIN || IS_OWNER) {
		$start = '<a href="admin.php?show_active=1">';
		$end = '</a>';
	} else {
		$start = $end = '';
	}
	$menu .= "<p>{$start}$activeUsers active user(s){$end}</p>\n";

	$menu .= "<p>" . date('<\a \t\i\t\l\e="T">M d - H:i</\a>') . "</p>\n";

	if ($gameInfo['paused'] != 1) {
		$menu .= "<p>$gameOpt[count_days_left_in_game] days left</p>\n";
	}


	/**************
	* Left Links
	**************/
	$menu .= <<<END
<h2><em>Places</em></h2>
<ul>
	<li><a href="system.php">Star system</a></li>
	<li><a href="news.php">Game news</a></li>

END;

	if ($gameOpt['max_clans'] > 0 || IS_ADMIN) {
		$menu .= "\t<li><a href=\"clan.php\">Clan control</a></li>\n";
	}

	$menu .= <<<END
	<li><a href="player_stat.php">Player ranking</a></li>
</ul>
<ul>
	<li><a href="diary.php">Fleet diary</a></li>

END;

	$mAmount = $db->query('SELECT COUNT(*) FROM [game]_messages WHERE ' .
	 'login_id = %u', array($user['login_id']));
	$counted = (int)current($db->fetchRow($mAmount));

	$menu .= "\t<li><a href=\"message_inbox.php\">$counted msg(s)</a> - <a href=\"message.php\">send</a></li>\n";

	$fAmount = $db->query('SELECT COUNT(*) FROM [game]_messages WHERE ' .
	 'timestamp > %u AND login_id = -1 AND sender_id != %u',
	 array($user['last_access_forum'], $user['login_id']));
	$counted = (int)current($db->fetchRow($fAmount));

	$temp_forum_text = "";
	if ($counted > 0) {
		$temp_forum_text = " ($counted <a href=\"forum.php?last_time=" .
		 $user['last_access_forum'] . "&amp;find_last=1\">new</a>)";
	}

	$menu .= "\t<li><a href=\"forum.php\">Game forum</a>$temp_forum_text</li>\n";

	if (IS_ADMIN || IS_OWNER) {
		$aForum = $db->query('SELECT last_access_admin_forum FROM ' .
		 '[game]_users WHERE login_id = %u', array($user['login_id']));
		$time_from = (int)current($db->fetchRow($aForum));

		$mCount = $db->query('SELECT COUNT(*) FROM se_central_forum WHERE ' .
		 'timestamp > %u', array($time_from));
		$messageCount = (int)current($db->fetchRow($mCount));
		$adminForumNew = '';
		if($messageCount > 0){
			$adminForumNew = ' (' . $messageCount . ' <a href="forum.php?' .
			 'last_time=' . $time_from . '&amp;view_a_forum=1">new</a>)';
		}
		$menu .= "\t<li><a href=\"forum.php?view_a_forum=1\">Admin forum</a>$adminForumNew</li>\n";
	}

	if (IS_ADMIN) {
		$menu .= "\t<li><a href=\"forum.php?clan_forum=1\">Clan forums</a></li>\n";
	} elseif ($user['clan_id'] !== NULL) {
		$cCount = $db->query('SELECT COUNT(*) FROM [game]_messages WHERE ' .
		 'timestamp > %u AND login_id = -5 AND clan_id = %u AND ' .
		 'sender_id != %u', array($user['last_access_clan_forum'],
		 $user['clan_id'], $user['login_id']));
		$messageCount = (int)current($db->fetchRow($cCount));
		$newMsgs = '';
		if($messageCount > 0){
			 $newMsgs = ' (' . $messageCount . ' <a href="forum.php?' .
			  'clan_forum=1&amp;last_time=' . $user['last_access_clan_forum'] .
			  '&amp;find_last=1">new</a>)';
		}
		$menu .= "\t<li><a href=\"forum.php?clan_forum=1\">" .
		 clanSymbol($user['clan_sym'], $user['clan_sym_color']) .
		 " Forum</a>$newMsgs</li>\n";
	}

	$menu .= <<<END
	<li><a href="http://forum.solar-empire.net/">Global forum</a></li>
</ul>

END;


	$menu .= "<h2><em>" . formatName($user['login_id'], $user['login_name'],
	 $user['clan_id'], $user['clan_sym'], $user['clan_sym_color']) . "</em></h2>\n";

	if (!IS_ADMIN) {
		if ($user['turns_run'] < $gameOpt['turns_safe']) {
			$s_turns = $gameOpt['turns_safe'] - $user['turns_run'];
			$menu .= "<p>$s_turns safe turn(s) left</p>\n";
		} elseif ($user['turns_run'] == $gameOpt['turns_safe']) {
			$menu .= "<p><em>Leaving</em> newbie safety!</p>\n";
		}
	}

	$credits = number_format($user['cash']);

	$menu .= <<<END
<div><table>
	<tr>
		<th>Turns</th>
		<td>$user[turns] / $gameOpt[max_turns]</td>
	</tr>
	<tr>
		<th>Credits</th>
		<td>$credits</td>
	</tr>
	<tr>
		<th>Kills</th>
		<td>$user[ships_killed] / $user[ships_lost]</td>
	</tr>
	<tr>
		<th>Score</th>
		<td>$user[score]</td>
	</tr>
</table></div>
<ul>
	<li><a href="help.php">Help files</a></li>
	<li><a href="options.php">Player options</a></li>

END;

	if (IS_ADMIN || IS_OWNER) {
		$menu .= "\t<li><a href=\"admin.php\">Game admin</a></li>\n";
		if (IS_OWNER) {
			$menu .= "\t<li><a href=\"owner.php\">Server info</a></li>\n";
		}
	}

	$menu .= <<<END
	<li><a href="logout.php?logout_single_game=1">Game list</a></li>
	<li><a href="logout.php?comp_logout=1">Logout</a></li>
</ul>

END;

	if ($user['ship_id'] === NULL) {
		$menu .= <<<END
<h2><em>Your ship is destroyed!</em></h2>
<p><a href="http://localhost/dev/se/syswars/earth.php">Buy one</a> to 
continue playing.</p>

END;
	} else {
		$popup = popup_help('help.php?popup=1&ship_info=1&shipno=' .
		 $userShip['type_id'], 300, 600, $userShip['ship_name']);
		$config = empty($userShip['config']) ? '<em>none</em>' :
		 $userShip['config'];
		$storage = bay_storage($userShip);

		$menu .= <<<END
<h2><em>$popup</em></h2>
<div><table>
	<tr>
		<th>Class</th>
		<td>$userShip[class_name]</td>
	</tr>
	<tr>
		<th>Hull</th>
		<td>$userShip[hull] / $userShip[max_hull]</td>
	</tr>
	<tr>
		<th>Shields</th>
		<td>$userShip[shields] / $userShip[max_shields]</td>
	</tr>
	<tr>
		<th>Fighters</th>
		<td>$userShip[fighters] / $userShip[max_fighters]</td>
	</tr>
	<tr>
		<th>Specials</th>
		<td>$config</td>
	</tr>
	<tr>
		<th>Storage</th>
		<td>$storage</td>
	</tr>
</table></div>

END;
	}


	return $menu;
}


#function that calls many other functions to result in a printed out page..
function print_page($title, $text)
{
	pageStart($title);
	echo $text;
	pageStop();
}



//function that can be used create a viable input form. Adds hidden vars.
function get_var($title, $page_name, $text, $var_name, $var_default)
{
	pageStart($title);
	echo <<<END
<p>$text</p>
<form name="get_var_form" action="$page_name" method="post">
END;
	foreach ($_REQUEST as $var => $value) {
		echo "\t<input type=\"hidden\" name=\"" . esc($var) . "\" value=\"" .
		 esc($value) . "\" />\n";
	}

	switch ($var_name) {
	    case 'sure':
	    	echo <<<END
	<p><input type="hidden" name="sure" value="yes" />
	<input type="submit" name="submit" value="Yes" class="button" /> -
	<input type="button" onclick="history.back()" value="No" class="button" /></p>

END;
	        break;
	    case 'passwd':
	    case 'passwd2':
	    case 'passwd_verify':
	    	echo <<<END
	<p><input type="password" name="$var_name" value="$var_default" />
	<input type="submit" name="submit" value="Submit" class="button" /></p>

END;
	        break;
	    case 'text':
	    	echo <<<END
	<p><textarea name="$var_name" cols="50" rows="20">$var_default</textarea></p>
	<p><input type="submit" name="submit" value="Submit" class="button" /></p>

END;
	        break;
	    default:
	    	echo <<<END
	<p><input type="text" name="$var_name" value="$var_default" class="text" />
	<input type="submit" name="submit" value="Submit" class="button" /></p>

END;
	}
	echo "</form>\n";

	pageStop();
}

function checkShip()
{
	global $db, $user, $userShip;

	$userShip = userShip($user['ship_id']);
	if ($userShip === NULL) {
		$oQuery = $db->query('SELECT ship_id FROM [game]_ships WHERE ' .
		 'login_id = %u ORDER BY RAND()', array($user['login_id']));

		if ($other = $db->fetchRow($oQuery)) {
			$user['ship_id'] = $other['ship_id'];
			$userShip = userShip($user['ship_id']);
			$db->query('UPDATE [game]_users SET ship_id = %u WHERE ' .
			 'login_id = %u', array($user['ship_id'], $user['login_id']));
		} elseif ($user['ship_id'] !== NULL) {
			$user['ship_id'] = NULL;
			$db->query('UPDATE [game]_users SET ship_id = NULL WHERE ' .
			 'login_id = %u', array($user['login_id']));
		}
	}
}


// ACCOUNT UPDATING FUNCTIONS

//function that charges turns for something. Admin is exempt.
function giveTurns($id, $amount)
{
	global $db, $gameInfo;

	if ($id == $gameInfo['admin'] || $amount == 0) {
		return true;
	}

	if ($amount > 0) {
		$gave = $db->query('UPDATE [game]_users SET turns = turns + %u ' .
		 'WHERE login_id = %u', array($amount, $id));
	} else {
		$amount = abs($amount);
		$gave = $db->query('UPDATE [game]_users SET turns = turns - %u, ' .
		 'turns_run = turns_run + %u WHERE login_id = %u AND turns >= %u',
		 array($amount, $amount, $id, $amount));
	}

	return $db->affectedRows($gave) ? true : false;
}

function giveTurnsPlayer($amount)
{
	global $user;

	if (giveTurns($user['login_id'], $amount)) {
		if (!IS_ADMIN) {
			$user['turns'] += $amount;
			$user['turns_run'] -= $amount;
		}
		return true;
	}

	return false;
}

function checkPlayer($id)
{
	global $user, $userOpt, $db;

	$uQuery = $db->query('SELECT u.*, c.symbol AS clan_sym, ' .
	 'c.sym_color AS clan_sym_color FROM [game]_users AS u ' .
	 'LEFT JOIN [game]_clans AS c ON u.clan_id = c.clan_id WHERE ' .
	 'login_id = %u', array($id));
	$user = $db->fetchRow($uQuery);

	if (!is_array($user)) {
		header('Location: index.php');
		exit();
	}

	$oQuery = $db->query('SELECT * FROM [game]_user_options WHERE ' .
	 'login_id = %u', array($user['login_id']));
	$userOpt = $db->fetchRow($oQuery);
}


//function that can give a user cash. Admin is exempt.
function giveMoney($id, $amount)
{
	global $db, $gameInfo;

	if ($id == $gameInfo['admin'] || $amount == 0) {
		return true;
	}

	if ($amount > 0) {
		$gave = $db->query('UPDATE [game]_users SET cash = cash + %u ' .
		 'WHERE login_id = %u', array($amount, $id));
	} else {
		$amount = abs($amount);
		$gave = $db->query('UPDATE [game]_users SET cash = cash - %u ' .
		 'WHERE login_id = %u AND cash >= %u', array($amount, $id, $amount));
	}

	return $db->affectedRows($gave) ? true : false;
}

function giveMoneyPlayer($amount)
{
	global $user;

	if (giveMoney($user['login_id'], $amount)) {
		if (!IS_ADMIN) {
			$user['cash'] += $amount;
		}
		return true;
	}

	return false;
}


// MESSAGE FUNCTIONS

//sends $text to $to, from global $user
function send_message($to, $text)
{
	global $db, $user;

	$newId = newId('[game]_messages', 'message_id');

	if ($to == -5 && $user['clan_id'] !== NULL) {
		$db->query('INSERT INTO [game]_messages (message_id, timestamp, ' .
		 'sender_name, sender_id, login_id, text, clan_id) VALUES ' .
		 '(%u, %u, \'%s\', %u, %d, \'%s\', %u)', array($newId, time(),
		 $db->escape($user['login_name']), $user['login_id'], $to,
		 $db->escape($text), $user['clan_id']));
	} else {
		$db->query('INSERT INTO [game]_messages (message_id, timestamp, ' .
		 'sender_name, sender_id, login_id, text) VALUES ' .
		 '(%u, %u, \'%s\', %u, %d, \'%s\')', array($newId, time(),
		 $db->escape($user['login_name']), $user['login_id'], $to,
		 $db->escape($text)));
	}
}

function print_messages($for, $full = false)
{
	global $db, $user, $error_str, $userOpt, $last_time, $prevdays,
	 $bug_board, $allow_signatures, $find_last;

	$sig = $allow_signatures && $userOpt['show_sigs'] ? ', u.sig' : '';
	$prev = isset($prevdays);

	$gForum = $for == -1;
	$cForum = $for == -5;
	$forum = $gForum ? 'forum' : ($cForum ? 'clan_forum' : '');
	$isForum = $forum !== '';

	if ($prev) {
		$forum_secs = $userOpt['forum_back'] * 5000;
		$last_time -= $forum_secs;
	} else if ($isForum && !$find_last) {
		$forum_secs = $userOpt['forum_back'] * 3600;
		$last_time = time() - $forum_secs;
	}

	$baseQuery = 'SELECT m.message_id, m.timestamp, m.text, ' .
	 'm.sender_id, u.login_id, u.clan_id, c.sym_color AS ' .
	 'clan_sym_color, c.symbol AS clan_sym, m.sender_name' . $sig .
	 ' FROM [game]_messages AS m ' .
	 'LEFT JOIN [game]_users AS u ON m.sender_id = u.login_id ' .
	 'LEFT JOIN user_accounts AS a ON u.login_id = a.login_id ' .
	 'LEFT JOIN [game]_clans AS c ON u.clan_id = c.clan_id ' .
	 'WHERE m.login_id = %d ';
	$baseArgs = array($for);

	if (isset($last_time)) {
		if ($prev && isset($forum_secs)) {
		    $baseQuery .= 'AND timestamp > %u and timestamp <= %u ';
		    $baseArgs[] = $last_time - $forum_secs;
		    $baseArgs[] = $last_time;
		} elseif ($find_last) {
		    $baseQuery .= 'AND timestamp > %u ';
			$baseArgs[] = $last_time;
		} elseif (isset($forum_secs)) {
		    $baseQuery .= 'AND timestamp > %u ';
		    $baseArgs[] = $last_time - $forum_secs;
		}
	}

	if ($cForum) {
		$baseQuery .= 'and m.clan_id = %u ';
		$baseArgs[] = $user['clan_id'];
	}

	$baseQuery .= 'ORDER BY timestamp DESC';

	/* Display */
	$list = $db->query($baseQuery, $baseArgs);
	$amount = $db->numRows($list);

	if ($amount > 0) {
	    $msgStr = formatMsgs($list, $full, $isForum ? ($cForum ? 'clan' : 'game') : 'personal');
	} else {
	    $msgStr = "<p>No messages</p>\n";
	}

	if ($isForum) {
		$db->query('UPDATE [game]_users SET last_access_%s = %u ' .
		 'WHERE login_id = %u', array($forum,
		 $user["last_access_$forum"] = time(), $user['login_id']));
	}

	if($gForum) {
		$count = $db->query('SELECT COUNT(message_id) FROM [game]_messages ' .
		 'WHERE login_id = -1 AND timestamp < %u', array($last_time));
		$num_mes_prev = (int)current($db->fetchRow($count));
		$msgStr .= "<p><a href=\"forum.php?last_time=$last_time&prevdays=yes\">Previous $userOpt[forum_back] Hours</a></p>";

		if (IS_ADMIN) {
		    $msgStr .= "<p><a href=\"forum.php?killallmsg=1\">Delete All Forum Messages</a></p>\n";
		}
	}

	return $msgStr;
}

function formatMsgs($list, $full = false, $type = 'forum')
{
	global $db, $userOpt, $smTImplode, $smSImplode, $user;

	$checkboxes = 4 < $db->numRows($list);

	$page = $type === 'personal' ? 'message_inbox' : 'forum';

	$msgStr = '<dl>';
	//cursing filter

	$filter = array();
	switch ((int)$userOpt['cursing_filter']) {
		case 2: // all rudeness
			$filter = array_merge($filter, array('gay', 'crap', 'damn',
			 'hore', 'bastard', 'cock', 'faggot'));
		case 1: // worse words
			$filter = array_merge($filter, array('fuck', 'cunt', 'dick', 'piss',
			 'nigger', 'bitch', 'shit', 'wank', 'bugger'));
	}

	while ($msg = $db->fetchRow($list)) {
		if (!empty($filter)) {
			$msg['text'] = preg_replace('/(\w*(?:' . implode('|', $filter) . ')\w*)/ie',
			 'str_repeat(\'*\', strlen(\'\1\'))', $msg['text']);
		}
		$msg['text'] = preg_replace('/\[(' . $smSImplode . ')(' . $smTImplode .
		 ')\]/i', '<img src="img/smiles/\1\2.gif" alt="\1 \2" />', $msg['text']);

		$msgStr .= "\n\t<dt>" . date('M d - H:i', $msg['timestamp']) .
		 ' ' . formatName($msg['sender_id'], $msg['sender_name'],
		 $msg['clan_id'], $msg['clan_sym'], $msg['clan_sym_color']) .
		 "</dt>\n\t<dd>" . $msg['text'] . "\n\t<p>" .
		 (empty($msg['sig']) ? '' : $msg['sig'] . ' - ') .
		 ($msg['sender_id'] === NULL ? '' : "<a href=\"message.php?target=$msg[sender_id]&amp;reply_to=" .
		 $msg['message_id'] . "\">Reply</a> - ") . "\n\t<a href=\"diary.php?log_ent=" .
		 $msg['message_id'] . "\">Log</a>";

		if ($full) {
			$msgStr .= " - <a href=\"$page.php?killmsg=$msg[message_id]" .
			 (IS_ADMIN && $type === 'clan' ?
			 "&amp;look_at=$user[clan_id]&amp;clan_forum=1" : '') .
			 "\">Delete</a>";
			if ($checkboxes) {
				$msgStr .= " - <input type=\"checkbox\" name=\"del_mess[$msg[message_id]]\" value=\"$msg[message_id]\" />";
			}
		}

		$msgStr .= "</p></dd>";
	}
	$msgStr .= "\n</dl>";

	if ($full && $checkboxes) {
		$clan = !($type === 'clan' && IS_ADMIN) ? '' : <<<END

	<input type="hidden" name="clan_forum" value="1" />
	<input type="hidden" name="look_at" value="{$user['clan_id']}" />
END;

		$msgStr = <<<END
<p><a href="$page.php?killallmsg=1">Delete All Messages</a></p>
<form method="post" action="$page.php" id="messag_form">
$msgStr
	<p><a href="#" onclick="tickInvert('messag_form');">Invert Message Selection</a> -
	<input type="submit" value="Delete selected" class="button" />
	<input type="hidden" name="clear_messages" value="1">$clan</p>
</form>

END;
	}

	return $msgStr;
}

//Retires $target
function retire_user($target)
{
	global $db, $gameInfo;

	$name = $db->query('SELECT login_name FROM [game]_users WHERE ' .
	 'login_id = %u', array($target));

	post_news(current($db->fetchRow($name)) . ' retired from the game');

	$db->query('DELETE FROM [game]_ships WHERE login_id = %u',
	 array($target));
	$db->query('UPDATE [game]_bilkos SET bidder_id = 0, ' .
	 'timestamp = %u WHERE bidder_id = %u', array(time(), $target));
	$db->query('UPDATE [game]_planets SET login_id = NULL, pass= \'\' ' .
	 'WHERE login_id = %u', array($target));
	$db->query('DELETE FROM [game]_user_options WHERE login_id = %u',
	 array($target));
	$db->query('DELETE FROM [game]_users WHERE login_id = %u',
	 array($target));

	switch ($target) {
		case $gameInfo['admin']:
		case OWNER_ID:
			break;
		default:
			$db->query('DELETE FROM [game]_diary WHERE login_id = %u',
			 array($target));
			$db->query('DELETE FROM [game]_messages WHERE login_id = %u',
			 array($target));
	}

	return true;
}


/********************
Get Information
*********************/

// retrieve the star data
function &get_star()
{
	global $userShip, $star, $db;

	$sQuery = $db->query('SELECT * FROM [game]_stars WHERE star_id = %u',
	 array($userShip['location']));
    $star = $db->fetchRow($sQuery);

	return $star;
}


//get distance between stars $s1 and $s2
function get_star_dist($s1,$s2)
{
	global $db;

	if (!(isset($s1) && isset($s2))) {
		return 0;
	}

	$stars = $db->query("SELECT x, y FROM [game]_stars WHERE star_id = %u OR star_id = %u", array($s1, $s2));
	$star1 = $db->fetchRow($stars);
	$star2 = $db->fetchRow($stars);

	$dist = ceil(sqrt(pow($star1['x'] - $star2['x'], 2) + pow($star1['y'] - $star2['y'], 2)));

	return $dist;
}

//function to check if a player is dead and out during sudden death.
function deathCheck($user)
{
	global $sudden_death, $gameInf;

	if ($user['ship_id'] === NULL) {
		if ($sudden_death && $user['login_id'] != $gameInfo['admin']) {
		    pageStart('Sudden death');
		    echo "You have no ship, and this game is Sudden Death. <br />As such you are out of the game. <br />You may still access the Forum, and send/recieve private messages though.";
		    pageStop();
		}

		return true;
	}

	return false;
}

//Choose a system at random
function random_system_num($userId)
{
	global $db, $user;

	$randomId = $db->query('SELECT star_id FROM [game]_stars AS s ' .
	 'LEFT JOIN [game]_planets AS p ON s.star_id = p.location ' .
	 'WHERE p.planet_id IS NULL OR p.login_id = %u ORDER BY RAND() ' .
	 'LIMIT 1', array($userId));

	if (($sys = $db->fetchRow($randomId)) === false) {
		return 1;
	}

	return (int)current($sys);
}



// Returns amount of hostile planets in the system
function attack_planet_check()
{
	global $db, $user, $userShip;

	$args = array($user['login_id'], $userShip['location']);
	if ($user['clan_id'] === NULL) {
	    $clan = 'IS NULL';
	} else {
	    $clan = '!= %u';
	    $args[] = $user['clan_id'];
	}

	$hostile = $db->query('SELECT COUNT(*) FROM [game]_planets AS p ' .
	 'LEFT JOIN [game]_users AS u ON p.login_id = u.login_id ' .
	 'WHERE fighter_set = 1 AND fighters > 0 AND p.login_id != %u AND ' .
	 'p.location = %u AND u.clan_id ' . $clan .
	 ' ORDER BY fighter_set DESC, fighters DESC LIMIT 1', $args);

	return (int)current($db->fetchRow($hostile, ROW_NUMERIC));
}



//load ship types from database.
function load_ship_types()
{
	global $db, $fighter_cost_earth;
	$ship_types = array();

	$sInfo = $db->query('SELECT * FROM [game]_ship_types WHERE ' .
	 'auction != 1 ORDER BY type_id');

	while($this_type = $db->fetchRow($sInfo)) {
		$this_type['cost'] += $this_type['fighters'] * $fighter_cost_earth;
		$ship_types[$this_type['type_id']] = $this_type;
	}

	return $ship_types;
}

//A function that gets all the details for the user's new ship, and returns the completed user_ship array.
function userShip($id)
{
	global $db, $user;

	if ($id === NULL) {
	    return false;
	}

	return getShip($id);
}

function getShip($id)
{
	global $db;

	$sQuery = $db->query('SELECT s.*, t.name AS class_name, ' .
	 't.abbr AS class_abbr, t.appearance, u.login_name, u.turns_run ' .
	 'FROM [game]_ships AS s LEFT JOIN [game]_ship_types AS t ON ' .
	 's.type_id = t.type_id LEFT JOIN [game]_users AS u ON ' .
	 's.login_id = u.login_id WHERE s.ship_id = %u', array($id));

	if (!$sInfo = $db->fetchRow($sQuery)) {
		return false;
	}

	empty_bays($sInfo);

	return $sInfo;
}

//a function that allows a message to be sent to all players.
function message_all_players($text, $game_db, $recipients, $sender)
{
	global $user, $db;

	$userId = $db->query('SELECT login_id FROM ' . $game_db . '_users');

	while ($players = $db->fetchRow($userId, ROW_ASSOC)) {
		$newId = newId('[game]_messages', 'message_id');
		$db->query('INSERT INTO ' . $game_db . '_messages (message_id, ' .
		 'timestamp, sender_name, sender_id, login_id, text) VALUES (%u, ' .
		 '%u, \'%s\', %u, %u, \'%s\')', array($newId, time(), $db->escape($user['login_name']), $user['login_id'],
		 $players['login_id'], $db->escape("<p>Message to " .
		 "<b class=b1>$recipients</b> from $sender:</p>\n<p>$text</p>")));
	}

	return "Message sent to all players in <b>$game_db</b>.";
}



//a function to allow for easy addition of upgrades.
function make_standard_upgrade($upgrade_str, $config_addon, $cost,
 $developement_id)
{
	global $user, $userShip, $db;
	if (shipHas($userShip, $config_addon)){
		return "Your ship is already equipped with a <b class=b1>$upgrade_str</b>.<br />There is no point in having more than one on a ship.<p>";
	} elseif ($userShip['upgrades'] < 1){
		return '';
	} elseif (!giveMoneyPlayer(-$cost)) {
		return "You can not afford to buy a <b class=b1>$upgrade_str</b>.<p>";
	} else {
		$configArr = explode(':', $userShip['config']);
		if (empty($configArr[0])) {
			$configArr[0] = $config_addon;
		} else {
			$configArr[] = $config_addon;
		}
		
		$userShip['config'] = implode(':', $configArr);

		$db->query("update [game]_ships set config = '$userShip[config] ', upgrades = upgrades - 1 where ship_id = '$user[ship_id]'");

		--$userShip['upgrades'];

		return "<b class=b1>$upgrade_str</b>, purchased and fitted to the <b class=b1>$userShip[ship_name]</b> for <b>$cost</b> Credits.<p>";
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
	global $user, $userShip, $db, $sure, $fill_dir;

	$ret_str = "";
	$taken = 0; //item taken from earth far.
	$ship_counter = 0; //ships passed through

	if($cargo_run == 1){ //cargo
		$sql_max_check = $item_max_sql;
		$sql_where_clause = " location = '$userShip[location]' AND login_id='$user[login_id]' AND $item_max_sql > 0 ";
	} else {//not cargo
		$sql_max_check = "($item_max_sql - $item_sql)";
		$sql_where_clause = " location = '$userShip[location]' AND login_id='$user[login_id]' AND $item_max_sql > 0 AND $item_sql < $item_max_sql ";
	}

	//elect all viable ships
	$mQuery = $db->query("select sum($sql_max_check) as total_capacity, count(ship_id) as total_ships from [game]_ships where ".$sql_where_clause);
	$maths = $db->fetchRow($mQuery);

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
				$db->query("update [game]_ships set $item_sql = $item_max_sql where ".$sql_where_clause);
				giveMoneyPlayer(-$total_cost);

				if($cargo_run == 0){ //not cargo bay stuff
					$userShip[$item_sql] = $userShip[$item_max_sql];
				} else { //cargo bay stuff
					$userShip[$item_sql] += $userShip['empty_bays'];
				}

				$ret_str .= "<b>$maths[total_capacity]</b> <b class=b1>$item_str</b> were added to <b>$maths[total_ships]</b> ships.<br />All ships are now at maximum capacity.";
			}

		//user CANNOT afford to fill the whole fleet, so we'll have to do it the hard way.
		} else {
			$total_can_afford = floor($user['cash'] / $item_cost); //work out amount can afford.

			if(empty($sure)) { //confirmation
				$extra_text = "<p><input type=radio name=fill_dir value=1 CHECKED> - Fill highest capacity ships ships first.";
				$extra_text .= "<br /><input type=radio name=fill_dir value=2> - Fill lowest capacity ships first.";
				get_var('Load ships',$script_name,"There is capacity for <b>$maths[total_capacity]</b> <b class=b1>$item_str</b> in <b>$maths[total_ships]</b> ships in this system. <br />However, you can only afford <b>$total_can_afford</b> $item_str.<p>Do you want to fill as many ships as you can afford to fill?".$extra_text,'sure','yes');
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

				$sQuery = $db->query("select ship_id, $item_sql, $item_max_sql as max, ship_name from [game]_ships where ".$sql_where_clause." order by $item_max_sql $order_dir");

				while($ships = $db->fetchRow($sQuery)) { //loop through the ships
					++$ship_counter; //increment counter
					$free_space = $ships['max'] - $ships[$item_sql]; //capacity of present ship

					if($free_space < $used_copy_afford) { //can load ship
						$used_copy_afford -= $free_space; //num to use
						$fill_ships_sql .= "ship_id = '$ships[ship_id]' OR ";

						$temp_str .= "<br /><b class=b1>$ships[ship_name]</b> had its $item_str cargo increased by <b>$free_space</b> to maximum capacity.";

						if($ships['ship_id'] == $userShip['ship_id']){ //do the user ship too.
							if($cargo_run == 0){ //not cargo bay stuff
								$userShip[$item_sql] = $userShip[$item_max_sql];
							} else { //cargo bay stuff
								$userShip[$item_sql] += $userShip['empty_bays'];
							}
						}

					} else { //cannot load ship whole ship.
						$db->query("update [game]_ships set $item_sql = $item_sql + '$used_copy_afford' where ship_id = '$ships[ship_id]'");

						if($ships['ship_id'] == $userShip['ship_id'] && $cargo_run == 0){ //do the user ship too.
							$userShip[$item_sql] += $used_copy_afford;
						} elseif($ships['ship_id'] == $userShip['ship_id']) { //cargo bay stuff
							$userShip[$item_sql] += $used_copy_afford;
						}
						$temp_str .= "<br /><b class=b1>$ships[ship_name]</b>s <b class=b1>$item_str</b> count was increased by <b>$used_copy_afford</b>.";
						break 1;
					}
				} //end of while

				$ret_str .= "<b>$ship_counter</b> ships had their <b class=b1>$item_str</b> count augmented by more $item_str.<br />Total increase in $item_str = <b>$total_can_afford</b>; Cost = <b>$final_cost</b><p>More Detailed Statistics :".$temp_str;

				//update DB with fully loaded ships.
				if(!empty($fill_ships_sql)){
					$fill_ships_sql = preg_replace("/\|\| $/", "", $fill_ships_sql);
					$db->query("update [game]_ships set $item_sql = $item_max_sql where ".$fill_ships_sql);
				}

				giveMoneyPlayer(-$final_cost); //charge the cash
			}
		}
	}
	return $ret_str; //return the result string.
}


//function that will return a list of the contents of the ships cargo bays.
function bay_storage($ship)
{
	if(empty($ship['cargo_bays'])) {
		return "none";
	}

	empty_bays($ship);

	$types = array();
	if(!empty($ship['metal'])) {
		$types[] = $ship['metal'] . ' metals';
	}
	if(!empty($ship['fuel'])) {
		$types[] = $ship['fuel'] . ' fuels';
	}
	if(!empty($ship['organ'])) {
		$types[] = $ship['organ'] . ' organics';
	}
	if(!empty($ship['elect'])) {
		$types[] = $ship['elect'] . ' electronics';
	}
	if(!empty($ship['colon'])) {
		$types[] = $ship['colon'] . ' colonists';
	}
	if ($ship['empty_bays'] > 0) {
		$types[] = $ship['empty_bays'] . ' empty';
	}

	return implode("<br />\n", $types);
}

function shipHas($ship, $config)
{
	return strpos($ship['config'], $config) !== false;
}

function starExists($id)
{
	global $db;

	$starExists = $db->query('SELECT COUNT(*) FROM [game]_stars WHERE ' .
	 'star_id = %u', array($id));

	return current($db->fetchRow($starExists)) != 0;
}

function towedByFleet($fleet, $location, $global = false)
{
	global $db;

	$list = array();


	$args = $fleet;
	if (!$global) {
		$args[] = $location;
	}

	$ships = $db->query('SELECT ship_id FROM [game]_ships WHERE ' .
	 'towed_by = %u' . str_repeat(' OR towed_by = %u', count($fleet) - 1) . 
	 ($global ? '' : ' AND location = %u'), $args);

	while ($ship = $db->fetchRow($ships, ROW_NUMERIC)) {
		$list[] = $ship[0];
	}

	if (!empty($list)) {
		return array_merge($list, towedByFleet($list, $location, $global));
	}

	return $list;
}

function towedByShip($ship, $global = false)
{
	global $db;

	return towedByFleet(array($ship['ship_id']), $ship['location'], $global);
}

function moveShipTo($ship, $sys)
{
	global $db;

	$args = array_merge(array($sys, $ship['ship_id']), towedByShip($ship));
	$moved = $db->query('UPDATE [game]_ships SET location = %u WHERE ' .
	 'ship_id = %u' . str_repeat(' OR ship_id = %u ', count($args) - 2),
	 $args);

	return $db->affectedRows($moved);
}

function moveUserTo($sys)
{
	global $userShip;

	$amount = moveShipTo($userShip, $sys);

	checkShip();

	return $amount;
}

function closestShip($playerId, $x, $y)
{
	global $db;
	$new = $db->query('SELECT s.ship_id FROM [game]_ships AS s ' .
	 'INNER JOIN [game]_stars AS l ON s.location = l.star_id ' .
	 'INNER JOIN [game]_users AS u ON s.login_id = u.login_id ' .
	 'WHERE s.login_id = %u && u.ship_id != s.ship_id ORDER BY ' .
	 '(POWER(l.x - %u, 2) + POWER(l.y - %u, 2)) ASC, RAND() LIMIT 1',
	 array($playerId, $x, $y));

	return $db->numRows($new) > 0 ? (int)current($db->fetchRow($new)) : false;
}

?>
