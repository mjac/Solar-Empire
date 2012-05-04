<?php

require_once('inc/user.inc.php');

$filename = 'clan.php';

$ret_str = '<p><a href=location.php>Return to star system</a>';
$error_str = "";

sudden_death_check($user);


if(isset($join)) { // Join clan
	if($user['clan_id']) {
		print_page('Join Clan','You are already a member of a clan.',"?clans=1");
	}
	db("select * from ${db_name}_clans where clan_id = $join");
	$clan = dbr(1);
	if($clan['members'] >= $clan_member_limit && $user['login_id'] != ADMIN_ID) {
		print_page('Join Clan','That clan already has the maximum number of members allowed.',"?clans=1");
	} elseif($user['clan_id'] > 0){
		print_page('Join Clan','You are already a member of a clan.',"?clans=1");
	}
	if($user['login_id'] == ADMIN_ID) {
		$passwd = $clan['passwd'];
	}
	if(empty($passwd)) {
		get_var('Join Clan',$filename,'What is the clan password?','passwd','');
	} elseif($clan['passwd'] != $passwd) {
		print_page('Join Clan','The password is incorrect.',"?clans=1");
	} else {
		dbn("update ${db_name}_users set clan_id = $join, clan_sym = '$clan[symbol]', clan_sym_color = '$clan[sym_color]' where login_id = $user[login_id]");
		dbn("update ${db_name}_planets set clan_id = $join where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set clan_id = $join where login_id = $user[login_id]");
		$user['clan_id'] = $join;
		$user['clan_sym'] = $clan['symbol'];
		$user['clan_sym_color'] = $clan['sym_color'];
		if($user['login_id'] != ADMIN_ID){
			dbn("update ${db_name}_clans set members = members + 1 where clan_id = '$join'");
			send_message($clan['leader_id'],"<b class=b1>$user[login_name]</b> has joined your clan.");
		}
		insert_history($user['login_id'],"Joined $clan[clan_name] clan.");
	}


} elseif(isset($leave)) { // Leave clan
	db("select leader_id,clan_name from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if($clan['leader_id'] == $user['login_id']) {
		$error_str .= "The clan leader may not leave the clan.<p>You may assign a new leader and then leave.";
	} else {
		dbn("update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where login_id = $user[login_id]");
		dbn("update ${db_name}_planets set clan_id = -1 where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set clan_id = -1 where login_id = $user[login_id]");

		if($user['login_id'] != ADMIN_ID){
			dbn("update ${db_name}_clans set members = members - 1 where clan_id = '$user[clan_id]'");
			send_message($clan['leader_id'],"<b class=b1>$user[login_name]</b> has left your clan.");
		}
		$user['clan_id'] = 0;
		$user['clan_sym'] = "";
		$user['clan_sym_color'] = "";
	}
	insert_history($user['login_id'],"Left $clan[clan_name] clan.");


} elseif(isset($kick)) { // Kick a clan member
	db("select leader_id,clan_name from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	db2("select clan_id,login_name from ${db_name}_users where login_id='$kick'");
	$kick_clan = dbr2();
	if($clan['leader_id'] != $user['login_id'] && $user['login_id'] !=1) {
		$error_str .= "You are not the leader of this clan.<p>";
	} elseif($user['clan_id'] < 1) {
		$error_str .= "You are not in a clan as such.<p>";
	} elseif($kick_clan['clan_id'] != $user['clan_id']) {
		$error_str .= "You can only kick members of your own clan.<p>";
	} elseif($kick == $clan['leader_id']) {
		$error_str .= "You may not Kick the Clan Leader out of the clan.<p>";
	} elseif($kick == 1) {
		$error_str .= "You may not Kick the Admin out of your clan.<p>";
	} elseif(!isset($sure)) {
		get_var('Kick Clan Member',$filename,'Are you sure you want to kick this clan member out?','sure','yes');
	} else {
		dbn("update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where login_id = $kick");
		dbn("update ${db_name}_planets set clan_id = -1 where login_id = $kick");
		dbn("update ${db_name}_ships set clan_id = -1 where login_id = $kick");
		dbn("update ${db_name}_clans set members = members - 1 where clan_id = '$kick_clan[clan_id]'");
		$error_str .= "User <b class=b1>$kick_clan[login_name]</b> kicked out of the clan.<p>";
		insert_history($user['login_id'],"Thrown out of $clan[clan_name] clan.");
	}


}elseif(isset($disband)) { // Disband clan
	db("select * from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if(($clan['leader_id'] != $user['login_id']) && ($user['login_id'] != ADMIN_ID)) {
		$error_str .= "You are not the leader of this clan.<p>";
	} elseif($user['clan_id'] < 1) {
		$error_str .= "You are not in a clan as such.<p>";
	} elseif(!isset($sure)) {
		get_var('Disband Clan',$filename,'Are you sure you want to disband this clan?','sure','yes');
	} else {
		post_news("<b class=b1>$user[login_name]</b> disbanded the <b class=b1>$clan[clan_name](<font color=$clan[sym_color]>$clan[symbol]</font>)</b> Clan.");

		dbn("update ${db_name}_users set clan_id = 0, clan_sym = '', clan_sym_color = '' where clan_id = $user[clan_id]");
		dbn("update ${db_name}_planets set clan_id = -1 where clan_id = $user[clan_id]");
		dbn("update ${db_name}_ships set clan_id = -1 where clan_id = $user[clan_id]");
		dbn("delete from ${db_name}_clans where clan_id = $user[clan_id]");
		dbn("delete from ${db_name}_messages where clan_id = $user[clan_id]");
		$user['clan_id'] = 0;
		$user['clan_sym'] = "";
		$user['clan_sym_color'] = "";
		insert_history($user['login_id'],"Disbanded $clan[clan_name] clan.");
	}


} elseif(isset($create)) { //Create a new clan
	db("select count(*) from ${db_name}_clans where clan_id");
	$result_max_clans = dbr();

	if ($result_max_clans[0] >= $max_clans && $user['login_id'] != ADMIN_ID) {
		$error_str .= "The Maximum allowed clans set by the admin has been met.";
	} elseif($user['clan_id'] > 0){
		$error_str = "You are already a member of a clan.<p>";
	}elseif(empty($name)) {
		get_var('Create Clan',$filename,'What should the name of your new clan be?','name','');
	} elseif (empty($symbol)) {
		get_var('Create Clan',$filename,'Please choose a three letter symbol for your clan.<br><b class=b1>Must</b> have either two or three letters: <br>(only symbols acceptable: a-z0-9~@$%&*_+-=£§¥²³µ¶)','symbol','');
	} else {
		if (empty($sym_color)) {
			$tempstr = "<form action=\"clan.php\" method=\"post\">";
			foreach($_REQUEST as $key => $value){
				$tempstr .= "<input type=\"hidden\" name=\"$key\" value=\"$value\">";
			}
			$tempstr .= "Choose a color for the clan symbol:<p>";

			$tempstr .= "Enter your own: <input type=\"text\" name=\"sym_color\" size=\"15\" /><p> Or pick one:";
			$tempstr .= "<table>\n\t<tr>";
			$i = 0;
			foreach ($msgColours as $name => $hex) {
				$thisColour = "\n\t\t<td><input type=\"radio\" name=\"sym_color\" value=\"$hex\"> <span style=\"color: #$hex;\">$name</span></td>";
				if ($i !== 0 && !($i % 4)) {
					$tempstr .= "\n\t</tr>\n\t<tr>" . $thisColour . "";
				} else {
					$tempstr .= $thisColour;
				}
				++$i;
			}
			$tempstr .= "\n\t</tr>\n</table>
<input type=\"submit\" value=\"Submit\">
</form>";
			print_page('Choose symbol color',$tempstr,"?clans=1");

		} elseif(empty($passwd)) {
			get_var('Create Clan',$filename,'What should the clan password be? (5 Characters Minimum, 25 Max)','passwd','');
		} elseif(levenshtein($passwd,$p_user['passwd']) < 2) { //password cannot be too similar to account pass
			print_page ("Error","That password is too similar to your account password. Please use a different password.");
		} elseif(empty($passwd_verify)) {
			get_var('Create Clan',$filename,'Please enter the clan password again.','passwd_verify','');

		} else {

			if(strlen($passwd) < 5) {
				print_page("Create Clan",'The password must be at least 5 characters.',"?clans=1");
			}elseif($passwd == $p_user['passwd']) {
				print_page("Create Clan",'No-way. You may not use the same pass as your user account. Try a different one.',"?clans=1");
			}elseif($passwd != $passwd_verify) {
				print_page("Create Clan",'The passwords did not match.',"?clans=1");
			}

			$symbol = substr($symbol,0,3);
			if(strlen($symbol) < 2) {
				print_page('Create Clan','Your clan symbol must have at least one character.',"?clans=1");
			}

			if(!valid_input($symbol)) {
				print_page('Create Clan','Your clan name may contain only letters, numbers or any of these characters: ~!@#$%&*_+-=��������׀��',"?clans=1");
			}

			db("select symbol from ${db_name}_clans where symbol = '$symbol'");
			$temp_result = dbr(1);
			if(!empty($temp_result)) {
				print_page('Create Clan','That symbol is already in use.',"?clans=1");
			}

			$name = htmlspecialchars($name);
			$name = addslashes($name);
			$sym_color = substr($sym_color,0,6);

			$sym_color = htmlspecialchars($sym_color);
			$sym_color = addslashes($sym_color);
			$symbol = htmlspecialchars($symbol);
			$symbol = addslashes($symbol);
			$passwd = addslashes($passwd);

			$q_string = "insert into ${db_name}_clans (";
			$q_string = $q_string . "clan_name,leader_id,passwd,symbol,sym_color";
			$q_string = $q_string . ") values(";
			$q_string = $q_string . "'$name','$user[login_id]','$passwd','$symbol','$sym_color')";
			db($q_string);

			$clan_id = mysql_insert_id();
			dbn("update ${db_name}_planets set clan_id = $clan_id where login_id = $user[login_id]");
			dbn("update ${db_name}_ships set clan_id = $clan_id where login_id = $user[login_id]");
			dbn("update ${db_name}_users set clan_id = $clan_id, clan_sym = '$symbol', clan_sym_color = '$sym_color' where login_id = $user[login_id]");

			$user['clan_id'] = $clan_id;
			$user['clan_sym'] = $symbol;
			$user['clan_sym_color'] = $sym_color;
			post_news("<b class=b1>$user[login_name]</b> created the <b class=b1>$name(<font color=$sym_color>$symbol</font>)</b> Clan.");
			insert_history($user['login_id'],"Created the $name clan.");
		}
	}

} elseif(isset($lead_change)) { // Assign new leader
	db("select leader_id from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	if($user['clan_id'] < 1) {
		$error_str .= "You are not in a clan as such.<p>";
	} elseif(($clan['leader_id'] != $user['login_id']) && ($user['login_id'] != ADMIN_ID)) {
		$error_str .= "You are not the leader of this clan.<p>";
	} elseif(!$leader_id) {
		db2("select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '1' && login_id != '$clan[leader_id]'");
		$member_name = dbr2(1);
		if($member_name) {
			$ostr .= "<form action=$filename method=POST>";
			$ostr .= "Please choose another clan member to be the leader:<p>";
			while (list($var, $value) = each($HTTP_GET_VARS)) {
				$ostr .= "<input type=hidden name=$var value='$value'>";
			}
			while (list($var, $value) = each($HTTP_POST_VARS)) {
				$ostr .= "<input type=hidden name=$var value='$value'>";
			}
			$ostr .= "<select name=leader_id>";
			while ($member_name) {
				$ostr .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
				$member_name = dbr2(1);
			}
			$ostr .= "</select>";
			//$ostr .= "<input type=hidden name=sure value='no'>";
			$ostr .= ' <input type=submit value=Submit></form>';
			print_page('Choose new clan leader',$ostr,"?clans=1");
		} else {
			print_page('Error',"No-one in your clan can become clan leader. That means u're stuck as clan leader.","?clans=1");
		}
	} elseif(!isset($sure) && $user['login_id'] != ADMIN_ID) {
		get_var('Change Clan Leader',$filename,'Are you sure you want to relinquish leadership of this clan?','sure','yes');
	} else {
		dbn("update ${db_name}_clans set leader_id = $leader_id where clan_id = $user[clan_id]");
		$clan['leader_id'] = $leader_id;
		$error_str .= "Clan leader changed<p>";
	}


}

#################
#Default clan page - if not in a clan.
################

if(isset($ranking) || ($user['clan_id'] < 1 && empty($clan_info))) { // Clan Ranking

if(!isset($ranking)){
	$ranking = 0;
}

	db("select count(clan_id) from ${db_name}_clans");
	$clan_count = dbr();

	if($clan_count[0] > 0) {

		if(isset($change_dir) && $change_dir == 1){
			$order_dir = "asc";
		} else {
			$order_dir = "desc";
			$dir_array = array_fill(0,10,"");
			$dir_array[$ranking] = "&change_dir=1";
		}

		if($ranking == 2){
			$order_by_str = "Clan Name";
			$order_by_sql = "c.clan_name";
		} elseif($ranking == 3){
			$order_by_str = "Members";
			$order_by_sql = "members";
		} elseif($ranking == 4){
			$order_by_str = "Fighters Killed";
			$order_by_sql = "fkilled";
		} elseif($ranking == 5){
			$order_by_str = "Fighters Lost";
			$order_by_sql = "flost";
		} elseif($ranking == 6){
			$order_by_str = "Ships Killed";
			$order_by_sql = "skilled";
		} elseif($ranking == 7){
			$order_by_str = "Ships Lost";
			$order_by_sql = "slost";
		} elseif($ranking == 8){
			$order_by_str = "Turns Run";
			$order_by_sql = "trun";
		} else {
			$order_by_str = "Score";
			$order_by_sql = "score";
		}

		#get details of each clan
		db2("select c.clan_id,c.clan_name,c.symbol,c.sym_color, count(u.login_id) as members, sum(u.fighters_killed) as fkilled, sum(u.fighters_lost) as flost, sum(u.ships_killed) as skilled, sum(u.ships_lost) as slost, sum(u.turns_run) as trun, sum(u.score) as score from ${db_name}_clans c, ${db_name}_users u where u.clan_id = c.clan_id GROUP by c.clan_id order by $order_by_sql $order_dir");
		$clan = dbr2(1);


		$error_str .= "There are <b>$clan_count[0]</b> clans at present. <br>The clan limit for this game is <b>$max_clans</b>.<p>Ranking listed by <b class=b1>$order_by_str</b><p>";
		$error_str .= make_table(array("Rank","<a href=$filename?ranking=2".$dir_array[2].">Clan Name</a>", "<a href=$filename?ranking=3".$dir_array[3].">Members</a>", "<a href=$filename?ranking=4".$dir_array[4].">Fighters<br>Killed</a>", "<a href=$filename?ranking=5".$dir_array[5].">Fighters<br>Lost</a>", "<a href=$filename?ranking=6".$dir_array[6].">Ships<br>Killed</a>", "<a href=$filename?ranking=7".$dir_array[7].">Ships<br>Lost</a>", "<a href=$filename?ranking=8".$dir_array[8].">Turns<br>Run</a>", "<a href=$filename?ranking=1".$dir_array[1].">Score</a>"));

		$ct1 = 1;
		$ct2 = 1;
		$last = "";

		while($clan) {
			if(isset($player) && $player[$order_by_sql] != $last) {
				$last = $player[$order_by_sql];
				if($ct2 > 1){
					$ct1 = $ct2;
				}
			}
			$option = "";
			if((($user['clan_id'] == 0) && ($clan['members'] < $clan_member_limit)) || $user['login_id'] == ADMIN_ID) {
				$option = "<a href=clan.php?join=$clan[clan_id]>Join</a>";
			} elseif($clan['members'] >= $clan_member_limit) {
				$option = "Full";
			} elseif($clan['clan_id'] == $user['clan_id']) {
					$option = "<a href=clan.php>View</a>";
			}

			$error_str .= make_row(array($ct1,"<b class=b1>$clan[clan_name]</b>(<b><font color=$clan[sym_color]>$clan[symbol]</font></b>)",$clan['members'],$clan['fkilled'],$clan['flost'],$clan['skilled'],$clan['slost'],$clan['trun'],$clan['score'],$option,"<a href=clan.php?clan_info=1&target=$clan[clan_id]>Details</a>"));

			$ct2++;
			$clan = dbr2(1);
		}
		$error_str .= "</table>";
	} else {
		$error_str .= "<br>There are no clans at present. <br>Maximum number of Clans allowed is <b>$max_clans</b>.";
	}
	if(($user['clan_id'] == 0 && $clan_count[0] < $max_clans) || $user['login_id'] == ADMIN_ID) {
		$error_str .= "<p><a href=clan.php?create=1>Create a new clan</a><br>";
	} elseif($clan_count[0] >= $max_clans) {
		$error_str .= "<p>The Maximum number of clans(<b>$max_clans</b>) has been reached.";
	}
	print_page("Clan Rankings",$error_str,"?clans=1");
}

if(isset($changepass)) {// change password
	db("select leader_id,passwd from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	$rs = "<a href=clan.php>Back To Clan Control</a>";
	if($user['clan_id'] < 1) {
		print_page("stop that","You are not in a clan as such.<p>","?clans=1");
	} elseif($clan['leader_id'] != $user['login_id'] && $user['login_id'] != ADMIN_ID) {
		print_page("stop that","You are not the clan leader.<p>","?clans=1");
	} elseif($changepass==1) {
		$temp_str = "Passwords must be minimum of five(5) characters long and a max of 25.";
		$temp_str .= "<table><form action=clan.php method=post><input type=hidden name=changepass value=changed>";

		if($login_id != 1){ #don't ask for the old pass for the admin
			$temp_str .= "<tr><td align=right>Old Password:</td><td><input type=password name=oldpass></td></tr>";
		}

		$temp_str .= "<tr><td align=right>New Password:</td><td><input type=password name=newpass></td></tr>";
		$temp_str .= "<tr><td align=right>Re-type New Password:</td><td><input type=password name=newpass2></td></tr>";
		$temp_str .= "<tr><td></td><td><input type=Submit value='Change Password'></td></tr></form></table><p>";
		print_page("Change Password",$temp_str,"?clans=1");
	} elseif ($changepass == 'changed') {
		if (isset($newpass) && ($newpass == $newpass2)) {
			if(strlen($newpass) < 5) {
				$temp_str = "The password must be at least 5 characters.<p>";
			}elseif($newpass == $user['passwd']) {
				$temp_str = "No-way. You may not use the same pass as your user account. Try a different one.<p>";
			} elseif($newpass == $oldpass) {
				$temp_str = "What are you wasting my bandwith for? Thats the same as the previous pass. Try something else.<p>";
			} elseif ($oldpass != $clan['passwd'] && $login_id != 1) { #admin doesn't need old pass
				$temp_str = "The old password is not correct!<br>";
				$temp_str .= "<a href='javascript:back()'>Go back</a><p>";
			} else {
				dbn("update ${db_name}_clans set passwd='$newpass' where clan_id=$user[clan_id]");
				$clan['passwd']='$newpass';
				$temp_str .= "Clan password changed successfully<p>";
			}
		} else {
			$temp_str = "Password mismatch!<br>";
			$temp_str .= "<a href='javascript:back()'>Go back</a><br>";
		}
		print_page("Change Password",$temp_str,"?clans=1");
	}

} elseif(isset($clan_info) && $target > 0){ #show clan info

	$x_link .= "<a href=clan.php>Clan Control</a>";

	if($user['login_id'] == ADMIN_ID || $user['clan_id'] == $target) { #admin can see all, as can clan members.
		$full = 1;
	} else {
		$full = 0;
	}

	#list some statistics about the clan, as user is a member (or admin).
	if($full == 1){
		#planet details
		db("select sum(cash) as cash,sum(tech) as tech,sum(fighters) as pfigs, count(planet_id) as planets, count(launch_pad) as lpads, sum(research_fac) as rfac, count(shield_gen) as sgens, sum(shield_charge) as scharge, sum(colon) as colon from ${db_name}_planets where clan_id = '$target'");
		$res1 = dbr(1);


		#planet percentages
		db("select sum(cash) as cash,sum(tech) as tech,sum(fighters) as pfigs, count(planet_id) as planets from ${db_name}_planets where login_id > '5'");
		$maths1 = dbr(1);


		#ship detals
		db("select sum(fighters) as sfigs, sum(max_fighters) as max_figs, count(ship_id) as ships, sum(cargo_bays) as cargo from ${db_name}_ships where clan_id = '$target'");
		$res2 = dbr(1);

		#used for ship percentages
		db("select sum(fighters) as sfigs, count(ship_id) as ships from ${db_name}_ships where login_id > '5'");
		$maths2 = dbr(1);


		#get user detals.
		db("select count(login_id) as members, sum(cash) as cash, sum(genesis) as gen, sum(terra_imploder) as imploder, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(bounty) as bounty, sum(score) as score, sum(alpha) as alpha, sum(gamma) as gamma, sum(delta) as delta, sum(sn_effect) as sne, sum(tech) as tech, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun, sum(turns) as turns, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost from ${db_name}_users where clan_id = '$target'");
		$res3 = dbr(1);

		#used to calculate percentages
		db("select count(login_id) as members, sum(cash) as cash, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(bounty) as bounty, sum(score) as score, sum(tech) as tech, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(ships_killed_points) as spkilled, sum(ships_lost_points) as splost, sum(turns_run) as trun, sum(turns) as turns from ${db_name}_users where login_id > '5'");
		$maths3 = dbr(1);

		$temp_str .= $x_link."<br><br>"; #link to clan control
	} else {#only partial listing given, so only get small amounts of data.
		db("select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from ${db_name}_users where clan_id = '$target'");
		$res3 = dbr(1);

		#for percentages
		db("select count(login_id) as members, sum(fighters_killed) as fkilled, sum(fighters_lost) as flost, sum(ships_killed) as skilled, sum(ships_lost) as slost, sum(turns_run) as trun from ${db_name}_users where login_id > '5'");
		$maths3 = dbr(1);
	}

	$temp_str .= make_table(array("",""));

	db("select clan_name, passwd, leader_id, symbol, sym_color from ${db_name}_clans where clan_id = '$target'");
	$cd = dbr(1);

	$temp_str .= quick_row("Clan Name",$cd['clan_name']);
	$temp_str .= quick_row("Clan Symbol","<font color=#".$cd['sym_color'].">$cd[symbol]</font>");
	$temp_str .= quick_row("Member Count",$res3['members']);

	if($full == 0){
		$temp_str .= quick_row("Fighters Killed",calc_perc($res3['fkilled'],$maths3['fkilled']));
		$temp_str .= quick_row("Fighters Lost",calc_perc($res3['flost'],$maths3['flost']));
		$temp_str .= quick_row("Ships Killed",calc_perc($res3['skilled'],$maths3['skilled']));
		$temp_str .= quick_row("Ships Lost",calc_perc($res3['slost'],$maths3['slost']));
		$temp_str .= quick_row("Turns Run",calc_perc($res3['trun'],$maths3['trun']));
		$temp_str .= "</table><br><br>Below is a listing of the members of the <b class=b1>$cd[clan_name]</b1> clan. ".make_table(array("User","Turns Run","Fighters Killed","Fighters Lost","Ships Killed","Ships Lost"));

		db("select login_id,turns_run, fighters_killed,fighters_lost, ships_killed, ships_lost from ${db_name}_users where clan_id = '$target'");
		while($clan_members = dbr(1)){
			$clan_members['login_id'] = print_name($clan_members);
			$clan_members['fighters_killed'] = calc_perc($clan_members['fighters_killed'],$maths3['fkilled']);
			$clan_members['fighters_lost'] = calc_perc($clan_members['fighters_lost'],$maths3['flost']);
			$clan_members['ships_killed'] = calc_perc($clan_members['ships_killed'],$maths3['skilled']);
			$clan_members['ships_lost'] = calc_perc($clan_members['ships_lost'],$maths3['slost']);
			$clan_members['turns_run'] = calc_perc($clan_members['turns_run'],$maths3['trun']);
			$temp_str .= make_row($clan_members);
		}

	} else {
		$temp_str .= quick_row("&nbsp;","");
		$temp_str .= quick_row("Cash",calc_perc($res3['cash'] + $res1['cash'],$maths3['cash'] + $maths1['cash']));
		$temp_str .= quick_row("Tech Units",calc_perc($res3['tech'] + $res1['tech'],$maths3['tech'] + $maths1['tech']));
		$temp_str .= quick_row("Turns",calc_perc($res3['turns'],$maths3['turns']));
		$temp_str .= quick_row("Turns Run",calc_perc($res3['trun'],$maths3['trun']));
		$t_figs = $res1['pfigs'] + $res2['sfigs'];
		$t_fcap = $maths1['pfigs'] + $maths2['sfigs'];
		$temp_str .= quick_row("Total Fighters",calc_perc($t_figs,$t_fcap));
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row("Ships Killed",calc_perc($res3['skilled'],$maths3['skilled']));
		$temp_str .= quick_row("Ships Lost",calc_perc($res3['slost'],$maths3['slost']));
		$temp_str .= quick_row("Ship Points Killed",calc_perc($res3['spkilled'],$maths3['spkilled']));
		$temp_str .= quick_row("Ship Points Lost",calc_perc($res3['splost'],$maths3['splost']));
		$temp_str .= quick_row("Fighters Killed",calc_perc($res3['fkilled'],$maths3['fkilled']));
		$temp_str .= quick_row("Fighters Lost",calc_perc($res3['flost'],$maths3['flost']));
		$temp_str .= quick_row("Score",calc_perc($res3['score'],$maths3['score']));
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row("Bounty",calc_perc($res3['bounty'],$maths3['bounty']));
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row("Planets",calc_perc($res1['planets'],$maths1['planets']));
		$temp_str .= quick_row("Planetary Fighters",calc_perc($res1['pfigs'],$maths1['pfigs']));
		$temp_str .= quick_row("Launch Pads",$res1['lpad']);
		$temp_str .= quick_row("Research Facilities",$res1['rfac']);
		$temp_str .= quick_row("Shield Generators",$res1['sgens']);
		$temp_str .= quick_row("Shield Charges",$res1['scharge']);
		$temp_str .= quick_row("Colonists",$res1['colon']);
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row("Ships",calc_perc($res2['ships'],$maths2['ships']));
		$temp_str .= quick_row("Ship Fighters",calc_perc($res2['sfigs'],$maths2['sfigs']));
		$temp_str .= quick_row("Fleet Fighter Capacity",$res2['max_figs']." Fighters");
		$temp_str .= quick_row("Fleet Cargo Capacity",$res2['cargo']." Units");
		$temp_str .= quick_row("&nbsp;","");

		$temp_str .= quick_row("Genesis Devices",$res3['gen']);
		if($uv_planets >= 0){
			$temp_str .= quick_row("Terra Imploders",$res3['imploder']);
		}
		if($flag_bomb < 2){
			$temp_str .= quick_row("Alpha Bombs",$res3['alpha']);
			$temp_str .= quick_row("Gamma Bombs",$res3['gamma']);
		}
		$temp_str .= quick_row("Delta Bombs",$res3['delta']);
		if($random_events == 3){
			$temp_str .= quick_row("SuperNova Effectors",$res3['alpha']);
		}
		$temp_str .= quick_row("&nbsp;","");

	}

	$temp_str .= "</table><br>";

	print_page("Clan Info",$temp_str.$x_link,"?clans=1");



} else {

	// print normal page for clan-member
	db("select * from ${db_name}_clans where clan_id = $user[clan_id]");
	$clan = dbr(1);
	$clan_name = stripslashes($clan['clan_name']);


	#change a ship's fleet
	if(isset($fleet_type) && $user['login_id'] == $clan['leader_id']){
		if($join_fleet_id_2 != 0){
			$join_fleet_id = $join_fleet_id_2;
		}

		$error_str .= "<br>".change_fleet_num($join_fleet_id,1,$do_ship,"ship_id")."<p><br>";
	}




	$error_str .= "You are a member of the <b class=b1>$clan_name</b>(<font color=$clan[sym_color]>$clan[symbol]</font>) clan.<p>";

	if($clan['leader_id'] == $user['login_id']){
		$error_str .= "Password for the clan is <b class=b1>$clan[passwd]</b>.<br><br>";
	}

	$error_str .= make_table(array("Member","Turns","Cash","Tech Units","Kills","Status"));
	db("select login_name,turns,cash,tech,ships_killed,last_request,login_id from ${db_name}_users where clan_id = $user[clan_id] order by login_name,ships_killed");
	$clan_member = dbr(1);
	while($clan_member) {
		if($clan['leader_id'] == $clan_member['login_id']) {
			$clan_member['login_name'] = "(L) ".print_name($clan_member);
		} else {
			$clan_member['login_name'] = print_name($clan_member);
		}
		if($clan_member['last_request'] > (time()-300)){
			$clan_member['last_request'] = "Online";
		} else {
			$clan_member['last_request'] = "N/A";
		}
		$temp_id = $clan_member['login_id'];
		if($clan_member['login_id'] != $user['login_id']){
			$clan_member['login_id'] = "<a href=message.php?target=$clan_member[login_id]>Message</a>";
		} else {
			$clan_member['login_id'] = "";
		}
		if(($user['login_id'] == $clan['leader_id'] || $user['login_id'] == ADMIN_ID) &&	($temp_id != $clan['leader_id'] && $temp_id != 1)) {
			$clan_member['login_id'] .= " - <a href=clan.php?kick=$temp_id>Kick</a>";
		}
		$error_str .= make_row($clan_member);
		$clan_member = dbr(1);
	}
	$error_str .= "</table>";
	$error_str .= "<br>";

	#little code to allow users to sort planets asc, desc in a number of criteria
	if(isset($sort_planets)){
		if($sorted==1){
			$going = "asc";
			$sorted=2;
		} else {
			$going = "desc";
			$sorted=1;
		}
		db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where clan_id = $user[clan_id] and location != 1 order by '$sort_planets' $going");
	} else {
		db("select login_name,planet_name,location,fighters,colon,cash,metal,fuel,elect,organ from ${db_name}_planets where clan_id = $user[clan_id] and location != 1 order by login_name asc, fighters desc, planet_name asc");
	}

	$clan_planet = dbr(1);
	if($clan_planet) {
		$error_str .= make_table(array("<a href=$filename?sort_planets=login_name&sorted=$sorted>Planet Owner</a>","<a href=$filename?sort_planets=planet_name&sorted=$sorted>Planet Name</a>","<a href=$filename?sort_planets=location&sorted=$sorted>Location</a>","<a href=$filename?sort_planets=fighters&sorted=$sorted>Fighters</a>","<a href=$filename?sort_planets=colon&sorted=$sorted>Colonists</a>","<a href=$filename?sort_planets=cash&sorted=$sorted>Cash</a>","<a href=$filename?sort_planets=metal&sorted=$sorted>Metal</a>","<a href=$filename?sort_planets=fuel&sorted=$sorted>Fuel</a>","<a href=$filename?sort_planets=elect&sorted=$sorted>Electronics</a>","<a href=$filename?sort_planets=organ&sorted=$sorted>Organics</a>"));
		while($clan_planet) {
			$clan_planet['login_name'] = "<b class=b1>$clan_planet[login_name]</b>";
			$error_str .= make_row($clan_planet);
			$clan_planet = dbr(1);
		}
		$error_str .= "</table><br>";
	}



	/*************
	* List Clan Ships
	**************/

	#show all ships, not just other clan members.
	if($user_options['show_clan_ships'] || isset($show_clan_ships)){

		#determine if users want to see the abbreviation or not of ship types..
		if($user_options['show_abbr_ship_class'] == 1){ #abbriviate class names
			$class_temp_var = "class_name_abbr";
		} else {
			$class_temp_var = "class_name";
		}

		$error_str .= "<p><a href=clan.php>Show Summary of Clan Ships</a></p>\n";
		#little to allow users to list the ships by different means, even asc and desc.
		if(isset($sort_ships)){
			if($sorted_ships==1){
				$going = "asc";
				$sorted_ships=2;
			} else {
				$going = "desc";
				$sorted_ships=1;
			}
			db("select login_name,ship_name,$class_temp_var,location,fighters,shields,ship_id from ${db_name}_ships where clan_id = '$user[clan_id]' order by '$sort_ships' $going");
		} else {
			db("select login_name,ship_name,$class_temp_var,location,fighters,shields,ship_id from ${db_name}_ships where clan_id = '$user[clan_id]' order by login_name asc, fighters desc, ship_name asc");
			$sorted_ships = 1;
		}
		$clan_ship = dbr(1);
		$clan_page_tab = array("<a href=$filename?sort_ships=login_name&sorted_ships=$sorted_ships&show_clan_ships=1>Ship Owner</a>","<a href=$filename?sort_ships=ship_name&sorted_ships=$sorted_ships&show_clan_ships=1>Ship Name</a>","<a href=$filename?sort_ships=$class_temp_var&sorted_ships=$sorted_ships&show_clan_ships=1>Ship Class</a>","<a href=$filename?sort_ships=location&sorted_ships=$sorted_ships&show_clan_ships=1>Location</a>","<a href=$filename?sort_ships=fighters&sorted_ships=$sorted_ships&show_clan_ships=1>Fighters</a>","<a href=$filename?sort_ships=shields&sorted_ships=$sorted_ships&show_clan_ships=1>Shields</a>");

		$error_str .= make_table($clan_page_tab);
		while($clan_ship) {
			unset($clan_ship['ship_id']);
			$clan_ship['login_name'] = "<b class=b1>$clan_ship[login_name]</b>";
			$error_str .= make_row($clan_ship);
			$clan_ship = dbr(1);
		}
		$error_str .= "</table><p>";



	/*************
	* Summary of Clan ships
	**************/
	} else {
		db("select count(ship_id) as total, sum(fighters) as fighters, login_name from ${db_name}_ships where clan_id = $user[clan_id] group by login_id order by login_name, fighters desc, ship_name desc");
		$clan_ship = dbr(1);

		$error_str .= "<br><br><a href=clan.php?show_clan_ships=1>Show All Clan Ships</a><p>";

		while($clan_ship){
			$error_str .= "<b class=b1>$clan_ship[login_name]</b> has <b>$clan_ship[total]</b> Ship(s) w/ <b>$clan_ship[fighters]</b> Total Fighters<br>";
			$clan_ship = dbr(1);
		}
		$error_str .= "<br><br>";
	}

	$error_str .= "<a href=clan.php?ranking=1>Clan Rankings</a>";
	$error_str .= "<br><a href=clan.php?clan_info=1&target=$user[clan_id]>Clan Information</a><br><br>";

	if($user['login_id'] == $clan['leader_id'] || $user['login_id'] == ADMIN_ID) {
		$error_str .= "<a href=clan.php?changepass=1>Change Clan Password</a><br>";

		if($clan['members'] >1) {
			$error_str .= "<a href=clan.php?lead_change=1>Change Clan Leader</a><br>";
			$error_str .= "<a href=message.php?target=-2&clan_id=$user[clan_id]>Message Clan</a><br>";
		}
		if($user['login_id'] ==1 && $user['login_id'] != $clan['leader_id']) {
			$error_str .= "<a href=clan.php?leave=1>Leave Clan</a><br>";
		}
		$error_str .= "<a href=clan.php?disband=1>Disband Clan</a><br>";
	} else {
		if($clan['members'] >1) {
			$error_str .= "<a href=message.php?target=-2&clan_id=$user[clan_id]>Message Clan</a><br>";
		}
		if($user['login_id'] ==1 || $user['login_id'] != $clan['leader_id']) {
			$error_str .= "<a href=clan.php?leave=1>Leave Clan</a><br>";
		}
	}


}

print_page("Clan",$error_str,"?clans=1");
?>
