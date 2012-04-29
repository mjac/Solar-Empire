<?php

require_once('inc/admin.inc.php');

$rs = "<p><a href=\"{$_SERVER['PHP_SELF']}\">Back to Admin Page</a>";

$out = "";

$savedVars = array();
#save database vars



if (isset($game_vars)) {
	if(isset($save_vars)) {
		foreach ($_REQUEST as $var => $value) {
			$value = mysql_real_escape_string($value);
			dbn("update ${db_name}_db_vars set value = '$value' where name = '" .
			 mysql_real_escape_string($var) . "' && '$value' <= max && '$value' >= min");
			if (mysql_affected_rows() > 0) {
				$savedVars[] = $var;
			}
		}

		if (!empty($savedVars)) {
			insert_history($user['login_id'], "Updated Game Vars: " .
			 implode(', ', $savedVars));
		}
	}

	$out = <<<END
<form action="$self" name="get_var_form" method="post">
	<p><input type="hidden" name="save_vars" value="1" />
	<input type="hidden" name="game_vars" value="1" />
	<input type="submit" value="Submit Vars" /></p>
	<p>Note: Only variables that are within range will be saved.</p>
	<table class="simple">
		<tr>
			<th>Variable</th>
			<th>Description</th>
			<th>Min</th>
			<th>Max</th>
			<th>Value</th>
		</tr>

END;

	db("select name, min, max, value,descript from ${db_name}_db_vars order by name");
	while ($adminVar = dbr(1)) {
		$out .= "\t\t<tr>\n\t\t\t<td><label for=\"" . $adminVar['name'] . "\">" .
		 $adminVar['name'] . "</label>" . (in_array($adminVar['name'], $savedVars) ?
		 " <strong>Updated</strong>" : '') . "</td>\n\t\t\t<td>" .
		 $adminVar['descript'] . "</td>\n\t\t\t<td>" . $adminVar['min'] .
		 "</td>\n\t\t\t<td>" . $adminVar['max'] .
		 "</td>\n\t\t\t<td><input type=\"text\" name=\"" .
		 $adminVar['name'] . "\" id=\"" . $adminVar['name'] . "\" value=\"" .
		 $adminVar['value'] . "\" size=\"8\"></td>\n\t\t</tr>\n";
	}
	$out .= <<<END
	</table>
</form>
END;
	print_page("Edit game variables", $out);
}

#update all player scores
if(isset($update_scores)){
	if($score_method == 0){
		$out .= "Scoring is presently turned off. Set the admin var to something other than 0 to turn it on.<br><br>";
	} else {
		score_func(0,1);
		$out .= "Scores successfully updated.<br><br>";
	}
	insert_history($user['login_id'],"Updated All Player Scores");
}

#give players money
if(isset($more_money)){
	if(!isset($money_amount)){
		get_var('Increase Money','admin.php','How much money do you want to give to each player?','money_amount','');
	} elseif($money_amount < 1) {
		$out .= "You can't decrease the players money.<br><br>";
	} else {
		settype($money_amount, "integer");
		$out .= "Player's money reserves increased by <b>$money_amount</b><br>Note: This has NOT sent a message to the players. That is your job.<br><br>";
		dbn("update ${db_name}_users set cash = cash + '$money_amount' where login_id != 1");
		insert_history($user['login_id'],"Gave $money_amount credits to all players.");
	}
}

#news post
if(isset($post_game_news) && empty($text)) {
	get_var('Post News',$PHP_SELF,'What do you want to post in the News?','text','');
} elseif(isset($post_game_news)) {
	$text = addslashes($text);
	$login_id = -1;
	post_news($text);
	$out = "News Posted.<p>";
}

