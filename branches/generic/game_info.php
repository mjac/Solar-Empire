<?php

require_once('inc/common.inc.php');

$db_name = isset($_REQUEST['db_name']) ? $_REQUEST['db_name'] : '';

db_connect();

gameVars($db_name);

print_header("Game Info");


function resolve_difficulty($diff)
{
	$txt = array(
		'Training',
		'Beginner',
		'Intermediate',
		'Challenge',
		'Advanced',
		'All Levels'
	);
	return $txt[($diff - 1) % count($txt)];
}


db("select count(login_id),sum(cash),sum(turns),sum(turns_run),sum(ships_killed), sum(fighters_lost) as lost_fighters, sum(fighters_killed) as killed_fighters, sum(tech) as tech from ${db_name}_users where login_id != " . ADMIN_ID);
$ct = dbr();
db("select count(login_id) from ${db_name}_users where ship_id != 1 && login_id != " . ADMIN_ID);
$ct2 = dbr();

db("select description, admin_name, name, paused,last_reset,difficulty from se_games where db_name = '$db_name'");
$descr = dbr();

echo make_table(array("",""));
echo quick_row("Game Name:","<b>$descr[name]</b>");
echo quick_row("Admin Name:","<b class=b1>$descr[admin_name]</b>");
if($descr['paused'] == 1){
	$g_status = "Paused";
} else {
	$g_status = "Running";
}
echo quick_row("Game Status:","$g_status");
echo quick_row("Players/Max Players:","$ct[0] / $max_players");

echo quick_row("Difficulty:",resolve_difficulty($descr['difficulty']));
echo quick_row("Last reset:",date("M d - H:i",$descr['last_reset']));
echo "</table><br><br>";
if($ct[0] >= $max_players) {
	echo "Player Limit for game reached. No New players allowed to join game.<br>";
} elseif($new_logins == 0  || $sudden_death == 1){
	echo "Signups are disabled.<br>";
}

if($admin_var_show == 1){
	echo "<a href=game_vars.php?db_name=$db_name>Game Variables</a><br><br>";
}



if(isset($new_page)){
	echo "<table cellspacing=1 cellpadding=2 border=0><tr><td bgcolor=#555555 nowrap>Admin Name:</td><td bgcolor=#333333><b class=b1>$descr[admin_name]</b></td></tr><tr><td bgcolor=#555555>Difficulty:</td><td bgcolor=#333333>".resolve_difficulty($descr['difficulty'])."</td></tr><tr><td bgcolor=#555555>Last Reset:</td><td bgcolor=#333333>".date("M d - H:i",$descr['last_reset'])."</td></tr></table>";
}

if($descr['description']){
	$descr['description'] = preg_replace("/\n/","<br>",$descr['description']);
	echo "<br><table cellspacing=1 cellpadding=2 border=0><tr bgcolor=#555555><td>Admin description of the game:</td></tr>";
	echo "<tr bgcolor=#333333 align=left><td>$descr[description]</td></tr>";
	echo "</table>";
}

echo "<br><br>";

//Admin board
//admin news start
echo "<table border=0 cellpadding=5><tr valign=top><td colspan=3>";
db("select headline,timestamp from ${db_name}_news where login_id = -1 || login_id = -11 order by timestamp desc LIMIT 5");
$news = dbr();
if($news){
	echo "Last 5 news headlines from Admin:<br>";
	echo "<table cellspacing=1 cellpadding=2 border=0 width=525>";
	while($news) {
		echo quick_row("<b>".date("M d - H:i",$news['timestamp']),stripslashes($news['headline']));
		$news = dbr();
	}
	echo "</table><br>";
}
//admin news end


//Start of the Viewable Information.
echo "</td></tr>";

db("select count(planet_id),sum(fighters),sum(cash) as cash, sum(tech) as tech from ${db_name}_planets where login_id != 1 && planet_type >=0");
$ct4 = dbr();

