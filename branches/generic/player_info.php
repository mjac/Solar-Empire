<?php

require_once('inc/user.inc.php');

sudden_death_check($user);

if(!isset($target)){
	$target=$user['login_id'];
}

$text = "";

//Destroy ship(s)
if(isset($self_destruct)) {
	$rs .= "<br><a href=player_info.php?target=$user[login_id]>Back to Player Info</a>";
	if(!$expl){
			print_page("Self Destruct","You must select at least one ship to destroy.");
	}
	$math = 0;
	$temp444 = $expl;
	while ($var = each($temp444)) {
		db("select login_id from ${db_name}_ships where ship_id = '$var[value]'");
		$target_ship = dbr();
		$math++;
		if($target_ship[login_id] != $user[login_id]) {
		print_page("Self Destruct","You can only self destruct one of your own ships!");
		break;
		} elseif($user[ship_id] == $var[value]) {
		print_page("Self Destruct","You may not destroy the ship that you are commanding.");
		break;
		}
	}
	if($user[turns] < $math) {
		print_page("Self Destruct","It costs <b>1</b> turn to blow up each ship. You do not have enough turns to destroy all these ships.");
	} elseif($sure != "yes") {
	$ostr .= "Are you sure you want to destroy <b>$math</b> ship(s)?";
	$ostr .= "<form name=destroy_ships action=player_info.php method=POST>";
	$temp444 = $expl;
	$i=0;
	while ($var = each($temp444)) {
		$ostr .= "<input type=hidden name=expl[$i] value='$var[value]'>";
		$i++;
	}
	$ostr .= '<input type=hidden name=self_destruct value=1>';
	$ostr .= '<input type=hidden name=sure value=yes>';
		$ostr .= '<input type=submit name=submit value=Yes> <input type="Button" width="30" value="No" onclick="javascript: history.back()"> </form>';
	print_page("Sure?",$ostr);
	} else {
	$temp444 = $expl;
	while ($var = each($temp444)) {
		dbn("update ${db_name}_ships set towed_by = 0 where towed_by = '$var[value]'");
		dbn("delete from ${db_name}_ships where ship_id = '$var[value]'");
	}
		post_news("<b class=b1>$user[login_name]</b> self destructed <b>$math</b> ship(s).");
			charge_turns($math);
	if($math > 100){
		$out = "<p>Dang, that bang almost makes a <b class=b1>SuperNova</b> Pale in comparison!<br>Here, have 10 credits to make up for it.";
		give_cash(10);
	} elseif($math > 60){
		$out = "<p>And I thought <b class=b1>Armageddon</b> created a big bang!!!!!<br>Here's 5 credits to help cover costs.";
		give_cash(5);
	} elseif($math > 30){
		$out = "<p>Now you're playing with BIG <b class=b1>FIREWORKS</b>!!!!!";
	} elseif($math > 10){
		$out = "<p>Thats a <b>BIG BANG</b>!!!!!";
	} else {
		$out = "<p>BOOOOOMMM!!!!!";
	}
	$text .= "<b>$math</b> Ships Blown up at a cost of <b>$math</b> turns.".$out."<br>";
	}
}