//active user listing
if (isset($show_active)) {
	$out = "Users that have logged with within the past 5 mins.";
	$out .= "<br>Time Loaded: ".date("H:i:s (M d)")."<br><a href=admin.php?show_active=1>Reload</a>";
	db("select last_request,login_name,login_id,clan_sym,clan_sym_color,clan_id from ${db_name}_users where last_request > ".(time()-300)." && login_id > 1 order by last_request desc");
	$player = dbr();
	if(!$player){
		$out .= "<p>There are no active users.";
	} else {
		$out .= "<p><table>";
		$out .= "<tr bgcolor='#555555'><td>Login Name</td><td>Last Request</td></tr>";
		while ($player) {
		  $out .= "<tr bgcolor='#333333'><td>".print_name($player)."</td><td>".date( "H:i:s (M d)",$player['last_request'])."</td><td> - <a href=message.php?target=$player[login_id]>Message</a><br></td></tr>";
		  $player = dbr();
		}
		$out .= "</table>";
	}

	$rs = "<p><a href=admin.php>Back to Admin Page</a>";
	print_page("Active Users",$out);
}


#admin sets difficulty
if(isset($difficulty)){
	if(!isset($set_dif)){
		$out = "This will have no effect upon the game itself, but will serve simply to inform new joiners to the game what to expect.<form action=$PHP_SELF name=get_dif_form method=POST>
		<p><input type=radio name=set_dif value=1>Begginer
		<br><input type=radio name=set_dif value=2>Begginer -> Intermediate
		<br><input type=radio name=set_dif value=3>Intermediate
		<br><input type=radio name=set_dif value=4>Intermediate - > Advanced
		<br><input type=radio name=set_dif value=5>Advanced
		<br><input type=radio name=set_dif value=6>All Skill Levels
		<p><input type=submit><input type=hidden name=difficulty value=1></form>";
		print_page("Select Difficulty",$out);
	} else {
		dbn("update se_games set difficulty = '$set_dif' where db_name = '$db_name'");
		$out .= "Stated Difficulty updated<p>";
		insert_history($user['login_id'],"Game difficulty changed.");
	}
}


