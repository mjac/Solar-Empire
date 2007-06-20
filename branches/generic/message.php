<?php

require_once('inc/user.inc.php');

$target = isset($target) ? (int)$target : 0;

// message checks
if ($target == -2 && (!isset($clan_id) || $clan_id <= 0)) {
	print_page("Send Clan Message","No such clan.");
} elseif($target == -2 && $clan_id != $user['clan_id']) {
	print_page("Send Clan Message","You can only send clan messages to your own clan.");
} elseif($target == -2 && $user['clan_id'] < 1) {
	print_page("Send Clan Message","You can may not send a message to this clan.");
} elseif($target == -4 && $user['login_id'] != ADMIN_ID && (OWNER_ID != 0 && $user['login_id'] != OWNER_ID)) {
	print_page("Send Mass Message","You are not the Admin.");
} elseif($target == -5 && $user['clan_id'] < 1 && $user['login_id'] != ADMIN_ID) {
	print_page("Clan Forum","You are not in a clan.");
} elseif($target == -99 && $user['login_id'] != ADMIN_ID && (OWNER_ID != 0 && $user['login_id'] != OWNER_ID)) {
	print_page("Admin Forum","You are not an Admin.");
} elseif(empty($text)) {
	if ($target > 0) {
		db("select login_name,login_id,clan_sym,clan_sym_color from ${db_name}_users where login_id = $target");
		$rec = dbr();
		$rec = print_name($rec);
	} elseif($target== -1){
		$rec = "the Forum";
	} elseif($target== -2){
		$rec = "your Clan";
	} elseif($target== -3){
		$rec = "the Bug Board";
	} elseif($target== -4){
		$rec = "All the Players";
	} elseif($target== -5){
		$rec = "your Clan Forum";
	} elseif($target== -99){
		$rec = "Admin Forum";
	} else {
		$ostr = "Send Message to:<br><br>";
		$ostr .= "<form name=\"get_var_form\" action=\"{$_SERVER['SCRIPT_NAME']}\" method=\"post\">";
		$ostr .= "<select name = 'target'>";
		db("select * from ${db_name}_users where login_id != '$login_id' order by login_name");
		while ($info2 = dbr()) {
			$ostr .= "<option value = '$info2[login_id]'>$info2[login_name]";
		}
		$ostr .= "</select><br><br>";
		$ostr .= "<textarea name='text' cols=50 rows=20 wrap=soft>".stripslashes($var_default)."</textarea>";
		$ostr .= "<input type=hidden name=rs value=".htmlentities($rs).">";
		$ostr .= '<p><input type=submit value=Submit></form>';
		print_page('Send message', $ostr);
	}

	if($reply_to && $target > 0){#reply to with original text
		$rs .= "<br><a href=mpage.php>Back to Message Page</a>";
		if($user[login_id] == -1){
			db("select text from ${db_name}_messages where message_id = '$reply_to' && login_id = '-1'");
		} else {
			db("select text from ${db_name}_messages where message_id = '$reply_to' && (login_id = -1 || login_id=$user[login_id] || (login_id = -5 && clan_id = $user[clan_id]))");
		}
		$reply_to = dbr();
		$reply_to[text] = stripslashes($reply_to[text]);
		get_var('Send Message','message.php',"Original Message from <b class=b1>$rec</b>:<br><blockquote><hr><br>$reply_to[text]<br><hr></blockquote><br>What is your reply?",'text','');
	} else { #no original text
		get_var('Send Message','message.php',"What is your message to <b class=b1>$rec</b>?",'text','');
	}
} elseif ($target !== 0) {
	$text = addslashes(mcit($text));
	if($user['login_id'] != ADMIN_ID) {
		$text = stripslashes($text);
	} else {
		if ($target != -99) {
			if($message_colour == 1) {
				$text = "<div style=\"display: inline; color: yellow;\">".$text."</div>";
			} elseif ($message_colour == 2) {
				$text = "<div style=\"display: inline; color: aqua;\">".$text."</div>";
			} elseif ($message_colour == 3) {
				$text = "<div style=\"display: inline; color: lime;\">".$text."</div>";
			} elseif ($message_colour == 4) {
				$text = "<div style=\"display: inline; color: red;\">".$text."</div>";
			}
		}
	}

#send message
	if($target==-2) {
		db2("select login_id from ${db_name}_users where clan_id='$clan_id' && clan_id > 0");
		$target_member = dbr2(1);
		while($target_member) {
			send_message($target_member['login_id'],$text);
			$target_member = dbr2(1);
		}
		$error_str = "Message sent to your clan.";

	} elseif($target==-4) {
		$error_str = message_all_players($text,$db_name, "All Players","<b class=b1>Admin</b>");

	} elseif($target==-5 && $user['bounty'] > 0 && $user['login_id'] == ADMIN_ID) {
		$temp_4323 = $user['clan_id'];
		$user['clan_id'] = $user['bounty'];
		send_message($target,$text);
		$user['clan_id'] = $temp_4323;
		$error_str = "Message sent.";
	} elseif($target == -99){
		if($user['login_id'] != ADMIN_ID){
			$game_name['name'] = "(Server Admin)";
			$sender_name = $user['login_name'];
		} else {
			db("select admin_name, name from se_games where db_name = '${db_name}'");
			$game_name = dbr(1);
			$game_name['name'] = "(Admin: $game_name[name])";
			$sender_name = $game_name['admin_name'];
		}
		dbn("insert into se_central_forum (timestamp,sender_name, sender_game, sender_game_db, text) values(".time().",'$sender_name','$game_name[name]', '$db_name','$text')");
		$error_str = "Message Posted";

	} elseif ($target !== 0) {
		send_message($target, $text);
		$error_str = "Message sent.";
	}
}

if($target == -1) {
	$error_str .= "<br><br><a href='forum.php'>Back to Forum</a>";
} elseif($target == -2) {
	$error_str .= "<br><br><a href='clan.php'>Back to Clan Control</a>";
} elseif($target == -5) {
	$error_str .= "<br><br><a href='forum.php?clan_forum=1'>Back to Clan Forum</a>";
} elseif($target != -99) {
	$error_str .= "<br><br><a href='mpage.php'>Back to Messages Page</a>";
}

# -4 is used to send messages to all players.
// print page
print_page("Send Message",$error_str);
?>