if(isset($ct2[0])) {
	echo "<tr valign=top><td>";
	echo make_table(array("Players","<b>".($ct[0])."</b>"));
	if($ct[0] > 0) {
		echo quick_row("Players Alive",calc_perc($ct2[0],$ct[0]));
		echo quick_row("Cash",number_format($ct[1] + $ct4['cash']));
		echo quick_row("Cash Average",number_format(round((($ct[1] + $ct4['cash']) * 100/$ct[0]) / 100)));
		if($flag_research == 1){
			echo quick_row("Tech. Units",number_format($ct['tech'] + $ct4['tech']));
			echo quick_row("Tech. Units Average",number_format(round((($ct['tech'] + $ct4['tech']) * 100/$ct[0]) / 100)));
		}
		echo quick_row("Turns",$ct[2]);
		echo quick_row("Turns Average",round(($ct[2] * 100/$ct[0]) / 100));
		echo quick_row("Turns Run",$ct[3]);
		echo quick_row("Turns Run Average",round(($ct[3] * 100/$ct[0]) / 100));
		echo quick_row("Ship Kills",$ct[4]);
		echo quick_row("Ship Kills Average",round(($ct[4] * 100/$ct[0]) / 100));
		echo quick_row("Fighters Killed",$ct['killed_fighters']);
		echo quick_row("Avg. Fighters Killed",round(($ct['killed_fighters'] * 100/$ct[0]) / 100));
	}

	echo "</table><br>";
	echo "</tr><td>";

	db("select count(login_id),sum(fighters) from ${db_name}_ships where login_id != 1 and login_id !=0");
	$ct3 = dbr();
	if($ct3[0] > 0) {
		echo make_table(array("Ships","<b>$ct3[0]</b>"));
		echo quick_row("Ships Avg/player",round($ct3[0]/$ct[0]));
		echo quick_row("Ship Fighters",$ct3[1]);
		echo quick_row("Avg. Fighters/Ship",round(($ct3[1] * 100/$ct3[0]) / 100));
		echo "</table><br>";
	}

	if(!empty($ct4[0])) {
		echo make_table(array("Planets","<b>$ct4[0]</b>"));
		echo quick_row("Planets Avg/player",number_format($ct4[0]/$ct[0],3));
		echo quick_row("Planet Fighters",$ct4[1]);
		echo quick_row("Avg. Fighters/Planet",round(($ct4[1] * 100/$ct4[0]) / 100));
		echo "</table><br>";
	}

	db("select count(distinct clan_id),count(login_id) from ${db_name}_users where clan_id > 0 && login_id != " . ADMIN_ID);
	$ct5 = dbr();
	if(!empty($ct5[0])) {
		echo make_table(array("Clans","<b>$ct5[0]</b>"));
		echo quick_row("Membership",$ct5[1]);
		echo "</table><br>";
	}

	echo "</td><td>";

	#echo "Top 10 Players<br>";
	echo make_table(array("Score","Login Name"));
	db("select login_name,clan_id,clan_sym,clan_sym_color,score from ${db_name}_users where ship_id != 1 and login_id != " . ADMIN_ID . " order by score desc, login_name limit 10");
	$players = dbr();

	while(($players)) {
		if ($players['clan_id'] == 0 || $players['clan_sym'] == "") {
			$players['login_name'] = "<b class=b1>$players[login_name]</b>";
		} else {
			$players['login_name'] = "<b class=b1>$players[login_name]</b>(<font color=$players[clan_sym_color]>$players[clan_sym]</font>)";
		}

		echo quick_row("<b>$players[score]</b>",$players['login_name']);
		$players = dbr();
	}
	echo "</table><br>";

	echo "</td></tr><tr><td colspan=3>";

}
echo "</table>";
?>
<p align="center"><a href="#" onclick="window.close(); return false;">Close Window</a></p>
<?php

print_footer();

?>