#ban player from game.
if(isset($ban)){
	if($ban == 2){ #show ban a player page.
		$max_time = 168;
		if(!$ban_target || $ban_target < 1 || !$ban_time){
			db("select login_name,login_id from ${db_name}_users where banned_time <= " .
			 time() . " && banned_time != -1 order by login_name");
			$out .= "Notes:<br>Number of hours you may ban a player for is limited to $max_time, which is 1 week (7 days).<br>Setting a ban-time of -1 means the ban time will last until the game is reset.<br>You may reset a ban period at any time from this page.<FORM action=$PHP_SELF method=POST name=ban_form>";
			$out .= "Select Player to Ban: <br><br>";
			$out .= "<select name=ban_target>";
			$out .= "<option value=0>Select player... ";
			while($list_em = dbr()) {
				$out .= "<option value=$list_em[login_id]>$list_em[login_name]";
			}
			$out .= "</select>";
			$out .= "<p><br>Enter the number of hours you would like the player to be banned for:<br><br><INPUT type=text name=ban_time size=3> hours";
			$out .= "<INPUT type=hidden name=ban value=2><p><br>Please give the reason you are banning this player (do not use apostrophes or quotation marks).<p><textarea name=ban_reason cols=50 rows=5 wrap=soft></textarea><br><br><INPUT type=submit value=Ban></form><p>";
		} elseif ($ban_target > 0){
			db("select login_name from ${db_name}_users where login_id = $ban_target");
			$ban_info = dbr();
			if($ban_time > $max_time || $ban_time < -1){
				$out = "Maximum period of time a player may be banned for is <b>$max_time</b> hours.<br>Or set to -1 to ban for the rest of the game.";
			} elseif(!$sure){
				if(!empty($ban_reason)){
					$ban_reason = stripslashes($ban_reason);
					$ban_reason = addslashes($ban_reason);
				}
				$rs="";
				get_var('Ban Player',"$PHP_SELF","Are you sure you want to ban <b class=b1>$ban_info[login_name]</b> for <b>$ban_time</b> hours?",'sure','yes');
			} else {
				insert_history($ban_target,"Was Banned from the game for $ban_time hours");
				if(empty($ban_reason)){
					$ban_reason = "No Reason.";
				}

				if($ban_time > 0){
					$ban_time = time() + round($ban_time * 3600);
				}

				dbn("update ${db_name}_users set banned_time = '$ban_time', banned_reason = '$ban_reason' where login_id = '$ban_target'");
				if($ban_time > 0){
					$time_text = date( "D jS M - H:i",$ban_time);
				} else {
					$time_text = "it resets";
				}
				post_news("<b class=b1>$ban_info[login_name]</b> has been banned from the game until $time_text by the Admin. <br>The reason being:<br>$ban_reason");
				$out = "<b class=b1>$ban_info[login_name]</b> has been banned from the game until $time_text.<br><br>";
			}
		}
#		print_page("Ban Player",$out);
	} elseif(isset($unban)){
		db("select login_name from ${db_name}_users where login_id = $unban");
		$ban_info = dbr();
		insert_history($unban,"Was Un-Banned from the game");
		dbn("update ${db_name}_users set banned_time = '0', banned_reason = '' where login_id = '$unban'");
		$out .= "<b class=b1>$ban_info[login_name]</b> was un-banned.<br><br>";
		post_news("<b class=b1>$ban_info[login_name]</b> was un-banned by the Admin");
	}

	#list players who are presently banned
	db("select login_name, login_id, banned_time, banned_reason from ${db_name}_users where banned_time = -1 || banned_time > ".time()." order by banned_time desc");
	$b_t1_out = "Listing Banned Players:";
	$b_t1_out .= make_table(array("Login Name","Banned until","Reason",""));
	while($list_banned = dbr()){
		if($list_banned[banned_time] != -1){
			$temp_343 = date( "D jS M - H:i",$list_banned[banned_time]);
		} else {
			$temp_343 = "End of Game";
		}
		$b_t_out .= make_row(array(print_name($list_banned),$temp_343,"$list_banned[banned_reason]","<a href=$PHP_SELF?ban=1&unban=$list_banned[login_id]>Un-Ban</a>"));
	}

	$out .= "<a href=$PHP_SELF?ban=2>Ban a player</a><br><br>";
	if(empty($b_t_out)){
		$out .= "<br><br>No players presently banned.<br>";
	} else {
		$out .= $b_t1_out.$b_t_out."</table>";
	}
	print_page("Ban Player",$out);
}


#(un)pause
if(isset($pause)){
	if($pause == 1){
		$out = "Game Paused.<p>";
		dbn("update se_games set paused = '1' where db_name = '$db_name'");
		post_news("Game Paused");
		insert_history($user['login_id'],"Paused Game.");
	} elseif($pause == 2){
		post_news("Game Un-Paused");
		$out = "Game Un-paused.<p>";
		dbn("update se_games set paused = '0' where db_name = '$db_name'");
		insert_history($user['login_id'],"Unpaused Game.");
	}
}

