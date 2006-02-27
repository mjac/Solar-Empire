<?php

require_once('inc/user.inc.php');

$out = '';

if (isset($clan_forum)) {
	if($user['clan_id'] === NULL && !IS_ADMIN){
		print_page('Clan forum', 'You must be in a clan to access this page.');
	}
	if (isset($killmsg) && IS_ADMIN) {
		$db->query('DELETE FROM [game]_messages WHERE message_id = %u AND ' .
		 'login_id = -5', array($killmsg));
	}

	if(isset($look_at) && IS_ADMIN){
		$realClan = $user['clan_id'];
		$user['clan_id'] = (int)$look_at;
	}

    if ($user['clan_id'] !== NULL) {
		db("SELECT clan_name, symbol, sym_color FROM [game]_clans where clan_id = $user[clan_id]");
		$clan = dbr(1);

		$out .= "<h1>" . clanName($clan['clan_name'], $clan['symbol'],
		 $clan['sym_color']) . " forum</h1>\n";
		if (IS_ADMIN) {
			$out .= "<p><a href=\"message.php?target=-5&amp;clan_id=$user[clan_id]\">Post to Clan Forum</a></p>\n";
		} else {
			$out .= "<p><a href=\"message.php?target=-5\">Post to Clan Forum</a></p>\n";
		}

		$out .= print_messages(-5, IS_ADMIN);
	}



	if (IS_ADMIN) {
		$cList = $db->query('SELECT clan_name, clan_id FROM ' .
		 '[game]_clans ORDER BY clan_name');
		if ($db->numRows($cList) > 0) {
			$out .= <<<END
<h2>Monitor any clan forum</h2>
<form action="forum.php" method="get">
	<p><input type="hidden" name="clan_forum" value="1" />
	<select name="look_at">

END;

			while ($clan = $db->fetchRow($cList)) {
				$out .= "\t\t<option value=\"" . $clan['clan_id'] . '"' .
				 ($clan['clan_id'] == $user['clan_id'] ? " selected=\"\"" :
				 "") . ">" . esc($clan['clan_name']) . "</option>\n";
			}
			$out .= <<<END
	</select> <input type="submit" value="Monitor" class="button" /></p>
</form>

END;
		} else {
			$out .= "<p>There are no clans in this game at present.</p>";
			print_page("Clan Forum", $out);
		}
	}

	if (isset($look_at) && IS_ADMIN) {
		$user['clan_id'] = $realClan;
	}

	print_page("Clan Forum", $out);

//admin only forum
} elseif (isset($view_a_forum) && (IS_ADMIN || IS_OWNER)) {
	$out .= "<h1>Global administration forum</h1>\n";
	if(isset($last_time)){
		$extra_where = 'timestamp > ' . $last_time;
	} else {
		$time = time() - (36 * 3600); //furthest back forum can go
		$extra_where = 'timestamp > ' . $time;
	}

	$out .= "<a href=message.php?target=-99>Post to Forum</a><p>";

	$gQuery = $db->query("SELECT *, NULL AS sender_id, NULL AS clan_id, NULL AS clan_sym, NULL AS clan_sym_color FROM se_central_forum WHERE $extra_where ORDER BY timestamp DESC");
	$out .= formatMsgs($gQuery);
	if (IS_ADMIN || IS_OWNER) {
		$db->query('UPDATE [game]_users SET last_access_admin_forum = %u ' .
		 'WHERE login_id = %u', array(time(), $user['login_id']));
	}

	print_page('Admin Forum',$out);
}


if (isset($killmsg) && IS_ADMIN) {
	$db->query('DELETE FROM [game]_messages WHERE message_id = %u AND ' .
	 'login_id = -1', array($killmsg));
}

if (isset($killallmsg) && IS_ADMIN) {
	if (!isset($sure)) {
		get_var('Delete Messages', 'forum.php', 'Are you sure you want delete all Forum messages?', 'sure', 'yes');
	} else {
		$db->query('DELETE FROM [game]_messages WHERE login_id = -1');
	}
}

$out .= "<h1>Game forum</h1>\n";
if($user['last_access_forum'] > 0){
	if (!isset($find_last)) {
		$out .= "<p><a href=\"forum.php?last_time={$user['last_access_forum']}&find_last=1\">Show new posts</a></p>\n";
	} else {
		$out .= "<p><a href=\"forum.php\">Show all posts</a></p>\n";
	}
}

$out .= "<p><a href=message.php?target=-1>Post to Forum</a> - " .
 "<a href=\"message_codes.php\">Message syntax</a></p>\n";

$out .= print_messages(-1, IS_ADMIN);

print_page('Forum', $out);

?>
