<?php
if (!defined('PATH_SAVANT')) exit();

if (!function_exists('popupHelp')) {
	include('popup_help.inc.php');
}

?><h1><em><?php 

echo popupHelp("game_info.php?db_name=$gameInfo[db_name]", 600, 450, 
 $gameInfo['name']) . ($gameInfo['status'] === 'running' ? '' : 
 (" (" . esc($gameInfo['status']) . ")"));

?></em></h1>

<?php

if (IS_ADMIN || IS_OWNER) {
	$start = '<a href="admin.php?show_active=1">';
	$end = '</a>';
} else {
	$start = $end = '';
}

echo "<p>$start" . $this->escape($this->activeUsers) . " active user(s)$end" .
 "</p>\n<p>" . date('<\a \t\i\t\l\e="T">M d - H:i</\a>') . "</p>\n";

if ($this->gameStatus === 'running') {
	echo "<p>" . ceil(($this->gameFinishes - time()) / 86400) . 
	 " day(s) left</p>\n";
}

?><h2><em>Places</em></h2>
<ul>
	<li><a href="system.php">Star system</a></li>
	<li><a href="news.php">Game news</a></li>
	<li><a href="player_stat.php">Player ranking</a></li>
</ul>
<ul>
	<li><a href="diary.php">Fleet journal</a></li>
<?php

	if ($this->gameClans) {
?>	<li><a href=\"clan.php\">Clan control</a></li>
<?php
	}

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
		$popup = popupHelp('help.php?popup=1&ship_info=1&shipno=' .
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
?>