//preview a universe
if(isset($preview)){
	print_header("Universe Preview");
?>
<script>
function refresh(){
	var now = new Date();
	document.images.preview_uni_img.src = 'build_universe.php?preview=1&process=1&rand=' + now.getTime();
}
</script>

<?php

if(!extension_loaded("gd") && !extension_loaded("gd2")){
	print_status('');
	print("You do not have the <b class=b1>gd</b> module installed with this PHP installation, therefore the maps cannot be generated.");
	print_footer();
	exit;
} else {
	print "<center><a href='javascript:refresh();'>Generate New Universe</a><br>\n<img name='preview_uni_img' src='build_universe.php?preview=1&process=1' border=1 alt='Please wait. Generating universe and loading image. This may take some time.'> \n <br><a href='javascript:refresh();'>Generate New Universe</a> \n </center>";
}

?>

<p>The above image uses the following variables <b>only</b>.</p>
<ul>
	<li>uv_map_layout</li>
	<li>uv_max_link_dist</li>
	<li>uv_min_star_dist</li>
	<li>uv_num_stars</li>
	<li>uv_show_warp_numbers</li>
	<li>uv_universe_size</li>
	<li>wormholes</li>
</ul>
<p>Changing any of these variables will have some sort of effect on the
image/universe generated.</p>
<h3>Warning</h3>
<p>If you change <b>uv_universe_size</b>, during a game that has
<b>uv_explored</b> set to 0, players may experience some very strange maps
getting created. So be sure to set uv_universe_size back to what it was when
you finished messing around if you are not about to create a new game.</p>
<p>There is no way to save the present universe and use it in a game.
It is only an example of what can be created.</p>

<p>If no image appears, then there is a pretty big bug somewhere in the universe generation process. Report it to the Server Admin.

<?php

print_footer();
exit;
}


#reset signup times
if(isset($reset_signup)) {
	$out = "Signup times reset.<p>";
	dbn("update ${db_name}_users set joined_game = " . time());
	insert_history($user['login_id'],"Reset Signup Times.");
}


#reset game
if(isset($reset)){
	if($reset == 1){
		$out .= "Are you sure you want to reset the game?";
		$out .= "<center><a href=$PHP_SELF?reset=2>Yes</a>&nbsp&nbsp&nbsp&nbsp&nbsp<a href=$PHP_SELF>No</a></center>";
	} elseif($reset == 2) {
		$out .= "Game reset started.<p>";

		dbn("delete from ${db_name}_users where login_id != " . ADMIN_ID);
		dbn("delete from ${db_name}_user_options where login_id != " . ADMIN_ID);
		$out .= "Users & their options deleted.<br>";

		dbn("update ${db_name}_users set turns='1000', turns_run='0', location=1, ship_id=NULL, cash='100000000', on_planet='0', last_attack='0', last_attack_by='', ships_killed='0', ships_lost=0, ships_lost_points=0, ships_killed_points=0, genesis='1', gamma='1', clan_sym='', clan_sym_color='', clan_id=0, fighters_killed='0', one_brob='0', alpha='1', sn_effect='0', last_request='0', score='0', tech='100000' where login_id=" . ADMIN_ID);
		$out .= "Admin account refurbished.<br>";

		dbn("delete from ${db_name}_news");
		$out .= "News erased.<br>";

		dbn("delete from ${db_name}_planets");
		$out .= "Planets erased.<br>";

		dbn("delete from ${db_name}_messages where login_id != " . ADMIN_ID . ' && login_id != ' . OWNER_ID);
		$out .= "Messages deleted.<br>";

		dbn("delete from ${db_name}_politics");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '1', 'Monarch', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '2', 'Industry Senator', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '3', 'Military Senator', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '4', 'Defense Senator', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '5', 'Trade Senator', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '6', 'War Senator', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '7', 'Espionage Senator', '0', '', '0')");
		dbn("INSERT INTO ${db_name}_politics VALUES ( '8', 'Research Senator', '0', '', '0')");
		$out .= "Politics refurbished.<br>";

		dbn("delete from ${db_name}_diary where login_id != " . ADMIN_ID . ' && login_id != ' . OWNER_ID);
		$out .= "Diaries erased.<br>";

		dbn("delete from ${db_name}_ships");
		$out .= "Ships deleted.<br>";

		dbn("delete from ${db_name}_clans");
		$out .= "Clans deleted.<br>";

		dbn("delete from ${db_name}_bilkos");
		$out .= "Bilkos Auction House Emptied.<br>";

		dbn("update se_games set last_reset = ".time()." where db_name = '$db_name'");
		$out .= "Last reset date updated to now.<br>";

		post_news("Game Reset.");
	}
	insert_history($user['login_id'],"Reset Game");
	print_page("Reset Game",$out);
}


