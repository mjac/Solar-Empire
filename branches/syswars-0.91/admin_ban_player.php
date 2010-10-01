<?php

require_once('inc/admin.inc.php');
$out = "<h1>Ban a player</h1>\n";

$max_time = 168;

if (isset($ban_target)) {
	if (is_numeric($ban_target)) {
		db("select login_name from [game]_users where login_id = $ban_target");
		$ban_info = dbr();
		if($ban_time > $max_time || $ban_time < -1){
			$out .= "<p>Maximum period of time a player may be banned for is $max_time hours.  Set to -1 to ban for the rest of the game.</p>";
		} elseif(!(isset($sure) && $sure)){
			get_var('Ban Player', $self, "Are you sure you want to ban <b class=b1>$ban_info[login_name]</b> for <b>$ban_time</b> hours?",'sure','yes');
		} else {
			insert_history($ban_target,"Was Banned from the game for $ban_time hours");
			if(empty($ban_reason)){
				$ban_reason = "No Reason.";
			}

			if($ban_time > 0){
				$ban_time = time() + round($ban_time * 3600);
			}

			$db->query("update [game]_users set banned_time = '$ban_time', banned_reason = '$ban_reason' where login_id = '$ban_target'");
			if($ban_time > 0){
				$time_text = date( "D jS M - H:i",$ban_time);
			} else {
				$time_text = "it resets";
			}
			post_news("$ban_info[login_name] has been banned from the game until $time_text by the Admin: $ban_reason");
			$out .= "<b class=b1>$ban_info[login_name]</b> has been banned from the game until $time_text.<br /><br />";
		}

		print_page('Player banned', $out);
	}

	db("select login_name,login_id from [game]_users where banned_time <= " . 
	 time() . " && banned_time != -1 order by login_name");
		$out .= <<<END
<p>The number of hours you may ban a player for is limited to $max_time, 
which is 1 week (7 days).<br />Setting a ban-time of -1 means the ban time 
will last until the game is reset.<br />You may reset a ban period at any 
time from this page.</p>
<form action="$self" method="post" name="ban_form">
	<h2>Select a player to ban</h2>
	<p><select name="ban_target">

END;
		while($list_em = dbr()) {
			$out .= "\t\t<option value=$list_em[login_id]>$list_em[login_name]</option>\n";
		}
		$out .= <<<END
	</select>
	<h2>Length of ban</h2>
	<p><input type="text" name="ban_time" size="3"> hours</p>

	<h2>Reason</h2>
	<p><textarea name="ban_reason" cols="50" rows="5" wrap="soft"></textarea></p>

	<p><input type="submit" value="Ban player" />
	<input type="hidden" name="ban" value="2" /></p>
</form>

END;

	print_page('Ban a player', $out);
}


if (isset($unban)) {
	db("select login_name, login_id from [game]_users where login_id = $unban");
	$ban_info = dbr();
	insert_history($unban,"Was Un-Banned from the game");
	$db->query("update [game]_users set banned_time = '0', banned_reason = '' where login_id = '$unban'");
	$out .= "<p>" . print_name($ban_info) . " was un-banned.</p>\n";
	post_news("$ban_info[login_name] was un-banned");
	print_page('Player un-banned', $out);
}


#list players who are presently banned
db("select login_name, login_id, banned_time, banned_reason from [game]_users where banned_time = -1 OR banned_time > ".time()." order by banned_time desc");
$b_t1_out = "Listing Banned Players:";
$b_t1_out .= make_table(array("Login Name","Banned until","Reason",""));
while($list_banned = dbr()){
	if($list_banned[banned_time] != -1){
		$temp_343 = date( "D jS M - H:i",$list_banned[banned_time]);
	} else {
		$temp_343 = "End of Game";
	}
	$b_t_out .= make_row(array(print_name($list_banned),$temp_343,"$list_banned[banned_reason]","<a href=$self?ban=1&unban=$list_banned[login_id]>Un-Ban</a>"));
}

$out .= "<a href=$self?ban_target=select>Ban a player</a><br /><br />";
if(empty($b_t_out)){
	$out .= "<br /><br />No players presently banned.<br />";
} else {
	$out .= $b_t1_out.$b_t_out."</table>";
}

print_page("Ban Player",$out);

?>