#History of players actions.
if(isset($history) && $history > 0){
	$action_show = 200;
	if($user['login_id'] == ADMIN_ID || $user['login_id'] == $history || $history == 1 || ($user['login_id'] == OWNER_ID && OWNER_ID != 0)){
		$rs="<a href=player_info.php?target=$history>Back to Player Info</a><br><br>";
		$sec_sort = "";

		//a direction to the history.
		if(isset($sorted_history) && $sorted_history == 1){
			$going = "asc";
			$sorted_history=2;
		} else {
			$going = "desc";
			$sorted_history=1;
		}

		//user not yet chosen to sort the history
		if(empty($sort_history) || $sort_history == "time"){
			$sql_sort = "timestamp $going";
		} elseif($sort_history == "game") {
			$sql_sort = "game_db $going, timestamp desc";
		} elseif($sort_history == "action") {
			$sql_sort = "action $going, timestamp desc";
		} elseif($sort_history == "IP") {
			$sql_sort = "user_IP $going, timestamp desc";
		} elseif($sort_history == "other") {
			$sql_sort = "other_info $going, timestamp desc";
		}

		$sql_game_select = "";
		$games_links = "";
		//user only wants to list history from a certain game.
		if(!empty($select_game)){
			$sql_game_select = " && game_db = '$select_game' ";
			$games_links .= "<br><a href=$PHP_SELF?history=$history&select_game=>All Games</a>";
		} else {
			$select_game = "";
		}

		//only the server op can see all of an admins details.
		if(($user['login_id'] == OWNER_ID && OWNER_ID != 0) || ($user['login_id'] == ADMIN_ID && $history != 1 && $history != OWNER_ID) || $user['login_id'] == $history){
			$to_select = "timestamp,game_db,action,user_IP,other_info";
			$is_full = 1;
		} else {
			$to_select = "timestamp,game_db,action";
			$is_full = 0;
		}

		db("select ".$to_select." from user_history where login_id = '$history' $sql_game_select order by $sql_sort LIMIT $action_show");

		if($user['login_id'] == ADMIN_ID || ($user['login_id'] == OWNER_ID && OWNER_ID != 0)){
			//get all the games the user has been in.
			db2("select game_db from user_history where login_id = '$history' group by game_db");
			while($games_in = dbr2()){
				$games_links .= "\n<br><a href=$PHP_SELF?history=$history&select_game=$games_in[game_db]>$games_in[game_db]</a>";
			}
		}
		$text = $rs;
		if(!empty($games_links)){
			$text .= "List details for only the selected game:".$games_links."<p>";
		}

		$text .= "List of last <b>$action_show</b> actions:";
		$text .= make_table(array("<a href=player_info.php?history=$history&sort_history=time&sorted_history=$sorted_history&select_game=$select_game>Time/Date</a>","<a href=player_info.php?history=$history&sort_history=game&sorted_history=$sorted_history&select_game=$select_game>Game</a>","<a href=player_info.php?history=$history&sort_history=action&sorted_history=$sorted_history&select_game=$select_game>Entry</a>","<a href=player_info.php?history=$history&sort_history=IP&sorted_history=$sorted_history&select_game=$select_game>IP</a>","<a href=player_info.php?history=$history&sort_history=other&sorted_history=$sorted_history&select_game=$select_game>Other</a>"));
		while($hist = dbr()){
			if($is_full == 0){
				$hist['user_IP'] = "";
				$hist['other_info'] = "";
			}
			$text .= make_row(array("<b>".date("M d - H:i",$hist['timestamp']),$hist['game_db'],$hist['action'],$hist['user_IP'],$hist['other_info']));
		}
		$text .= "</table><br>";
		print_page("Account History",$text);
	} else{
		print_page("Account History","You may not view another players account history.");
	}
}

// transfer cash
if(isset($transfer)) {

	if($sure != 'yes') {
		get_var('Transfer cash','player_info.php',"Are you sure you want to transfer $trans_amount cash to $trans_target?",'sure','yes');
	} else {
		settype($trans_amount, "integer");
		if ($trans_amount<=0) {
			print_page("Transfer Error","You can't transfer negative or 0 cash<br><a href=javascript:back()>Go back</a><br>");
		} elseif ($user['cash'] < $trans_amount) {
			print_page("Transfer Error","You don't have that much cash<br><a href=javascript:back()>Go back</a><br>");
		} elseif ($user['joined_game'] > (time() - ($min_before_transfer * 86400)) && $user['login_id'] != ADMIN_ID) {
			print_page("Transfer Error","The admin has restricted access to Credit transfer. You may only transfer Credits once you have been in the game <b>$min_before_transfer</b> days or more. <br><a href=javascript:back()>Go back</a><br>");
		} else {
			take_cash($trans_amount);
			dbn("update ${db_name}_users set cash = cash + $trans_amount where login_id = '".$trans_target_id."'");
			send_message($trans_target_id,"<b class=b1>$user[login_name]</b> has sent you <b>$trans_amount</b> Credits.");
			insert_history($user['login_id'],"Transfered $trans_amount to $trans_target");
			print_page("Transfer Complete","You sent <b>$trans_amount</b> Credits to <b class=b1>$trans_target</b>.");
		}
	}
}

db("select u.*, pu.*, pu.login_name as generic_l_name, u.login_name as login_name from ${db_name}_users u, user_accounts pu where u.login_id = '$target' && pu.login_id = '$target'");
$player = dbr();

