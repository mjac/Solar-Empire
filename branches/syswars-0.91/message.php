<?php

require_once('inc/user.inc.php');

$target = isset($target) ? (int)$target : 0;

// message checks
if ($target == -2 && $user['clan_id'] !== NULL && $clan_id !== $user['clan_id']) {
	print_page("Send Clan Message","You can only send clan messages to your own clan.");
} elseif ($target == -2 && $user['clan_id'] === NULL) {
	print_page("Send Clan Message","You can may not send a message to this clan.");
} elseif ($target == -4 && !(IS_ADMIN || IS_OWNER)) {
	print_page("Send Mass Message","You are not the Admin.");
} elseif ($target == -5 && $user['clan_id'] === NULL && !IS_ADMIN) {
	print_page("Clan Forum","You are not in a clan.");
} elseif ($target == -99 && !(IS_ADMIN || IS_OWNER)) {
	print_page("Admin Forum","You are not an Admin.");
} elseif (empty($text)) {
	if($target > 0){
		$tInfo = $db->query('SELECT u.login_name, u.login_id, ' .
		 'c.clan_id, c.symbol, c.sym_color FROM [game]_users ' .
		 'AS u LEFT JOIN [game]_clans AS c ON u.clan_id = ' .
		 'u.clan_id WHERE login_id = %u', array($target));
		if (!$info = $db->fetchRow($tInfo)) {
			print_page('Invalid player', '<p>That is an invalid player.</p>');
		}

		$rec = formatName($info['login_id'], $info['login_name'],
		 $info['clan_id'], $info['symbol'], $info['sym_color']);
	} elseif ($target== -1) {
		$rec = "the Forum";
	} elseif ($target== -2) {
		$rec = "your Clan";
	} elseif ($target== -3) {
		$rec = "the Bug Board";
	} elseif ($target== -4) {
		$rec = "All the Players";
	} elseif ($target== -5) {
		$rec = "your Clan Forum";
	} elseif ($target== -99) {
		$rec = "Admin Forum";
	} else {
		$ostr = <<<END
<h1>Send a message</h1>
<form name="get_var_form" action="$self" method="post">
	<p><select name="target" id="target">

END;
		$pQuery = $db->query('SELECT * FROM [game]_users WHERE ' .
		 'login_id != %u ORDER BY login_name', array($login_id));
		while ($person = $db->fetchRow($pQuery)) {
			$ostr .= "\t\t<option value=\"{$person['login_id']}\">" .
			 $person['login_name'] . "</option>\n";
		}

		$ostr .= <<<END
	</select> <label for="target">Target</label></p>
	<p><textarea name="text" cols="50" rows="20"></textarea></p>
	<p><input type="submit" value="Submit" class="button" /></p>
</form>
END;
		print_page('Send a message to someone', $ostr);
	}

	if (isset($reply_to) && $reply_to && $target > 0) {#reply to with original text
		$rs = "<p><a href=\"message_inbox.php\">Return to messages</p>\n";
		if ($user['login_id'] == -1) { // forum
			$rText = $db->query('SELECT text FROM [game]_messages WHERE ' .
			 'message_id = %u AND login_id = -1', array($reply_to));
		} else {
			$rText = $db->query('SELECT text FROM [game]_messages WHERE ' .
			 'message_id = %u AND (login_id = -1 OR login_id = %u OR ' .
			 '(login_id = -5 AND clan_id = %u))', array($reply_to,
			 $user['login_id'], $user['clan_id']));
		}
		$reply_to = $db->fetchRow($rText);
		get_var('Send Message','message.php',"Original Message from $rec:<br /><blockquote><hr><br />{$reply_to['text']}<br /><hr></blockquote><br />What is your reply?",'text','');
	} else { #no original text
		get_var('Send Message','message.php',"What is your message to $rec?",'text','');
	}
} elseif ($target != 0) {
	$text = msgToHTML($text);

#send message
	if($target == -2) {
		db2("select login_id from [game]_users where clan_id='$clan_id' AND clan_id > 0");
		$target_member = dbr2(1);
		while($target_member) {
			send_message($target_member['login_id'], $text);
			$target_member = dbr2(1);
		}
		$error_str = "Message sent to your clan.";
	} elseif($target == -4) {
		$error_str = message_all_players($text, $db_name, "All Players","<b class=b1>Admin</b>");
	} elseif ($target == -5 && $user['bounty'] > 0 && IS_ADMIN && isset($clan_id)) {
		$clan = $user['clan_id'];
		$user['clan_id'] = $clan_id;
		send_message($target, $text);
		$user['clan_id'] = $clan;
		$error_str = "Message sent.";
	} elseif ($target == -99 && (IS_ADMIN || IS_OWNER)) {
		$newId = newId('se_central_forum', 'message_id');
		$db->query('INSERT INTO se_central_forum (message_id, timestamp, ' .
		 'sender_name, sender_game, text) VALUES (%u, %u, \'%s\', ' .
		 '\'[game]\', \'%s\')', array($newId, time(),
		 $db->escape($user['login_name']), $db->escape($text)));
		$error_str = "Message Posted";
	} elseif ($target !== 0) {
		send_message($target, $text);
		$error_str = "Message sent.";
	}
}

if($target == -1) {
	$error_str .= "<p><a href=\"forum.php\">Back to Forum</a></p>";
} elseif($target == -2) {
	$error_str .= "<p><a href=\"clan.php\">Back to Clan Control</a></p>";
} elseif($target == -5) {
	$error_str .= "<p><a href=\"forum.php?clan_forum=1\">Back to Clan Forum</a></p>";
} elseif($target != -99) {
	$error_str .= "<p><a href=\"message_inbox.php\">Back to Messages Page</a></p>";
}

# -4 is used to send messages to all players.
// print page
print_page("Send Message",$error_str);

?>
