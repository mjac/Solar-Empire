<?php
#page for the person running the server to look at the statistics for a game.
#get login restriction code from index.php on own servers at uni.

require_once('inc/owner.inc.php');

$out_str = '';


#developer sends a message
if (isset($send_message)) {
	if (!(isset($text) && isset($target))) {
		$out = <<<END
<form action="$self" method="post">
	<p>Select the group of people you would like to send the message to:
	enter your message below (HTML is useable, message codes are not).</p>
	<p><select name="target">
		<option value="-1">All Admins</options>
		<option value="-2">All Players in all games</options>
		<option value="-3">All Game forums</options>

END;

		#loop through the games.
		db("select db_name, name from se_games");
		while ($dest = dbr(1)){
			$out .= "\n<option value=\"$dest[db_name]\">Players in $dest[name]</option>\n";
		}
		$out .= <<<END
	</select></p>
	<p><textarea name="text" cols="50" rows="20"></textarea></p>
	<p><input type="submit" name="send_message" value="Send message"
	 class="button" /></p>
</form>

END;

		print_page('Send Message', $out);
		#one of the pre-defined destinations.
	} else {
		if($target == -1){
			$send_to = "All the Admins";
		} elseif($target == -2){
			$send_to = "all the players in all the games";
		} elseif($target == -3){
			$send_to = "all the game forums";
		} else {
			$send_to = "all players";
		}

		db("SELECT db_name FROM se_games");
		while ($dest = dbr(1)) {
			#message only to recipients of this one game, or all players in all games
			if (($target > 0 && $dest['db_name'] == $target) || $target == -2) {
				$out_str .= "<p>".message_all_players($text, $dest['db_name'], $send_to, "<strong>The Server Operator</strong>");
			} elseif ($target == -1 || $target == -3) { // all admins or all forums
				if($target == -1){
					$dest_id = 1;
					$extra_txt = "Message to <strong>All Admins</strong> from <strong>The Server Operator</strong>:<p> ".$text;
				} else {
					$dest_id = -1;
					$extra_txt = "<p>Message to <strong>All Game Forums</strong> from <strong>The Server Operator</strong></p>".$text;
				}

				$newId = newId($dest['db_name'] . '_messages', 'message_id');
				$db->query("INSERT INTO $dest[db_name]_messages (message_id, timestamp, sender_name, sender_id, login_id, text) VALUES ($newId, ".time().", '$user[login_name]', $user[login_id], '$dest_id', '$extra_txt')");
			}
		}

		print_page('Send message', '<p>Message sent to ' . $send_to . '</p>');
	}
// show stats for the server
} elseif (isset($server_details)) {
	$out_str .= "<p>Generic Server Information";
	$genInfo = $db->query('SELECT COUNT(*), SUM(login_count), ' .
	 'SUM(num_games_joined), SUM(page_views) from user_accounts');
	$serv1 = $db->fetchRow($genInfo, ROW_NUMERIC);

	$gameAmount = $db->query('SELECT COUNT(*) FROM se_games');
	$serv2 = $db->fetchRow($gameAmount, ROW_NUMERIC);

	$out_str .= make_table(array()) . quick_row('Total Games:',  $serv2[0]) .
	 quick_row('Total Accounts:', $serv1[0]) . 
	 quick_row('Total Logins:', $serv1[1]) .
	 quick_row('Total page views:', $serv1[3]);
	if ($serv1[0] > 0) {
		$out_str .= quick_row('Logins / Player:', 
		 number_format($serv1[1] / $serv1[0], 2));
		$out_str .= quick_row('Games Joined / Player:', 
		 number_format($serv1[2] / $serv1[0], 2));
		$out_str .= quick_row('Page Views / Player:', 
		 number_format($serv1[3] / $serv1[0], 2));
	}
	$out_str .= "</table><br />";
} elseif (isset($php_info)) {
	phpinfo();
	exit;
} else {
	$out_str .= <<<END
<h1>Owner Tools</h1>
<h2>Server Functions</h2>
<ul>
	<li><a href="$self?server_details=1">Server Details</a></li>
	<li><a href="$self?php_info=1">PHP Info</a></li>
</ul>

<h2>Communications</h2>
<ul>
	<li><a href="$self?send_message=1">Message People</a></li>
</ul>
END;

}

print_page("Server Admin", $out_str);

?>