#list all planets in game
if(isset($planet_list) || isset($sort_planets)){

	if(!empty($sort_planets)){
		if($sorted==1){
			$going = "asc";
			$sorted=2;
		} else {
			$going = "desc";
			$sorted=1;
		}
		db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where location != 1 && planet_type >= 0 order by '$sort_planets' $going");
	} else {
		db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where location != 1 && planet_type >= 0 order by login_name asc, fighters desc, planet_name asc");
	}

	$clan_planet = dbr(1);
	if($clan_planet) {
		$out .= $rs.make_table(array("<a href=$PHP_SELF?sort_planets=login_name&sorted=$sorted>Planet Owner</a>","<a href=$PHP_SELF?sort_planets=planet_name&sorted=$sorted>Planet Name</a>","<a href=$PHP_SELF?sort_planets=location&sorted=$sorted>Location</a>","<a href=$PHP_SELF?sort_planets=fighters&sorted=$sorted>Fighters</a>","<a href=$PHP_SELF?sort_planets=colon&sorted=$sorted>Colonists</a>","<a href=$PHP_SELF?sort_planets=cash&sorted=$sorted>Cash</a>","<a href=$PHP_SELF?sort_planets=metal&sorted=$sorted>Metal</a>","<a href=$PHP_SELF?sort_planets=fuel&sorted=$sorted>Fuel</a>","<a href=$PHP_SELF?sort_planets=elect&sorted=$sorted>Electronics</a>","<a href=$PHP_SELF?sort_planets=organ&sorted=$sorted>Organics</a>"));
		while($clan_planet) {
			$clan_planet['login_name'] = "<b class=b1>$clan_planet[login_name]</b>";
			$out .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$out .= "</table>";
		print_page("Planet List",$out);
	} else {
		$out .= "There are no planets in the game.<p>";
	}
}


#change intro message
if(isset($messag)){
	if($messag == 1){
		db("select intro_message from se_games where db_name = '$db_name'");
		$present_mess = dbr();
		$present_mess[0] = stripslashes($present_mess[0]);
		$out .= "Please enter a message that all new players will recieve when they join. <p>Notes: HTML is enabled. Message codes are not used.";
		$out .= "<form name=get_var_form action=$PHP_SELF method=POST>";
		$out .= "<input type=hidden name=messag value='2'>";
		$out .= "<textarea name=new_mess cols=50 rows=20 wrap=soft>$present_mess[0]</textarea>";
		$out .= '<p><input type=submit value=Change></form>';
	} else {
		$new_mess = addslashes($new_mess);
		dbn("update se_games set intro_message = '$new_mess' where db_name = '$db_name'");
		$out .= "The Intro message has been changed.";
	}
	print_page("Change Intro Message",$out);
	insert_history($user['login_id'],"Intro Message Changed.");
}


#change admin email
if(isset($email)){
	if($email == 1){
		db("select admin_email from se_games where db_name = '$db_name'");
		$present_mail = dbr();
		$out .= "Please enter New Admin E-mail Address:";
		$out .= "<form name=get_var_form action=$PHP_SELF method=POST>";
		$out .= "<input type=hidden name=email value='2'>";
		$out .= "<input type=text name=new_mail value='$present_mail[0]' size=30>";
		$out .= '<p><input type=submit value=Change></form>';
	} else {
		if(!ereg("@",$new_mail) || !ereg("\.",$new_mail)){
			 print_page("Admin Mail","Please Enter a Valid Email Address");
		}
			dbn("update se_games set admin_email = '$new_mail' where db_name = '$db_name'");
		$out .= "Admins Email Address has been changed to: <br><b>$new_mail</b>.";
	}
	print_page("Change Admin Mail",$out);
	insert_history($user['login_id'],"Admin E-mail Addy changed.");
}


#change admin name
if(isset($admin_name)){
	if($admin_name == 1){
		db("select admin_name from se_games where db_name = '$db_name'");
		$admin_name = dbr();
		$out .= "Please enter New Admin's Name:";
		$out .= "<form name=get_var_form action=$PHP_SELF method=POST>";
		$out .= "<input type=hidden name=admin_name value='2'>";
		$out .= "<input type=text name=new_name value='$admin_name[0]' size=30>";
		$out .= '<p><input type=submit value=Change></form>';
	} else {
		dbn("update se_games set admin_name = '$new_name' where db_name = '$db_name'");
		$out .= "Admin's Name has been changed to: <br><b>$new_name</b>.";
	}
	print_page("Change Admin Mail",$out);
	insert_history($user['login_id'],"Admin Name Changed.");
}



#change game description
if(isset($descr)){
	if($descr == 1){
		db("select description from se_games where db_name = '$db_name'");
		$present_desc = dbr();
		$out .= "Please enter some words that explain the game.<p>Note: HTML is enabled, but does not use the message codes. <br>(Leave empty if you don't want to use it)";
		$out .= "<form name=get_var_form action=$PHP_SELF method=POST>";
		$out .= "<input type=hidden name=descr value='2'>";
		$out .= "<textarea name=new_descr cols=50 rows=20 wrap=soft>$present_desc[0]</textarea>";
		$out .= '<p><input type=submit value=Change></form>';
	} else {
		$new_descr = stripslashes($new_descr);
		$new_descr = addslashes($new_descr);
		dbn("update se_games set description = '$new_descr' where db_name = '$db_name'");
		$out .= "The description of the game has been changed.";
	}
	print_page("Change Description",$out);
	insert_history($user['login_id'],"Game description changed.");
}

#list all admin options
$self = esc($_SERVER['SCRIPT_NAME']);
db("select paused from se_games where db_name = '$db_name'");
list($paused) = dbr();
$pauseStr = $paused ? 'Un-Pause' : 'Pause';
$pauseId = $paused ? 2 : 1;

$out .= <<<END
<h1>Administration</h1>

<h2>Game Functions</h2>
<ul>
	<li><a href="$self?game_vars=1">Edit Variables</a></li>
	<li><a href="$self?pause=$pauseId">$pauseStr Game</a></li>
	<li><a href="$self?reset=1">Reset Game</a></li>
	<li><a href="$self?reset_signup=1">Reset Signup Times</a></li>
	<li><a href="shipedit.php?editshiptype=-1">Edit Ships</a></li>
	<li><a href="$self?difficulty=1">Change Stated Difficulty</a></li>
</ul>

<h2>Godlike Abilities</h2>
<ul>
	<li><a href="build_universe.php?build_universe=1&amp;process=1">Generate New Universe</a></li>
	<li><a href="$self?preview=1">Preview Universe</a></li>
</ul>

<h2>Communications</h2>
<ul>
	<li><a href="message.php?target=-4">Message <b>All</b> Players</a></li>
	<li><a href="$self.php?post_game_news=1">Post News</a></li>
</ul>

<h2>Players</h2>
<ul>
	<li><a href="$self?ban=1">Ban Player</a></li>
	<li><a href="$self.php?show_active=1">List Online Players</a></li>
	<li><a href="$self?planet_list=1">List All Planets</a></li>
	<li><a href="$self?update_scores=1">Update Scores</a></li>
	<li><a href="$self?more_money=1">Give Money</a></li>
</ul>

<h2>General</h2>
<ul>
	<li><a href="$self?admin_name=1">Change Admin Name</a></li>
	<li><a href="$self?email=1">Change Admin E-mail</a></li>
	<li><a href="$self?descr=1">Change Game Description</a></li>
	<li><a href="$self?messag=1">Change Intro Message</a></li>
</ul>
END;

$rs = "<p><a href=location.php>Back to Star System</a>";
print_page("Admin", $out);

?>