#used to calculate percentages
db("select sum(cash) as cash, sum(fighters_killed) as fighters_killed, sum(fighters_lost) as fighters_lost, sum(score) as score, sum(tech) as tech, sum(ships_killed) as ships_killed, sum(ships_lost) as ships_lost, sum(ships_killed_points) as ships_killed_points, sum(ships_lost_points) as ships_lost_points, sum(turns_run) as turns_run, sum(turns) as turns, sum(game_login_count) as game_login_count from ${db_name}_users where login_id > '5'");
$all_player = dbr();

# Won't display alien or pirate information, or the other two reserved accounts.
# they have login_id 2 or 3, or 4,5 for the reserved ones.
# Minimum login id is 0 (or 1, but then as admin gets 1, it must be 2 for players cos its auto increment).
if($target == ADMIN_ID){
	$special_show = 1;
	$full = 1;
} elseif($target == $user['login_id'] || $target == 1 || ($user['clan_id'] == $player['clan_id'] && $user['clan_id'] > 0) || $user['login_id'] == ADMIN_ID || (OWNER_ID != 0 && $user['login_id'] == OWNER_ID)) { #admin can see all, but not aliens/pirates
	$full = 1;
} else { #if none of the above are true, then a more limited view is given.
	$full = 0;
}

$text .= make_table(array("",""));
$text .= quick_row("Game Name",print_name($player));
if($full == 1 || isset($special_show)) {
	$text .= quick_row("Login name",$player['generic_l_name']);
	if(isset($special_show)){
		$text .= quick_row("Misc Info",$player['real_name']);
		$text .= quick_row("Purpose","$player[email_address]");
	} else {
		$text .= quick_row("Real Name",$player['real_name']);
		$text .= quick_row("Email Address","<a href=mailto:$player[email_address]>$player[email_address]</a>");
	}
	if($user['login_id'] == OWNER_ID && OWNER_ID != 0){
		$text .= quick_row("&nbsp;","");
		$text .= quick_row("All time Logins",$player['login_count']);
		$text .= quick_row("Page Views",$player['page_views']);
	}
	$text .= quick_row("&nbsp;","");
	$text .= quick_row("Joined Game",date( "M d - H:i",$player['joined_game']));
	$text .= quick_row("Last Page Request",date( "M d - H:i:s",$player['last_request']));
	$text .= quick_row("Login Count",calc_perc($player['game_login_count'],$all_player['game_login_count']));
	$text .= quick_row("Last IP Address",$player['last_ip']);
	$text .= quick_row("Num. Games Joined",$player['num_games_joined']);
	$text .= quick_row("&nbsp;","");
	db("select count(ship_id) from ${db_name}_ships where login_id = '$target'");
	$ship_count = dbr();
	db("select count(ship_id) from ${db_name}_ships where login_id > 5");
	$ship_count_all = dbr();

	$text .= quick_row("Ship Count",calc_perc($ship_count[0],$ship_count_all[0]));
	$text .= quick_row("Cash",calc_perc($player['cash'],$all_player['cash']));
	if($flag_research == 1){
		$text .= quick_row("Tech Units",calc_perc($player['tech'],$all_player['tech']));
	}
	$text .= quick_row("Turns",calc_perc($player['turns'],$all_player['turns']));
}

$text .= quick_row("Turns Run",calc_perc($player['turns_run'],$all_player['turns_run']));
$text .= quick_row("&nbsp;","");

$text .= quick_row("Ships Killed", calc_perc($player['ships_killed'],$all_player['ships_killed']));
$text .= quick_row("Ships Lost", calc_perc($player['ships_lost'],$all_player['ships_lost']));
$text .= quick_row("Ship Points Killed", calc_perc($player['ships_killed_points'], $all_player['ships_killed_points']));
$text .= quick_row("Ship Points Lost",calc_perc($player['ships_lost_points'],$all_player['ships_lost_points']));
$text .= quick_row("Fighters Killed",calc_perc($player['fighters_killed'],$all_player['fighters_killed']));
$text .= quick_row("Fighters Lost",calc_perc($player['fighters_lost'],$all_player['fighters_lost']));
$text .= quick_row("Flagship Count",num_flagships($player['one_brob']));

if($score_method != 0){
	db("select count(login_id) from ${db_name}_users where score > '$player[score]' && login_id > 5");
	$score_front = dbr();
	db("select count(login_id) from ${db_name}_users where login_id > 5");
	$score_back = dbr();

	$score_front[0]++;
	$text .= quick_row("Score",$player['score']." ($score_front[0] of $score_back[0])");
}

