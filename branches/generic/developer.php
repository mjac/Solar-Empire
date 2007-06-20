<?php
#page for the person running the server to look at the statistics for a game.
#get login restriction code from index.php on own servers at uni.

require_once('inc/owner.inc.php');

$out_str = "";


#developer sends a message
if(isset($send_message)){
	if(empty($text) || !isset($target)){
		$mess_str = "<select name=target>\n";
		$mess_str .= "\t<option value=-1>All Admins</options>\n";
		$mess_str .= "\t<option value=-2>All Players in all games</options>\n";
		$mess_str .= "\t<option value=-3>All Game forums</options>\n";

		#loop through the games.
		db("select game_id, name from se_games");
		while($dest = dbr(1)){
			$mess_str .= "\n<option value=$dest[game_id]> Players in '$dest[name]'";
		}
		$mess_str .= "\n</select><br>";

		get_var('Send Message','developer.php',"Select the group of people you would like to send the message to:<br><br> $mess_str<br><br>Enter your message below (note: HTML is useable. Message codes are not):",'text',"");

	#one of the pre-defined destinations.
	} else{
		if($target == -1){
			$send_to = "All the Admins";
		} elseif($target == -2){
			$send_to = "all the players in all the games";
		} elseif($target == -3){
			$send_to = "all the game forums";
		} else {
			$send_to = "all players";
		}

		db("select game_id, db_name from se_games");
		while($dest = dbr(1)){

			#message only to recipients of this one game, or all players in all games
			if(($target > 0 && $dest['game_id'] == $target) || $target == -2){
				$out_str .= "<p>".message_all_players($text,$dest['db_name'], $send_to,"<font color=lime>The Server Operator</font>");

			} elseif($target == -1 || $target == -3){//all admins or all forums
				if($target == -1){
					$dest_id = 1;
					$extra_txt = "Message to <b class=b1>All Admins</b> from <font color=lime>The Server Operator</font>:<p> ".$text;
				} else {
					$dest_id = -1;
					$extra_txt = "Message to <b class=b1>All Game Forums</b> from <font color=lime>The Server Operator</font>:<p> ".$text;
				}
				dbn("insert into {$dest['db_name']}_messages (timestamp,sender_name, sender_id, login_id, text) values(".time().",'$user[login_name]','$user[login_id]','$dest_id','$extra_txt')");
			}
		}
	}

// show stats for the server
} elseif(isset($server_details)) {

	if(!isset($game_details)){

		$out_str .= "<p>Generic Server Information";
		db("select count(login_id), sum(login_count), sum(num_games_joined), sum(page_views) from user_accounts WHERE login_id != " . ADMIN_ID);
		$serv1 = dbr();

		db("select count(game_id) from se_games where status = 1");
		$serv2 = dbr();

		$out_str .= make_table(array("",""));
		$out_str .= quick_row("Total Games:","$serv2[0]");
		$out_str .= quick_row("Total Accounts:","$serv1[0]");
		$out_str .= quick_row("Total Logins:","$serv1[1]");
		$out_str .= quick_row("Total page views:","$serv1[3]");
		$out_str .= quick_row("Avg. Logins/Player:",number_format($serv1[1]/$serv1[0],2));
		$out_str .= quick_row("Avg. Games Joined/Player:",number_format($serv1[2]/$serv1[0],2));
		$out_str .= quick_row("Avg. Page Views/Player:",number_format($serv1[3]/$serv1[0],2));
		$out_str .= "</table><br><br><br>";

		$out_str .= "<b class=b1>MySQL Server Details</b><br>".preg_replace("/  /","<br>", mysql_stat($database_link));


	} else{

		$out_str .= "<p>Game Details:";
		#loop once per game
		db2("select * from se_games order by name");
		while ($game = dbr2()){
			$db_name = $game['db_name'];
			db("select count(login_id),sum(cash),sum(turns),sum(turns_run),sum(ships_killed), sum(fighters_lost) as lost_fighters, sum(fighters_killed) as killed_fighters from ${db_name}_users where login_id != " . ADMIN_ID);
			$ct = dbr();
			db("select count(login_id) from ${db_name}_users where ship_id != NULL && login_id != " . ADMIN_ID);
			$ct2 = dbr();
			db("select count(login_id), sum(fighters) from ${db_name}_ships where login_id != " . ADMIN_ID);
			$ct3 = dbr();
			db("select count(planet_id), sum(fighters), sum(colon), sum(elect), sum(metal), sum(fuel) from ${db_name}_planets where planet_type >= 0 && login_id != " . ADMIN_ID);
			$ct4 = dbr();
			db("select count(distinct clan_id), count(login_id) from ${db_name}_users where clan_id > 0 && login_id != " . ADMIN_ID);
			$ct5 = dbr();
			db("select count(news_id) from ${db_name}_news");
			$ct6 = dbr();
			db("select count(message_id) from ${db_name}_messages where login_id = -1");
			$forum_posts = dbr();
			db("select count(message_id) from ${db_name}_messages where login_id > 1");
			$player_mess = dbr();
			db("select count(message_id) from ${db_name}_messages where login_id = -5");
			$clan_forum_posts = dbr();
			$out_str .= "<table border=0 cellpadding=5><tr valign=top><td colspan=3>";
			$out_str .= make_table(array("",""));
			$out_str .= quick_row("Game Name:","$game[name]");
			$out_str .= quick_row("Game ID:","$game[game_id]");
			$out_str .= quick_row("db_name: ","$game[db_name]");
			$out_str .= quick_row("Paused: ","$game[paused]");
			$out_str .= quick_row("Status: ","$game[status]");
			$out_str .= quick_row("","");
			$out_str .= quick_row("","");
			$out_str .= quick_row("Admin Name:","$game[admin_name]");
			$out_str .= quick_row("Description:","$game[description]");
			$out_str .= quick_row("Intro Message:","$game[intro_message]");
			$out_str .= quick_row("Num Stars:","$game[num_stars]");
			$out_str .= quick_row("","");
			$out_str .= "</table></td></tr><tr><td>";

			$out_str .= make_table(array("",""));
			$out_str .= quick_row("News Posts","$ct6[0]");
			$out_str .= quick_row("Forum Posts","$forum_posts[0]");
			$out_str .= quick_row("Player Messages","$player_mess[0]");
			$out_str .= quick_row("Clan Forum Posts","$clan_forum_posts[0]");
			$out_str .= "</table><br>";

			$out_str .= make_table(array("",""));
			$out_str .= quick_row("Players","<b>".($ct[0])."</b>");
			$out_str .= quick_row("Players Alive",calc_perc($ct2[0],$ct[0]));
			$out_str .= quick_row("Cash",number_format($ct[1]));
			$out_str .= quick_row("Cash Average",number_format(round(($ct[1] * 100/$ct[0]) / 100)));
			$out_str .= quick_row("Turns",$ct[2]);
			$out_str .= quick_row("Turns Average",number_format($ct[2]/$ct[0]),2);
			$out_str .= quick_row("Turns Run",$ct[3]);
			$out_str .= quick_row("Turns Run Average",number_format($ct[3]/$ct[0]),2);
			$out_str .= "</table></td><td>";
			#new grid

			$out_str .= make_table(array("",""));
			$out_str .= quick_row("Ships","<b>$ct3[0]</b>");
			$out_str .= quick_row("Ships Average",round($ct3[0]/$ct[0]));
			$out_str .= quick_row("Fighters",$ct3[1]);
			$out_str .= quick_row("Avg. Fighters/Ship",round(($ct3[1] * 100/$ct3[0]) / 100));
			$out_str .= "</table><br>";

			$out_str .= make_table(array("",""));
			$out_str .= quick_row("Planets","<b>$ct4[0]</b>");
			$out_str .= quick_row("Planets Average",number_format($ct4[0]/$ct[0],3));
			$out_str .= quick_row("Planet Colonists","<b>$ct4[2]</b>");
			$out_str .= quick_row("Planet Metal","<b>$ct4[4]</b>");
			$out_str .= quick_row("Planet Fuel","<b>$ct4[5]</b>");
			$out_str .= quick_row("Planet Electronics","<b>$ct4[3]</b>");
			$out_str .= quick_row("Planet Fighters",$ct4[1]);
			if($ct4[1] > 0){
				$out_str .= quick_row("Fighters Average",number_format(($ct4[1] * 100/$ct4[0]) / 100,2));
			} else {
				$out_str .= quick_row("Fighters Average","0%)");
			}
			$out_str .= "</table></td><td>";
			#new grid

			$out_str .= make_table(array("",""));
			$out_str .= quick_row("Kills",$ct[4]);
			$out_str .= quick_row("Kills Average",round(($ct[4] * 100/$ct[0]) / 100));
			$out_str .= quick_row("Fighters Killed",$ct['killed_fighters']);
			$out_str .= quick_row("Fighters Killed Average",round(($ct['killed_fighters'] * 100/$ct[0]) / 100));
			$out_str .= quick_row("Fighters Lost",$ct['lost_fighters']);
			$out_str .= quick_row("Fighters Lost Average",round(($ct['lost_fighters'] * 100/$ct[0]) / 100));

			$out_str .= "</table><br>";
			$out_str .= make_table(array("",""));
			$out_str .= quick_row("Clans","<b>$ct5[0]</b>");
			$out_str .= quick_row("Total Clan <br>Membership",$ct5[1]);
			if($ct5[1] > 0){
				$out_str .= quick_row("Average Clan <br>Membership",round(($ct5[1] * 100/$ct5[0]) / 100));
			}
			$out_str .= "</table><br><br>";
			$out_str .= "</table><br><br>";
		}
	}

//optimise the DB tables.
}elseif(isset($optimise)){
	$tables_str = "";
	$count = 0;
	//select all tables from the DB
	$tables = mysql_list_tables(DATABASE);
	while ($row = mysql_fetch_row($tables)) {
		$tables_str .= " `$row[0]`, ";
		++$count;
	}
	$tables_str = preg_replace("/, $/", "", $tables_str);
	dbn("OPTIMIZE TABLE $tables_str");
	$out_str .= $count . ' tables were optimised in database <b class=b1>' .
	 DATABASE . '</b>';

} elseif(isset($php_info)){
	phpinfo();
	exit();
} else {
	$self = $_SERVER['SCRIPT_NAME'];
	$out_str .= <<<END
<h1>Owner Tools</h1>
<h2>Server Functions</h2>
<ul>
	<li><a href="$self?server_details=1">Server Details</a></li>
	<li><a href="$self?server_details=1&amp;game_details=1">Game Details</a></li>
	<li><a href="$self?php_info=1">PHP Info</a></li>
	<li><a href="$self?optimise=1">Optimise Tables</a></li>
</ul>

<h2>Communications</h2>
<ul>
	<li><a href="$self?send_message=1">Message People</a></li>
	<li><a href="post_server_news.php">Post Server News</a></li>
</ul>
END;

}

print_page("Server Admin",$out_str);

?>