if($player['last_attack'] <= 1){
	$text .= quick_row("Last Attack (Time)","Never");
	$player['last_attack_by'] = "No-one";
} else {
	$text .= quick_row("Last Attack (Time)",date( "M d - H:i",$player['last_attack']));
}

$text .= quick_row("Last Attack (Against)","<b class=b1>$player[last_attack_by]</b>");
$text .= quick_row("&nbsp;","");
$text .= quick_row("Bounty",$player['bounty']);


if($full) {
	$text .= quick_row("&nbsp;","");
	$text .= quick_row("Genesis Devices",$player['genesis']);
	if($flag_bomb < 2){
		$text .= quick_row("Alpha Bombs",$player['alpha']);
		$text .= quick_row("Gamma Bombs",$player['gamma']);
		$text .= quick_row("Delta Bombs",$player['delta']);
		$text .= quick_row("SuperNova Effectors",$player['sn_effect']);
	}
}

$text .= "</table><br>";
if($full) {

	if(isset($sort_planets)){
		if($sorted_planets==1){
			$going = "asc";
			$sorted_planets=2;
		} else {
			$going = "desc";
			$sorted_planets=1;
		}
		db("select planet_name,location,fighters,colon,cash,metal,fuel,elect from ${db_name}_planets where login_id = '$target' order by '$sort_planets' $going");
	} else {
		db("select planet_name,location,fighters,colon,cash,metal,fuel,elect from ${db_name}_planets where login_id = '$target' order by fighters desc, planet_name asc, location desc");
		$sorted_planets = "";
	}
	$clan_planet = dbr(1);
	if($clan_planet) {
		$text .= make_table(array("<a href=player_info.php?target=$target&sort_planets=planet_name&sorted_planets=$sorted_planets>Planet Name</a>","<a href=player_info.php?target=$target&sort_planets=location&sorted_planets=$sorted_planets>Location</a>","<a href=player_info.php?target=$target&sort_planets=fighters&sorted_planets=$sorted_planets>Fighters</a>","<a href=player_info.php?target=$target&sort_planets=colon&sorted_planets=$sorted_planets>Colonists</a>","<a href=player_info.php?target=$target&sort_planets=cash&sorted_planets=$sorted_planets>Cash</a>","<a href=player_info.php?target=$target&sort_planets=metal&sorted_planets=$sorted_planets>Metal</a>","<a href=player_info.php?target=$target&sort_planets=fuel&sorted_planets=$sorted_planets>Fuel</a>","<a href=player_info.php?target=$target&sort_planets=elect&sorted_planets=$sorted_planets>Electronics</a>"));
		while($clan_planet) {
			$clan_planet['planet_name'] = "<b class=b1>$clan_planet[planet_name]</b>";
			$text .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$text .= "</table><br>";
	}
}

#links at bottom of page to transfer stuff and message player.
if($player['login_id'] != $user['login_id']) {
	$text .= "<a href=message.php?target=$target>Message $player[login_name]</a><br>";
	if($user['joined_game'] < (time() - ($min_before_transfer * 86400)) || $user['login_id'] == ADMIN_ID){
		$text .= "<a href=send_ship.php?target=$target>Transfer Ship Registration</a><br>";
		$text .= "<form action=player_info.php><input type=hidden name=transfer value=yes>";
		$text .= "Transfer cash to <b class=b1>$player[login_name]</b>:<br>";
		$text .= "<input type=text name=trans_amount size=6>";
		$text .= "<input type=hidden name=trans_target_id value=$player[login_id]>";
		$text .= "<input type=hidden name=trans_target value=$player[login_name]>";
		$text .= "<input type=submit value='Transfer'></form>";
	} else {
		$text .= "<p>You may not Transfer Things yet. <br>Your account must be <b>$min_before_transfer</b> or more days old.<p>";
	}
}

#retire player
if($user['login_id'] == ADMIN_ID && $target != 1) {
	$text .= "<a href=retire.php?target=$target>Retire $player[login_name]</a><br>";
}

#show account history
if(($target == $user['login_id']) || $user['login_id'] == ADMIN_ID || $target == 1 || (OWNER_ID !=0 && $user['login_id'] == OWNER_ID)){
	$text .= "<a href=player_info.php?history=$target>Show Account History</a><br>";
}

print_page('Player Info',$text);

?>
