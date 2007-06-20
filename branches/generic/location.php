<?php

require_once('inc/user.inc.php');

get_star();
if (!$star) {
	print_page('Error', 'This star-system does not exist, please regenerate the universe.');
}

function print_link($link_num)
{
	global $error_str;
	if($link_num) {
		$error_str .= "&lt;<a href=\"location.php?toloc=$link_num\">$link_num</a>&gt; ";
	}
}

function isLinked($star, $linkNo)
{
	switch ($linkNo) {
		case $star['link_1']:
		case $star['link_2']:
		case $star['link_3']:
		case $star['link_4']:
		case $star['link_5']:
		case $star['link_6']:
		case $star['wormhole']:
			return 0;
	}

	return 1;
}



$header = "Star System";
$auto_str = "";
$error_str = "";
$user_loc_message = "";

// retire
if(isset($retire)) {
	if($sure != 'yes') {
		get_var('Retire','location.php','<p><b class=b1>Warning!</b> This will permanently remove your account from this game. <br>Are you sure you want to retire?','sure','yes');
	} else {
	if ($user[clan_id] > 0) {
		db("select leader_id,members from ${db_name}_clans where clan_id = $user[clan_id]");
		$clan = dbr();
		if($clan[members] > 1 && $user[login_id] == $clan[leader_id] && !$what_to_do){
			$new_page = "Before you retire you must first select whether you want your clan to be disbanded, or assign a new leader to it:";
			$new_page .= "<form action=location.php method=POST name=retiring>";
			while (list($var, $value) = each($_POST)) {
				$new_page .= "<input type=hidden name=$var value='$value'>";
			}
			$new_page .= "<p>Disband Clan <INPUT type=radio name=what_to_do value=1 CHECKED> / Assign New Clan Leader<INPUT type=radio name=what_to_do value=2><p><INPUT type=submit value='Submit'></form>";
			print_page("Retiring",$new_page);
		} elseif($clan[members] < 2 || $what_to_do == 1){

			dbn("update ${db_name}_users set clan_id = 0 where clan_id = $user[clan_id]");
			dbn("update ${db_name}_planets set clan_id = -1 where clan_id = $user[clan_id]");
			dbn("delete from ${db_name}_clans where clan_id = $user[clan_id]");
			dbn("delete from ${db_name}_messages where clan_id = $user[clan_id]");
		} elseif($what_to_do == 2 && !$leader_id){
			$new_page = "Please select which of the below you would like to be the new clan leader:";
			$new_page .= "<form action=location.php method=POST name=retiring2>";
			#$new_page .= "<input type=hidden name=what_to_do value='$what_to_do'>";
			db2("select login_id,login_name from ${db_name}_users where clan_id = '$user[clan_id]' && login_id != '$user[login_id]'");
			$new_page .= "<select name=leader_id>";
			while ($member_name = dbr2()) {
				$new_page .= "<option value=$member_name[login_id]>$member_name[login_name]</option>";
			}
			$new_page .= "</select>";
			while (list($var, $value) = each($_POST)) {
				$new_page .= "<input type=hidden name=$var value='$value'>";
			}
			$new_page .= "<p><INPUT type=submit value='Submit'></form>";
			print_page("Assign New Clan Leader",$new_page);
		} else{
				//dbn("update ${db_name}_clans set leader_id = $leader_id where clan_id = $user[clan_id]");
		}
	}

	retire_user($user['login_id']);
	$rs = "<p><a href=game_listing.php>Go to Game List</a>";
	print_header("Account Removed");
	insert_history($user['login_id'], "Retired From Game");
	echo "You have been removed from the Game.";
	print_footer();
	exit();
	}
}

// check for being dead
if (($user_ship === NULL || $user['ship_id'] === NULL) &&
     $user['login_id'] != ADMIN_ID) {
	pageStart();

	$diedAt = date("M d - H:s", $user['last_attack']);
	print <<<END
<p>Your ship was destroyed by <b class=b1>$user[last_attack_by]</b> at <b>$diedAt</b></p>
<p><a href=earth.php>Buy a ship to continue playing</a></p>

END;

//	if($sudden_death) {
#		echo "<p>You are out of the game permanently.";
/*	} else {
	if($user[last_attack] > (time() - ($hours_after_death * 3600))) {
		echo "<p>You are out of the game for <b>$hours_after_death</b> hours.";
	} else {
		give_cash(3000);
		mysql_db_query($database,"update ${db_name}_users set ship_id = 2 where login_id = $user[login_id]") or die("Failure in mysql_db_query function: ".mysql_error());
		echo "<p>Name your new ship.";
		echo "<form action=ship_build.php method=POST>";
		echo "<input type=hidden name=ship_type value=$start_ship>";
		echo "<input name=ship_name> ";
		echo "<input type=submit name=Submit></form>";
	}
	}*/
	pageStop('End of the road');
	exit();
}

sudden_death_check($user);

// Check for on_planet
if($user['on_planet'] != 0) {
	dbn("update ${db_name}_users set on_planet = 0 where login_id = $user[login_id]");
	$user['on_planet'] = 0;
}

//Tow all
if(isset($towall)) {
	if($towall == 1) {
		dbn("update ${db_name}_ships set towed_by = '$user[ship_id]' where location = '$user[location]' && login_id = '$user[login_id]' && ship_id != '$user[ship_id]'");
	$error_str .= "Ships in Tow";
	}
	elseif($towall == 2) {
	dbn("update ${db_name}_ships set towed_by = '0' where location = '$user[location]' && login_id = '$user[login_id]'");
	$error_str .= "Ships Released";
	}
	else {
		$error_str = "Unable to Tow Ships.";
	}
}

if(isset($tow_release)){
	if(!$do_ship && !$do_ship_type){
			$error_str .= "No Ships Selected.<p>";
	} elseif($tow_release == 1){
		$q_m = 0;
		if(isset($do_ship_type)) {
			foreach($do_ship_type as $var) {
				dbn("update ${db_name}_ships set towed_by = '$user[ship_id]' where shipclass = '$var' && login_id = '$user[login_id]' && location='$user[location]'");
				$q_m += mysql_affected_rows();
			}
		} else {
			foreach($do_ship as $var) {
				dbn("update ${db_name}_ships set towed_by = '$user[ship_id]' where ship_id = '$var' && login_id = '$user[login_id]' && location='$user[location]'");
				$q_m++;
			}
		}
		$error_str .= "<b>$q_m</b> Ship(s) Added to Tow Group.<p>";
	} else {
		$q_m = 0;
		if(isset($do_ship_type)) {
			foreach($do_ship_type as $var) {
				dbn("update ${db_name}_ships set towed_by = '0' where shipclass = '$var' && login_id = '$user[login_id]' && location='$user[location]'");
				$q_m += mysql_affected_rows();
			}
		} else {
			foreach($do_ship as $var) {
				dbn("update ${db_name}_ships set towed_by = '0' where ship_id = '$var' && login_id = '$user[login_id]' && location='$user[location]'");
				$q_m++;
			}
		}
		$error_str .= "<b>$q_m</b> Ship(s) Removed from Group.<p>";
	}
}


// command a different ship
if(!empty($command)) {
	if($command==0 || $command==1){
		print_page("Error","It is not possible to command this ship.");
	}
	db("select * from ${db_name}_ships where ship_id = $command");
	$temp_ship = dbr(1);
	if($temp_ship['login_id'] == $user['login_id']) {
		if($temp_ship['location'] != $user['location']) {
			$dist = get_star_dist($user['location'],$temp_ship['location']);
			if($dist < 12) {
				if ($dist > $user['turns']) {
						print_page("Command Failed","You are not able to take command of this ship remotely, as it would require <b>$dist</b> turns and you only have <b>$user[turns]</b><p>");
				} else {
						charge_turns(2);
					$header = "Remote Command";
					$error_str .= "Command Transfered. Cost = <b>2</b> turns<p>";
				}
			} else {
				$dist = $dist -10;
				if ($dist > $user['turns']) {
					print_page("Command Failed","You are not able to take command of this ship remotely, as it would require <b>$dist</b> turns and you only have <b>$user[turns]</b><p>");
				} else {
					$header = "Remote Command";
					charge_turns($dist);
					$error_str .= "Command Transfered. Cost = <b>$dist</b> turns<p>";
				}
			}
		} else {
			$error_str .= "Command Transfered.<p>";
		}
		dbn("update ${db_name}_users set ship_id = '$command', location = '$temp_ship[location]' where login_id = $user[login_id]");
		$user['ship_id'] = $command;
	$user['location'] = $temp_ship[location];
	get_star();

		$user_ship = $temp_ship;
		empty_bays($user_ship);
	}
}

//Transwarp Burst
if(!empty($transburst)) {
	if(!ereg("tw",$user_ship['config'])) {
		print_page("Transwarp","Your ship is not equipped with a Transwarp Drive.");
	} elseif($user['turns'] < 15) {
		print_page("Transwarp","You need <b>15</b> turns to use Transwarp Burst");
	} elseif(!isset($sure)) {
		get_var('Transwarp Burst','location.php',"Are you sure you want to engage the <b class=b1>Transwarp Burst</b>? <br>This will cost <b>15</b> turns and send you and any towed ships to a random location.",'sure','yes');
	}

	$new_loc = random_system_num(); #make a random system number up.
	dbn("update ${db_name}_users set location = $new_loc where login_id = $user[login_id]");
	dbn("update ${db_name}_ships set location = $new_loc,mine_mode = 0 where ship_id = $user[ship_id]");

	if($user_ship['ship_id']) {
		dbn("update ${db_name}_ships set location = '$new_loc',mine_mode = 0 where towed_by = '$user_ship[ship_id]' and location = '$user_ship[location]'");
	}

	$error_str .= "You and all towed ships have ended up in system <b class=b1>#$new_loc</b>";
	$user['location'] = $new_loc;
	$user_ship['location'] = $new_loc;
	get_star();
	charge_turns(15);
	if ($star['event_random'] > 0 && $user['login_id'] != ADMIN_ID && $user['ship_id'] != NULL) {
			require("random_event_funcs.php");
			random_event_checker($star,$user,$autowarp);
	}
}

// Transwarp
if(!empty($transwarp)) {
#	$tw_distance = 15;
	#max distance jumped is based on the size of the universe.
	$tw_distance = round($uv_universe_size / 35);
	db("select count(star_id) from ${db_name}_stars");
	$num_ss1 = dbr();
	$num_ss = $num_ss1[0];

	db("select count(ship_id) from ${db_name}_ships where towed_by = '$user_ship[ship_id]' and location = '$user_ship[location]'");
		$temp545 = dbr();
	$ship_count = $temp545[0];
	if($ship_warp_cost > 1){
		$mathstuff2 = round((get_star_dist($user['location'],$transwarp) + $ship_count) / 4) + 1;
		$mathstuff = round((($ship_warp_cost / 3) +1) * $mathstuff2);
	} else {
		$mathstuff = round((get_star_dist($user['location'],$transwarp) + $ship_count) / 4) + 1;
	}

	if(!ereg("tw",$user_ship['config'])) {
		print_page("Transwarp","Your ship is not equipped with a transwarp drive.");
	} elseif($transwarp == $user['location']) {
		$user_loc_message = "You're already there!";
	} elseif(($transwarp == "") || (!eregi("[0-9]{1,3}",$transwarp)) || ($transwarp > $num_ss)) {
		print_page("Transwarp","Invalid transwarp destination.");
	} elseif(get_star_dist($user['location'],$transwarp) > $tw_distance) {
		print_page("Transwarp","Your Transwarp drive cannot warp that far. Maximum Transwarp distance of $tw_distance Light Years.");
	} elseif($user['turns'] < $mathstuff) {
		print_page("Transwarp","You need <b>$mathstuff</b> turns to warp that far.");
	} elseif($transwarp != $user[location]) {
		//$mathstuff1 = get_star_dist($user[location],$transwarp);
		dbn("update ${db_name}_users set location = $transwarp where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set location = $transwarp,mine_mode = 0 where ship_id = $user[ship_id]");

		if($user_ship[ship_id]) {
			dbn("update ${db_name}_ships set location = '$transwarp',mine_mode = 0 where towed_by = '$user_ship[ship_id]' and location = '$user_ship[location]'");

			charge_turns($mathstuff);
			$user_ship['mine_mode'] = 0;
			$user_ship['location'] = $transwarp;
			$user['location'] = $transwarp;
			get_star();

			//random event stuff
			if ($star['event_random'] > 0 && $user['login_id'] != ADMIN_ID && $user['ship_id'] != NULL) {
				require("random_event_funcs.php");
				random_event_checker($star,$user,$autowarp);
			}
		}
	}
}

//subspace jump
if(!empty($subspace)) {
	db("select count(ship_id) from ${db_name}_ships where towed_by = '$user_ship[ship_id]' && location = '$user_ship[location]' && ship_id != '$user[ship_id]' && login_id = '$user[login_id]'");
	$num_towed1 = dbr();
	$num_towed = $num_towed1[0];

	db("select count(star_id) from ${db_name}_stars");
	$num_ss = dbr();
	$turn = get_star_dist($user[location],$subspace)/2 +1;
	if(!ereg("sj",$user_ship['config'])) {
		print_page("Sub-Space","This does not have a Sub-Space Jump Drive.");
	} elseif($subspace == $user[location]) {
		$error_str = "You're already there!";
	} elseif($user[turns] < $turn) {
		print_page("Sub-Space","You need <b>$turn</b> turns to get that far");
	} elseif($num_towed > 10 && !ereg("ws",$user_ship['config'])) {
		print_page("Sub-Space","You can only tow <b>10 </b>ships through subspace, you are towing <b>$num_towed</b>.<br><br>To have unlimited tow capability, purchase and install the <b class=b1>Wormhole Stabiliser</b> Upgrade.");
	} elseif($subspace > $num_ss[0] || $subspace <= 0) {
		print_page("Sub-Space","Where are you trying to go? That location doesn't excist. You can only go from systems <b>1</b> to <b>$num_ss[0] </b>using any form of transport.");
	} else {
		dbn("update ${db_name}_users set location = $subspace where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set location = $subspace,mine_mode = 0 where ship_id = $user[ship_id]");

		$user_ship[mine_mode] = 0;
		if($user_ship[ship_id]) {
			dbn("update ${db_name}_ships set location = '$subspace',mine_mode = 0 where towed_by = '$user_ship[ship_id]' and location = '$user_ship[location]'");
		}

		charge_turns($turn);
		dbn("update ${db_name}_users set location = $subspace where login_id = $user[login_id]");
		dbn("update ${db_name}_ships set location = $subspace,mine_mode = 0 where ship_id = $user[ship_id]");

		$user['location'] = $subspace;
		$user_ship['location'] = $subspace;
		get_star();

		//random event stuff
		if ($star['event_random'] > 0 && $user['login_id'] != ADMIN_ID && $user['ship_id'] != NULL) {
			require("random_event_funcs.php");
			random_event_checker($star,$user,$autowarp);
		}
	}
}

// Process page location command if given
if(isset($toloc)) {
	$toloc = (int)$toloc;
	// checks
	if($ship_warp_cost < 0){ #warp cost is determined by largest ship in fleet.
		if($user['ship_id'] == NULL){ //ship destroyed warp cost in turns
			$warp_cost = 1;
		} else {
			db("select move_turn_cost from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (towed_by = '$user[ship_id]' || ship_id = '$user[ship_id]') order by move_turn_cost desc limit 1");
			$move_turn_cost_fleet = dbr();
			$warp_cost = $move_turn_cost_fleet['move_turn_cost']; #set it to warp_cost so can keep generic
		}
	} else {#warp cost is set by admin
		$warp_cost = $ship_warp_cost; #set to warp_cost so as to keep generic
	}
	if($user['turns'] < $ship_warp_cost && $ship_warp_cost > 0 && $user['login_id'] != ADMIN_ID) {
		$error_str = "Sorry, you can't move because you have less than <b>$ship_warp_cost</b> turn(s). <br>This is the present turn cost to move between systems, as set by the <b class=b1>Admin</b>.<p>";
	} elseif($ship_warp_cost < 0 && $user['turns'] < $warp_cost && $user['login_id'] != ADMIN_ID) {
		$error_str = "Sorry, you can't more because you have less than <b>$warp_cost</b> turn(s).<br>This is the amount of turns required to move the largest ship in your present fleet.<br>Differernt ships use different amounts of turns to move between systems. See the help for more information.";
	} else {
		if($toloc == $star['wormhole']) {
			$query_temp_3 = attack_planet_check($db_name,$user);
			db2($query_temp_3);
			$hostile_sys = dbr2(1);
		}
		if(!empty($hostile_sys)){
			$error_str .= "It is not possible to get to the wormhole to jump to that system, because the hostile fighters in this system get in the way.";
		} elseif($toloc < 1) {
			$error_str = "That system does not excist.<p>";
		} elseif($toloc == $user['location']) {
			$error_str = "You are already there.<p>";
		} elseif(isLinked($star, $toloc)) {
			$error_str = "This star system does not have a link to (#<b>$toloc</b>).<p>";
		} else {
			charge_turns($warp_cost);
			dbn("update ${db_name}_users set location = '$toloc' where login_id = '$user[login_id]'");

			if($user['ship_id'] != NULL){
				if(eregi("br",$user_ship['config'])) {
					$space = empty_bays();
					$collected = mt_rand(1,3);
					if($collected >= $space) {
						$collected = $space;
					}
					dbn("update ${db_name}_ships set fuel = fuel + $collected where ship_id = $user[ship_id]");
					$user_ship['fuel'] += $collected;
				}

				#simpler & quicker version of ramscooping.
				#ramscooping using the mammoth ramjet
				$temp056 = mt_rand(1,3);
				dbn("update ${db_name}_ships set fuel = fuel + '$temp056' where shipclass = 301 && (ship_id = '$user_ship[ship_id]' || (towed_by = '$user_ship[ship_id]' && location = '$user[location]' && (cargo_bays - metal-fuel-elect-organ-colon) > $temp056))");
				if($user_ship['shipclass'] == 301 && $temp056 + $user_ship['empty_bays'] <= $user_ship['cargo_bays']){
					$user_ship['fuel'] += $temp056;
					$user_ship['empty_bays'] -= $temp056;
				}

				#ramscooping using the asteroid processor.
				$temp056 = mt_rand(1,3);
				dbn("update ${db_name}_ships set metal = metal + '$temp056' where shipclass = 302 && (ship_id = '$user_ship[ship_id]' || (towed_by = '$user_ship[ship_id]' && location = '$user[location]' && (cargo_bays - metal-fuel-elect-organ-colon) > $temp056))");
				if($user_ship['shipclass'] == 302 && $temp056 + $user_ship['empty_bays'] <= $user_ship['cargo_bays']){
					$user_ship['metal'] += $temp056;
					$user_ship['empty_bays'] -= $temp056;
				}
			}

			if ($user['ship_id'] !== NULL) {
				dbn("update ${db_name}_ships set location = $toloc, mine_mode = 0 where ship_id = '$user[ship_id]'");
				dbn("update ${db_name}_ships set location = $toloc, mine_mode = 0 where towed_by = '$user_ship[ship_id]' && location = '$user[location]'");
				$user_ship['mine_mode'] = 0;
				$user_ship['location'] = $toloc;
			}
			$user['location'] = $toloc;

			get_star();

			$user['location'] = $toloc;

			//random event stuff
		if ($star['event_random'] > 0 && $user['login_id'] != ADMIN_ID && $user['ship_id'] != NULL) {
				require("random_event_funcs.php");
				random_event_checker($star,$user,$autowarp);
			}
		}
	}
}


#random event stuff:
if ($star['event_random'] > 0) {
if ($star['event_random'] == 2) {
	$random_str = "<font color=#00aaaa><center><p>This is a <b class=b1>Nebula</b> system. <a href=help.php?random=1 target=_blank>(help)</a>";
	$random_str .= "<p><b class=b1>Warning! Warning! </b>Any ship left in this system will take damage. <b class=b1>Warning! Warning!</center></b><p></font>";
	$header = "Nebula";
} elseif($star['event_random'] == 4) {
	$random_str = "<center><p>This system has a recently discovered <b class=b1>metal rich deposit </b>in it.";
	$random_str .= "<p>Mining of <b>metal </b>in this system is <b class=b1>quadrupled</b> (*4).</center>";
	$header = "Metal rush";
} elseif($star['event_random'] == 5) {
	$random_str = "<center><p>This system is going to go <b class=b1>SuperNova </b>some time soon.";
	$random_str .= "<p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>Staying in this system could prove disastrous. When the star in this system explodes, <b class=b1> EVERYTHING</b> in the system will be destroyed.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "SuperNova";
} elseif($star['event_random'] == 6) {
	$random_str = "<center><p>This system has recently gone SuperNova. The Supernova remnant is very rich in minerals.";
	$random_str .= "<p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>Mining of <b class=b1>Metal and Fuel </b>in this system is much faster. <b class=b1>However</b>, this system <b class=b1>may</b> shortly turn into a <b class=b1>black hole</b>. There's no saying if/when though. <br>Mine at your own risk.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "SuperNova Remnant";
} elseif($star['event_random'] == 14) {
	$random_str = "<center><p>This system has gone SuperNova. The Supernova Remnant is very rich in minerals.";
	$random_str .= "<br>The <font color=lime>- - - Science Institute of Sol - - -</font> have deemed this system safe, and say it will <b class=b1>not</b> turn into a BlackHole.</center>";
	$header = "Safe SuperNova Remnant";
} elseif($star['event_random'] == 10) {
	$random_str .= "<center><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>This is going to go SuperNova within <b class=b1>48</b> hours. <br>This is an artificially created SuperNova.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "Artificial SuperNova";
} elseif($star['event_random'] == 11) {
	$random_str .= "<center><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>This is going to go SuperNova within <b class=b1>24</b> hours. <br>This is an artificially created SuperNova.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "Artificial SuperNova";
} elseif($star['event_random'] == 12) {
	$random_str .= "<center><p><b class=b1>Warning! Warning! </b>";
	$random_str .= "<br>There is increased Solar Activity in this System, creating a Solar Storm.<br>This means that all shields on all ships are reduced to zero for the duration of the storm.<br>";
	$random_str .= "<b class=b1>Warning! Warning!</center></b><p>";
	$header = "Solar Storm";
}
}


# Attack Planets:
if ($user['login_id'] != ADMIN_ID) {
	$query_temp = attack_planet_check($db_name,$user);
	db2($query_temp);
	while($planet2 = dbr2()) {
		$error_str .= "<p align=\"center\">This system is guarded by the <b>$planet2[fighters]</b> fighters of <b class=b1>$planet2[planet_name]</b>.</p>";

		if($planet2['pass'] != '0') {
			$error_str .= "<a href=planet.php?planet_id=$planet2[planet_id]&want_access=1>Request Access</a>, ";
		}

		if($flag_planet_attack){
			if(ereg("sv",$user_ship['config'])) {
				$error_str .= "<a href=attack.php?quark=1&planet_num=$planet2[planet_id]>Fire Quark Displacer</a>, ";
			} elseif(ereg("sw",$user_ship['config']) && $enable_superweapons == 1) {
				$error_str .= "<a href=attack.php?terra=1&planet_num=$planet2[planet_id]>Fire Terra Maelstrom</a>, ";
			}
			$error_str .= "<a href=planet.php?planet_id=$planet2[planet_id]&attack_planet=1>Attack</a> or Run Away: ";
		} else {
			$error_str .= "Run Away: ";
		}

		if($user['sn_effect'] == 1) {
			$error_str .= "<p><a href=\"bombs.php?sn_effect=1\">Use SuperNova Effector</a><p>";
		}

		print_link($star['link_1']);
		print_link($star['link_2']);
		print_link($star['link_3']);
		print_link($star['link_4']);
		print_link($star['link_5']);
		print_link($star['link_6']);

		if($autowarp) {
			$path_str = str_replace("+", " ", $autowarp);
			$autowarp_path = array();
			$autowarp_path = explode(" ", $path_str);
			//$error_str .= "<br>Path_str is $path_str";
			$next_sector = array_shift($autowarp_path);
			//$error_str .= " Next Sector is $next_sector";
			if($next_sector && ($next_sector == $star[link_1] || $next_sector == $star[link_2] || $next_sector == $star[link_3] || $next_sector == $star[link_4] || $next_sector == $star[link_5] || $next_sector == $star[link_6])) {
				$temp328 = implode($autowarp_path, "\x2B");
				if($temp328){
					$error_str .= "<br>AutoWarp to Next System: &lt;<a href=location.php?toloc=$next_sector&autowarp=$temp328>$next_sector</a>&gt;";
				} else {
					$error_str .= "<br>AutoWarp to Next System: &lt;<a href=location.php?toloc=$next_sector>$next_sector</a>&gt;";
				}
			}
		}

		$error_str .= "</center>";
		$error_str .= $random_str;
		$rs = "";
		print_page("Hostile Planet",$error_str);
	}
}


#Normal system:
if(isset($mine)) {
	$tempx9x = 1;
	db("select fighter_set,login_id,clan_id from ${db_name}_planets where location = '$user[location]'");
	while ($planets = dbr()) {
		if ($planets['fighter_set'] && ($user['login_id'] == $planets['login_id'] || ($user['clan_id'] == $planets['clan_id'] && $user['clan_id'] > 0)) || ereg("ps",$user_ship['config'])) {
			$tempx9x = 1;
		} elseif (!$planets['fighter_set']) {
			$tempx9x = 1;
		} else {
			$tempx9x = 0;
		}
	}

	if ($tempx9x != 1) {
			$error_str .= "It is not possible to mine in a system where the fighters are set to Hostile.<p>";
	} else {
		if($mine == 1 && $user_ship[mine_rate_metal] < 1 && $alternate_play_1 == 1){
			$error_str .= "This ship cannot mine metal.";
			$user_ship[mine_mode] = 0;
		} elseif($mine == 0 && $user_ship[mine_rate_fuel] < 1 && $alternate_play_1 == 1) {
			$error_str .= "This ship cannot mine fuel.";
			$user_ship[mine_mode] = 0;
		} elseif($user_ship[mine_rate_metal] < 1 && $user_ship[mine_rate_fuel] < 1 && $alternate_plat_1 == 0){
			$error_str .= "This ship has no mining ability.";
			$user_ship[mine_mode] = 0;
			} else {
			dbn("update ${db_name}_ships set mine_mode = '$mine' where ship_id = '$user[ship_id]'");
			$user_ship[mine_mode] = $mine;
			$error_str .= "Ship Mining";
			}
	}
}

if(isset($mine_all)) {
	$tempx9x = 1;
	db("select fighter_set,login_id,clan_id from ${db_name}_planets where location = '$user[location]'");
	while ($planets = dbr()) {
		if ($planets['fighter_set'] && ($user['login_id'] == $planets['login_id'] || ($user['clan_id'] == $planets['clan_id'] && $user['clan_id'] > 0))) {
			$tempx9x = 1;
		} elseif (!$planets['fighter_set']) {
			$tempx9x = 1;
		} else {
			$tempx9x = 0;
		}
	}

	if ($tempx9x != 1) {
			$error_str .= "It is not possible to mine in a system where the fighters are set to hostile.<p>";
	} else {
		if($alternate_play_1 == 1){ #alternate mining
			if($mine_all == 1){#metal
				dbn("update ${db_name}_ships set mine_mode = '$mine_all' where mine_rate_metal > 0 && (ship_id = $user[ship_id] || (login_id = '$user[login_id]' && location = '$user[location]'))");
			} else {#fuel
				dbn("update ${db_name}_ships set mine_mode = '$mine_all' where mine_rate_fuel > 0 && (ship_id = $user[ship_id] || (login_name = '$user[login_name]' && location = '$user[location]'))");
			}
		} else { #normal mining
			dbn("update ${db_name}_ships set mine_mode = '$mine_all' where (mine_rate_metal > 0 || mine_rate_fuel > 0) && (ship_id = $user[ship_id] || (login_id = '$user[login_id]' && location = '$user[location]'))");
		}
		#mass mining
		if((($user_ship[mine_rate_metal] > 0 || $user_ship[mine_rate_fuel] > 0) && $mine_all && $alternate_play_1==0) || ($user_ship[mine_rate_metal] > 0 && $mine_all == 1 && $alternate_play_1==1) ||($user_ship[mine_rate_fuel] > 0 && $mine_all == 2 && $alternate_play_1==1)){
			$user_ship[mine_mode] = $mine_all;
			$error_str .= "Fleet Mining";
		} else {
			$error_str .= "This ship has no mining ability, However any ships in this system that you own that can mine, are now mining.";
			$user_ship[mine_mode] = 0;
		}
	}
}

if(isset($tow)) {
	db("select location,login_id,towed_by from ${db_name}_ships where ship_id = '$tow'");
	$towed = dbr();
	if(($towed[location] == $user_ship[location]) && ($towed[login_id] == $user_ship[login_id])) {
		if($towed[towed_by] == $user_ship[ship_id]) {
			dbn("update ${db_name}_ships set towed_by = 0 where ship_id = '$tow' && login_id = '$user[login_id]' && location = '$user[location]'");
		} else {
			dbn("update ${db_name}_ships set towed_by = $user_ship[ship_id] where ship_id = '$tow' && login_id = '$user[login_id]' && location = '$user[location]'");
		}
	} else {
		$error_str .= "Couldn't Tow Ship.<p>";
	}
}

if(isset($tow_group) && isset($class)) {
	if($tow_group == 0) {
		dbn("update ${db_name}_ships set towed_by = 0 where shipclass = '$class' && login_id = '$user[login_id]' && location = '$user[location]'");
	} else {
		dbn("update ${db_name}_ships set towed_by = '$user_ship[ship_id]' where shipclass = '$class' && login_id = '$user[login_id]' && location = '$user[location]'");
	}
	$error_str .= mysql_affected_rows()." Ships' Tow Orders Updated.<p>";
}


if(isset($jettison)) {
	if($sure != 'yes') {
			get_var('Jettison Cargo','location.php','Are you sure you want to Jettison all Cargo in this ship?','sure','yes');
	} else {
		if($user_ship['colon'] > 0){
			$temp = round(rand(0,5));
			if($temp <= 1) {
				$extra_text = "<br>You mercyless blighter. Those poor innocent (dead) colonists. What did they ever do to you?";
				$news_text_extra = "Without provocation, <b class=b1>$user[login_name]</b> jettisoned <b>$user_ship[colon]</b> colonists into open space.<br>Reason being: \"Planetlubbers the lot of em. I\'ve seen scurvy with more guts (and the guts of someone with scurvy)\"";
			} elseif($temp <= 2) {
				$extra_text = "<br>Thats just nasty. Those colonists didn't stand a chance";
				$news_text_extra = "Ghastly. <b class=b1>$user[login_name]</b> just jettisoned <b>$user_ship[colon]</b> colonists into deep space.<br>Explaination being \"Bloomin Unions.\"";
			} elseif($temp <= 3) {
				$extra_text = "<br>What will the relatives of those poor (dead) colonists think of you now?";
				$news_text_extra = "<b>$user_ship[colon]</b> (dead) colonists are now floating around in space courtesy of <b class=b1>$user[login_name]</b>.<br>When asked what happened: \"Aye laddy, it twas \'im *pointing to innocent cabin boy*\"";
			} elseif($temp <= 4) {
				$extra_text = "<br>You gonna go out there are sweep up that mess of (dead) colonists you just	made?<br>Thought not.";
				$news_text_extra = "<b class=b1>$user[login_name]</b> just brutally murdered <b>$user_ship[colon]</b> colonists by jettisoning them into space. <br>The excuse: \"But they were already dead guvn\'r\"";
			} elseif($temp <= 5) {
				$extra_text = "<br>Its a good thing your crew is as heartless as you are. Slaughtering colonists like that indeed.";
				$news_text_extra = "No heart <b class=b1>$user[login_name]</b> just lived up to the name by deciding to (and actually doing it too!) jettison <b>$user_ship[colon]</b> colonists into open space. When interviewed: \"I\'m not heartless. Just coldly calculating.\"";
			}
			post_news($news_text_extra);
		}
		dbn("update ${db_name}_ships set metal=0, fuel=0, elect=0, organ=0, colon=0 where ship_id = $user_ship[ship_id]");
		$user_ship['metal'] = 0;
		$user_ship['fuel'] = 0;
		$user_ship['elect'] = 0;
		$user_ship['organ'] = 0;
		$user_ship['colon'] = 0;
		empty_bays($user_ship);
		$user_loc_message .= "Cargo Jettisoned. \n$extra_text<p>";
	}
}

#ships has been told to defend the fleet
if(isset($defender)){
	dbn("update ${db_name}_ships set defend_fleet = 1 where ship_id = '$defender' && (config REGEXP 'oo' || config REGEXP 'bs') && login_id = '$user[login_id]' && location = '$user[location]'");
	if(mysql_affected_rows() == 1){
		$user_loc_message .= "<p>Ship is now defending the fleet.</p>";
	} else {
		$user_loc_message .= "<p>Unable to comply.</p>";
	}
} elseif(isset($defender_t)) {
	dbn("update ${db_name}_ships set defend_fleet = 0 where ship_id = '$defender_t' && login_id = '$user[login_id]' && location = '$user[location]'");
	if(mysql_affected_rows() == 1){
		$user_loc_message .= "<p>Ship has now resumed normal activities.</p>";
	} else {
		$user_loc_message .= "<p>Unable to comply.</p>";
	}
}

$rs = "";

//this will show any remaining autowarps the player is trying to perform.
if(isset($autowarp)) {
	$auto_str  .= " - <a href=\"autowarp.php\">Set New AutoWarp</a>";

	$path_str = str_replace("+", " ", $autowarp);
	$autowarp_path = array();
	$autowarp_path = explode(" ", $path_str);
	$next_sector = array_shift($autowarp_path);
	$num_aw_left = count($autowarp_path) + 1;
	if($next_sector == $star['link_1'] || $next_sector == $star['link_2'] || $next_sector ==
		$star['link_3'] || $next_sector == $star['link_4'] || $next_sector == $star['link_5'] || $next_sector == $star['link_6']) {
		$autowarp = implode($autowarp_path, "\x2B");
		$sys_to_go = "<b>".preg_replace("/\+/","</b> - <b>",$autowarp)."</b>";
		$auto_link = "";
		if(isset($autowarp)){
			$auto_link .= "&lt;<a href=location.php?toloc=$next_sector&autowarp=$autowarp>$next_sector</a>&gt;";
		} else {
			$auto_link .= "&lt;<a href=location.php?toloc=$next_sector>$next_sector</a>&gt;";
		}
	}
} else {
	$auto_str .= " - <a href=\"autowarp.php\">Set AutoWarp</a>";
}


#determine system metal info
if($star['metal'] > 0) {
	$metal_str = "<b>$star[metal]</b>";
	if ($user['ship_id'] != NULL) {
		if($user_ship['mine_rate_metal'] > 0 || (($user_ship['mine_rate_metal'] > 0 || $user_ship['mine_rate_fuel'] > 0) && $alternate_play_1 == 0)){
			if($user_ship['mine_mode'] == 1) {
			$metal_str .= " - (Currently Mining) - <a href=location.php?mine_all=1>Fleet Mining</a>";
			} else {
			$metal_str .= " - <a href=location.php?mine=1>Mine</a> - <a href=location.php?mine_all=1>Fleet Mining</a>";
			}
		} else {
			$metal_str .= " - <a href=location.php?mine_all=1>Fleet Mining</a>";
		}
	}
}

#determine system fuel info
if($star['fuel'] > 0) {
	$fuel_str = "<b>$star[fuel]</b>";
	if($user['ship_id'] != NULL){
		if($user_ship['mine_rate_fuel'] > 0 || (($user_ship['mine_rate_metal'] > 0 || $user_ship['mine_rate_fuel'] > 0) && $alternate_play_1 == 0)){
			if($user_ship['mine_mode'] == 2) {
			$fuel_str .= " - (Currently Mining) - <a href=location.php?mine_all=2>Fleet Mining</a>";
			} else {
			$fuel_str .= " - <a href=location.php?mine=2>Mine</a> - <a href=location.php?mine_all=2>Fleet Mining</a>";
			}
		} else {
			$fuel_str .= " - <a href=location.php?mine_all=2>Fleet Mining</a>";
		}
	}
}


#use table based method for displaying system info.
if($user_options['system_disp_method'] == 2){

	$error_str .= "<table class=\"simple\">";
	$error_str .= "<tr><th>Star System</th><td>#<b>$user[location]</b> - <b class=b1>$star[star_name]</b>";
	if ($uv_planet_slots_use) {
		$error_str .= " (<b>$star[planetary_slots]</b> Planetary Slots)";
	}
	$error_str .= "</td></tr>";

	#warp links
	$error_str .= "<tr><th>Warp Links</th><td>";

	if($user['location'] > 0 && $user['location'] <= $uv_num_stars){ //ensure user is in a system that exists.
		print_link($star['link_1']);
		print_link($star['link_2']);
		print_link($star['link_3']);
		print_link($star['link_4']);
		print_link($star['link_5']);
		print_link($star['link_6']);
		$error_str .= $auto_str."</td>";

		if(!empty($star['wormhole'])) {
			$error_str .= "<th>Wormhole</th><td><a href=location.php?toloc=$star[wormhole]>$star[wormhole]</a></td>";
		}
	} else { //user not in a system that exists, so make a link to system 1.
		$error_str .= "&lt;<a href=location.php?toloc=1>1</a>&gt; ";
	}

	$error_str .= "<tr>"; #end warp links

	#autowarp
	if(!empty($auto_link)){
		$error_str .= "<tr><th>Autowarp ($num_aw_left)</th><td>$auto_link - $sys_to_go</td></tr>";
	}

	#metal and fuel
	if(!empty($metal_str)){
		$error_str .= "<tr><th>Metals</th><td>".$metal_str."</td></tr>";
	}
	if(!empty($fuel_str)){
		$error_str .= "<tr><th>Fuels</th><td>".$fuel_str."</td></tr>";
	}

	if(!empty($user_loc_message)){
		$error_str .= "<tr><td colspan=\"2\">$user_loc_message</td></tr>";
	}

	if(!empty($random_str)){
		$error_str .= "<tr><td colspan=\"2\">$random_str</td></tr>";
	}


	$error_str .= "</table>";

#use original, normal text method.
} else {
	$error_str .= $user_loc_message;
	$error_str .= "<center><b class=b1>$star[star_name]</b> Star System (<b>#$user[location]</b>)";
	if ($uv_planet_slots_use) {
		$error_str .= "<br><b>$star[planetary_slots]</b> Planetary Slots";
	}

	#warp links
	$error_str .= "<br>Warp: ";
	if($user['location'] > 0 && $user['location'] <= $uv_num_stars){ //ensure user is in a system that exists.
		print_link($star['link_1']);
		print_link($star['link_2']);
		print_link($star['link_3']);
		print_link($star['link_4']);
		print_link($star['link_5']);
		print_link($star['link_6']);
		$error_str .= $auto_str;

		if(isset($auto_link)){
			$error_str .= "<br />AutoWarp to next System $auto_link - <b>$num_aw_left</b> Warp(s) Left";
		}

		if(!empty($star['wormhole'])) {
			$error_str .= "<br />Wormhole to : <a href=location.php?toloc=$star[wormhole]>$star[wormhole]</a>";
		}
	} else { //user not in a system that exists, so make a link to system 1.
		$error_str .= "&lt;<a href=location.php?toloc=1>1</a>&gt; ";
	}


	#metal and fuel
	if(isset($metal_str)){
		$error_str .= "<br>Metals: ".$metal_str;
	}
	if(isset($fuel_str)){
		$error_str .= "<br>Fuels: ".$fuel_str;
	}

	$error_str .= "</center>";

	$error_str .= $random_str;
}


$error_str .= "<p>";

// ports
db("select port_id,metal_bonus from ${db_name}_ports where location = '$user[location]'");
while($port = dbr()) {
	$error_str .= "Starport - <a href=port.php?port_id=$port[port_id]>Dock</a><br>";
}

// blackmarkets
if($flag_research == 1){
	$bm_t[0] = "black_market.php";
	$bm_t[1] = "bm_ships.php";
	$bm_t[2] = "bm_upgrades.php";
	$bm_t[3] = "bm_bombs.php";

	db("select bmrkt_id,bm_name,bmrkt_type from ${db_name}_bmrkt where location = '$user[location]'");
	$bmrkt = dbr();
	if($bmrkt){
		$error_str .= "<br>";
		while($bmrkt) {
			$error_str .= "<b class=b1>$bmrkt[bm_name]'s</b> Blackmarket - <a href=".$bm_t[$bmrkt['bmrkt_type']]."?bmrkt_id=$bmrkt[bmrkt_id]>Contact</a><br>";
			$bmrkt = dbr();
		}
		$error_str .= "<br>";
	}
}

/**********************
* Planet Listings
**********************/
if($user['location'] == 1){ //system 1. Only earth
	$temp_str = "Planet <b class=b1>Earth</b> - <a href=earth.php>Land</a> <br>";

} else {

	db("select * from ${db_name}_planets where location = '$user[location]' order by planet_name asc, fighters desc");
	$planets = dbr(1);

	$temp_str = "";
	$temp2_str = "";

	while($planets) {
		if($planets['login_id'] == $user['login_id']){ #seperate user planets from other planets
			$temp2_str .= "Planet <b class=b1>$planets[planet_name]</b> (w/ <b>$planets[fighters]</b> fighters) - <a href=planet.php?planet_id=$planets[planet_id]>Land</a><br>";
		} else { #other players planets
			$temp_str .= "<br>Planet <b class=b1>$planets[planet_name]</b> ";
			$temp_str .= "(w/ <b>$planets[fighters]</b> fighters)";

			if(($planets['login_id'] == $user['login_id']) ||
			   ($planets['clan_id'] == $user['clan_id'] &&
			   $planets['clan_id'] != 0) || ($user['login_id'] == ADMIN_ID) ||
			   ($planets['fighters'] == 0) || $user['ship_id'] == NULL) {
				$temp_str .= "- <a href=planet.php?planet_id=$planets[planet_id]>Land</a>";
			} else {
				if($flag_planet_attack != 0){
					$temp_str .= "- <a href=planet.php?planet_id=$planets[planet_id]&attack_planet=1>Attack</a>";
					if(ereg("sv",$user_ship['config'])) { //quark disrupter
						$temp_str .= " - <a href=attack.php?quark=1&planet_num=$planets[planet_id]>Fire Quark Displacer</a>";
					} elseif(ereg("sw",$user_ship['config']) && $enable_superweapons == 1) { //terra maelstrom
						$temp_str .= " - <a href=attack.php?terra=1&planet_num=$planets[planet_id]>Fire Terra Maelstrom</a>";
					}
					if($planets['pass'] != '0') {
						$temp_str .= " - <a href=planet.php?planet_id=$planets[planet_id]>Have Pass</a>";
					}
				}
			}
		}
		$planets = dbr(1);
	}//end while
}

if ($user['location'] == 1 && $random_events != 0) {
	$temp_str .= "<b class=b1>Observatory</b> of <b>Sol</b> - <a href=science.php>Visit</a>";
}

#determine if user has any planets in the system
if(!empty($temp2_str)){
	$error_str .= "Your planets:<br>".$temp2_str."<br>";
	if(!empty($temp_str)){
		$error_str .= "Other planets:<br>".$temp_str;
	}
} else {
	$error_str .= $temp_str;
}
$temp_str = "";




/**********************
* Player Ship Listings
**********************/

$error_str .= "<p>";

db("select count(ship_id) from ${db_name}_ships where login_id = '$user[login_id]' && location='$user[location]'");
$count=dbr();
$error_str .= "You have $count[0] ship(s) in this system.<p>";

if($count[0] > 2 && $user_options['tow_method'] == 1){
	$error_str .= "<p><a href='location.php?towall=1'>Tow All</a> - <a href='location.php?towall=2'>Release All</a><p>";
} elseif($count[0] > 1 && $user_options['tow_method'] == 2){
	$error_str .= "<form method=\"post\" action=\"location.php\" name=\"ship_towing\">\n";
	$error_str .= "Tow<INPUT type=radio name=tow_release value=1 CHECKED> / Release<INPUT type=radio name=tow_release value=2>";
	$error_str .= " - <a href=javascript:TickAll(\"ship_towing\")>Invert Ship Selection</a>";
	//$error_str .= " - Select All/None<INPUT NAME=select_all TYPE=checkbox VALUE=\"Select all\" onClick=\"TickAll( \"ship_towing\" )\">";
	$error_str .= " - <input type=\"submit\" value=\"Tow/Release\"><p>";
}


settype($show_user_ships, "integer");

/* HANDLE USER SHIPS */
if ($show_user_ships == 1) {
	$user['show_user_ships'] = 1;
	dbn("update ${db_name}_users set show_user_ships = 1 where login_id = '$user[login_id]'");
} elseif ($show_user_ships == 2) {
	$user['show_user_ships'] = 0;
	dbn("update ${db_name}_users set show_user_ships = 0 where login_id = '$user[login_id]'");
}


/* SHOW FULL LIST OF USER SHIPS */
if($user['show_user_ships'] == 1) {
	db2("select ship_id,ship_name,class_name,class_name_abbr,config,fighters,towed_by,config,defend_fleet from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && ship_id != '$user[ship_id]' && ship_id > 1 order by fighters desc,ship_name asc");
	$ships = dbr2(1);
		if($ships == "") {
		$error_str .= "<I>You're presently in command of your only ship in this system.</I><P>";
		} else {
		$error_str .= "<A HREF='location.php?show_user_ships=2'>Show Summary of Your Ships</A><P>";

		#Loop through all of a players ships in the system.
		while($ships){
			$cloak_str_start = "";
			$cloak_str_end = "";
			$ships['ship_name'] = stripslashes($ships['ship_name']);
			#ship is cloaked.
			if(ereg("ls",$ships['config']) || ereg("hs",$ships['config'])){
				$cloak_str_start = "<b class=cloak>";
				$cloak_str_end = "</b>";
			}

			# IF SHOW ABBRIVIATED SHIP CLASSES IS SET, THEN...
			if($user_options['show_abbr_ship_class'] == 1){ #abbriviate class names
				$error_str .= "$cloak_str_start $ships[ship_name] ($ships[class_name_abbr] w/ <b>$ships[fighters]</b> fighters)".$cloak_str_end;

			# IF SHOW ABBRIVIATED SHIP CLASSES IS NOT SET, THEN...
			} else {
				$error_str .= "$cloak_str_start $ships[ship_name] ($ships[class_name] w/ <b>$ships[fighters]</b> fighters)".$cloak_str_end;
			}

			if($ships['config'] && $user_options['show_config']==1) {
				$error_str .= " - $ships[config]";
			}
			#ship being towed and using original method
			if($ships['towed_by'] == $user_ship['ship_id'] && $user_options['tow_method'] == 1) {
				$error_str .= " - <a href=location.php?tow=$ships[ship_id]>Stop Towing</a>";

			#ship being towed using Mori's method
			}elseif($ships['towed_by'] == $user_ship['ship_id'] && $user_options['tow_method'] == 2) {
				$error_str .= " - (Towing)";

			#no ship being towed by original method
			}elseif($user_options['tow_method'] == 1) {
				$error_str .= " - <a href=location.php?tow=$ships[ship_id]>Tow</a>";
			}

			#show checkbox (for Moris tow method)
			if($count[0] > 1 && $user_options['tow_method'] == 2){
				$error_str .= " - <a href=location.php?command=$ships[ship_id]>Command</a>";
				if(($ships['defend_fleet'] == 0) && (eregi("bs",$ships['config']) || eregi("oo",$ships['config']))) {
					$error_str .= " - <a href=location.php?defender=$ships[ship_id]>Passive</a>";
				} elseif($ships['defend_fleet'] == 1) {
					$error_str .= " - <a href=location.php?defender_t=$ships[ship_id]>Defending</a>";
				}
				$error_str .= " - <input type=checkbox name=do_ship[$ships[ship_id]] value=$ships[ship_id]><br>";
			} else {
				$error_str .= " - <a href=location.php?command=$ships[ship_id]>Command</a>";
				if(($ships['defend_fleet'] == 0) && (eregi("bs",$ships['config']) || eregi("oo",$ships['config']))) {
					$error_str .= " - <a href=location.php?defender=$ships[ship_id]>Passive</a><br>";
				} elseif($ships['defend_fleet'] == 1) {
					$error_str .= " - <a href=location.php?defender_t=$ships[ship_id]>Defending</a><br>";
				} else {
					$error_str .= "<br>";
				}
			}
		$ships = dbr2(1);
		}
	} # end of looping through all of players' ships.


	if($count[0] > 10 && $user_options['tow_method'] == 1){
		$error_str .= "<p><a href='location.php?towall=1'>Tow All</a> - <a href='location.php?towall=2'>Release All</a></p>";
	#end of original method
	} elseif($count[0] > 10 && $user_options['tow_method'] == 2){
		$error_str .= "Tow <input type=radio name=tow_release value=\"1\" checked=\"checked\"> / Release <INPUT type=radio name=tow_release value=2>";
		//$error_str .= " - Select All/None<INPUT NAME=select_all TYPE=checkbox VALUE=\"Select all\" onClick=\"TickAll(ship_towing);\">";
		$error_str .= " - <a href=javascript:TickAll(\"ship_towing\")>Invert Ship Selection</a>";
		$error_str .= " - <input type=submit value='Tow/Release'></form>";
	#end of moris tow method
	} elseif($user_options['tow_method'] == 2 && $count[0] > 1){
		$error_str .= "</form>";
	#otherwise just plain print this
	}

unset($ships);

/* SHOW SUMMARY OF USER SHIPS */
} else {
	db2("select count(ship_id) as total, sum(fighters) as fighters, avg(towed_by) as group_tow,class_name, config,shipclass from ${db_name}_ships where location = $user[location] && ship_id > 1 && login_id = $user[login_id] && ship_id != '$user[ship_id]' group by class_name order by total desc, fighters desc");
	$ships = dbr2(1);
	if(!$ships){
		$error_str .= "<p>You are commanding the only ship you have in this system.</p>";
	} else {
		$error_str .= "<p><a href=\"location.php?show_user_ships=1\">Show all of your ships</a></p>\n";
		$cloaked_ships = 0;
		$cloaked_figs = 0;
		while($ships){
			$error_str .= "<B>$ships[class_name]s</B>: $ships[total] w/ <b>$ships[fighters]</b> Fighters";
			#show config for ships.
			$group_tow_text = "";
			if($ships['total'] > 1){
				$group_tow_text .= " Group";
			}
			if($ships['config'] && $user_options['show_config']==1) {
				$error_str .= " - $ships[config]";
			}
			if($user_options['tow_method'] == 2) {
				if($ships['group_tow'] == $user['ship_id']) {
					$error_str .= " (Towing$group_tow_text)";
				}
				$error_str .= " - <input type=checkbox name=do_ship_type[$ships[shipclass]] value=$ships[shipclass]><br>";
			} else {
				if($ships['group_tow'] == $user['ship_id']) {
					$error_str .= " <a href=location.php?tow_group=0&class=$ships[shipclass]>Stop Towing$group_tow_text</a><br>";
				} else {
					$error_str .= " <a href=location.php?tow_group=1&class=$ships[shipclass]>Tow$group_tow_text</a><br>";
				}
			}
			$ships = dbr2(1);
		}
	if($user_options['tow_method'] == 2 && $count[0] > 1){
		$error_str .= "</form>";
	}
	unset($ships);
	}
}

settype($show_enemy_ships, "integer");
/* HANDLE Enemy SHIPS */
if ($show_enemy_ships == 1) {
	$user['show_enemy_ships'] = 1;
	dbn("update ${db_name}_users set show_enemy_ships = 1 where login_id = '$user[login_id]'");
} elseif ($show_enemy_ships == 2) {
	$user['show_enemy_ships'] = 0;
	dbn("update ${db_name}_users set show_enemy_ships = 0 where login_id = '$user[login_id]'");
}

/* SHOW FULL LIST OF ENEMY SHIPS */
if ($user['show_enemy_ships'] == 1) {
	db2("select s.ship_id, s.ship_name, s.login_id, s.fighters, s.class_name,s.class_name_abbr, s.size, u.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.turns_run from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' and s.ship_id > 1 and s.login_id = u.login_id && s.login_id != '$user[login_id]' order by s.fighters desc,s.login_name,s.ship_name");
	$ships = dbr2(1);

	$can_attack = 1;
	if ($user['login_id'] != ADMIN_ID && (($user['turns_run'] < $turns_before_attack ||
	     $user['ship_id'] == NULL) || ($user['location'] == 1 && $flag_sol_attack == 0) || $flag_space_attack == 0)) {
		$can_attack = 0;
	}

	#there are other ships in the system
	if ($ships) {
		$error_str .= "<p><a href=\"location.php?show_enemy_ships=2\">Give summary of other ships</a></p>\n";

		#loop through other players ships.
		while($ships){
			!isset($ships['config']) ? $ships['config'] = "" : 1;
			#reset cloaked ship info.
			$cloak_str_start = "";
			$cloak_str_end = "";

			#player is able to see only non-cloaked ships, unless conditions are met.
			if((!ereg("ls",$ships['config']) && !ereg("hs",$ships['config'])) || ($ships['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) || (ereg("ls",$ships['config']) && ereg("sc",$user_ship['config'])) || $user['login_id'] == ADMIN_ID){
				$error_str .= print_name($ships);

				#sets some cloak text into a string, if a ship is cloaked.
				if(ereg("ls",$ships['config']) || ereg("hs",$ships['config'])){
					$cloak_str_start = "<b class=cloak>";
					$cloak_str_end = "</b>";
				}

				#non-abbriviated ship class.
				if($user_options['show_abbr_ship_class'] == 1){
					$error_str .= "$cloak_str_start $ships[ship_name] ($ships[class_name_abbr] w/ <b>$ships[fighters]</b> fighters)".$cloak_str_end;
				#abbriviated ship class.
				} else {
					$error_str .= "$cloak_str_start	$ships[ship_name] ($ships[class_name] w/ <b>$ships[fighters]</b> fighters)".$cloak_str_end;
				}
			} elseif(ereg("hs",$ships['config']) && !ereg("sc",$user_ship['config'])) { #hs without scanner
				$error_str .= "<b class=cloak>::::: ".discern_size($ships['size'])." Disturbance Detected:::::</b>";
			} elseif(ereg("hs",$ships['config']) && ereg("sc",$user_ship['config'])) { # hs, with scanner.
				$error_str .= "<b>Unknown Owner</b><b class=cloak> $ships[ship_name] ($ships[class_name] w/ <b>$ships[fighters]</b> fighters)</b>";
			} elseif(ereg("ls",$ships['config']) && !ereg("sc",$user_ship['config'])) { # ls, no scanner.
				$error_str .= "<b class=cloak>:::::Cloaked $ships[class_name] Detected:::::</b>";
			}

			if ($user['login_id'] != ADMIN_ID && (($user['clan_id'] == $ships['clan_id'] && $user['clan_id'] > 0) || ($can_attack == 0 || $ships['turns_run'] < $turns_safe) || ((ereg("ls",$ships['config']) || ereg("hs",$ships['config'])) && !ereg("sc", $user_ship['config'])) || $ships['login_id'] == ADMIN_ID)) {
				$error_str .= "<br>";
			} else {
				$error_str .= " - <a href=attack.php?target=$ships[ship_id]>Attack</a><br>";
			}
			$ships = dbr2(1);

		} #end of loop through other ships
		unset($ships);

	} else {
		$error_str .= "<p><i>There are no other ships in this system.</i></p>\n";
	}

/* SHOW SUMMARY OF ENEMY SHIPS	*/
} else {
	db2("select count(s.ship_id) as total, sum(s.fighters) as fighters, s.login_id, s.login_name, u.clan_id, u.clan_sym, u.clan_sym_color, u.turns_run from ${db_name}_ships s  LEFT JOIN  ${db_name}_users u ON u.login_id = s.login_id WHERE `s`.`location` = '$user[location]' && `s`.`ship_id` > 1 && `s`.`login_id` != '$user[login_id]' group by login_id order by total,s.login_name");
	$ships = dbr2();
	if(!$ships){
		$error_str .= "<p>There are no other ships in this system.</p>\n";
	} else {
		if($user['login_id'] == ADMIN_ID || ($user['turns_run'] < $turns_before_attack || $user['ship_id'] == NULL) || ($user['location'] == 1 && $flag_sol_attack == 0) || $flag_space_attack == 0){
			$can_attack = 0;
		} else {
			$can_attack = 1;
		}
		$error_str .= "<p><a href=\"location.php?show_enemy_ships=1\">Full list of other ships</a></p>\n";
		$cloaked_ships = 0;
		$cloaked_figs = 0;
		while($ships){

			!isset($ships['config']) ? $ships['config'] = "" : 1;
			#show un-cloaked ships
			if(!ereg("ls",$ships['config']) && !ereg("hs",$ships['config'])){
				$error_str .= print_name($ships);
				$error_str .= " has <b>$ships[total]</b> Ship(s) w/ <b>$ships[fighters]</b> Fighters";

				#show attack link next to group of ships.
				if(($user['clan_id'] == $ships['clan_id'] && $user['clan_id'] > 0) || ($can_attack == 0 || $ships['turns_run'] < $turns_safe) || ((ereg("ls",$ships['config']) || ereg("hs",$ships['config'])) && !ereg("sc",$user_ship['config']))){
					$error_str .= "<br>";
				} else {
					db("select ship_id from ${db_name}_ships where login_id = '$ships[login_id]' && location='$user[location]' limit 1");
					$to_attack = dbr(1);
					$error_str .= " - <a href=attack.php?target=$to_attack[ship_id]>Attack</a><br>";
				}

			#show cloaked ships if certain conditions are met.
			} elseif((ereg("ls",$ships['config']) && ereg("sc",$user_ship['config'])) || ($ships['clan_id'] == $user['clan_id'] && $user['clan_id'] > 0) || $user['login_id'] == ADMIN_ID){
				$error_str .= print_name($ships);
				if(ereg("ls",$ships['config'])){
					$error_str .= "<b class=cloak> has <b>$ships[total]</b> Cloaked Ship(s) w/ <b>$ships[fighters]</b> Fighters</b>";
				} else {
					$error_str .= "<b class=cloak> has <b>$ships[total]</b> Highly Cloaked Ship(s) w/ <b>$ships[fighters]</b> Fighters</b>";
				}

				#show attack link next to group of Lightly cloaked ships.
				if(($user['clan_id'] == $ships['clan_id'] && $user['clan_id'] > 0) || ($can_attack == 0 || $ships['turns_run'] < $turns_safe)){
					$error_str .= "<br>";
				} else {
					db("select ship_id from ${db_name}_ships where login_id = '$ships[login_id]' && location='$user[location]' && config REGEXP 'ls' limit 1");
					$to_attack = dbr(1);
					$error_str .= " - <a href=attack.php?target=$to_attack[ship_id]>Attack</a><br>";
				}

			#give only brief details about cloaked ships if none of the requisite conditions are met.
			} else {
				$cloaked_ships += $ships['total'];
				if(ereg("sc",$user_ship['config']) && ereg("hs",$ships['config'])){
					$cloaked_figs += $ships['fighters'];
					db("select ship_id from ${db_name}_ships where login_id = '$ships[login_id]' && location='$user[location]' && config REGEXP 'hs' limit 1");
					$to_attack = dbr(1);
					$cloaked_attack_link = " - <a href=attack.php?target=$to_attack[ship_id]>Attack</a>";
				}
			}
			$ships = dbr2(1);
		} # end of loop of other players ships.

		#cloaked ships the player cannot tell many details about.
		if($cloaked_figs){
			$error_str .= "<b class=cloak><b>$cloaked_ships</b> Cloaked Ship(s) w/ <b>$cloaked_figs</b> Fighters</b>".$cloaked_attack_link."<br>";
		} elseif($cloaked_ships){
			$error_str .= "<b class=cloak><b>$cloaked_ships</b> Cloaked Ship(s)</b><br>";
		}
		unset($ships);
	}
}



$error_str .= $temp_str;

$locBar = "<div id=\"locBar\">\n";

if($user_options['show_minimap']){
	$locBar .= "\t<a href=\"map.php\"><img id=\"miniMap\" src=\"" .
	 esc('img/' . $db_name . '_maps/sm' . $user['location'] . '.png') .
	 "\" alt=\"Map of systems around {$user['location']}\" /></a>\n";
} else {
	$locBar .= "\t<p><a href=\"map.php\">Full Universe Map</a></p>\n";
}

if ($user['ship_id'] !== NULL) {
	if ($user_ship['empty_bays'] != $user_ship['cargo_bays']) {
		$locBar .= "\t<p><a href=\"location.php?jettison=1\">Jettison Cargo</a></p>\n";
	}
}


if($user['genesis'] > 0) {
	$locBar .= "\t<p><a href=\"planet_build.php?location=$user[location]\">Use Genesis Device</a> ($user[genesis])</p>\n";
}

if($user['alpha'] > 0) {
	$locBar .= "\t<p><a href=\"bombs.php?alpha=1\">Use Alpha Bomb</a> ($user[alpha])</p>\n";
}
if($user['gamma'] > 0) {
	$locBar .= "\t<p><a href=\"bombs.php?bomb_type=1\">Use Gamma Bomb</a> ($user[gamma])</p>\n";
}
if($user['delta'] > 0) {
	$locBar .= "\t<p><a href=\"bombs.php?bomb_type=2\">Use Delta Bomb</a>! ($user[delta])</p>\n";
}
if($user['sn_effect'] > 0) {
	$locBar .= "\t<p><a href=\"bombs.php?sn_effect=1\">Use SuperNova Effector</a>!</p>\n";
}

if (isset($user_ship['config'])) {
	if (strpos($user_ship['config'], 'tw') !== false) {
		$locBar .= "<br><b><form name=transwarp_form action='location.php' method=POST><a href='javascript:alert(\"Use Transwarp to travel a short distance across the universe without warp links. Limit of 15 light years.\\nCan tow unlimited number of ships however each additional ship towed adds one turn to Warp cost.\\n\\t\\t\\t\\tCost: 5+ turns\")'>Transwarp Jump</a> - <a href='location.php?transburst=1'>Burst</a><br>Destination: <input type='text' size='3' maxlength='3' name='transwarp'><br><input type='submit' value='Engage'></form></b><p>";
	}

	if (strpos($user_ship['config'], 'sj') !== false) {
		$locBar .= "<p><b><form name=subspace_form action='location.php' method=POST><a href='javascript:alert(\"Use Sub-Space Jump to travel to anywhere in the Galaxy.\\nCan tow only 10 ships.\\n\\t\\tCost:10+ turns.\")'>SubSpace Jump</a><br>Destination: <input type='text' size='3' maxlength='3' name='subspace'><br><input type='submit' value='Engage'></form></b><p>";
	}
}

$locBar .= "</div>\n<div id=\"locInfo\">";

$error_str = $locBar . $error_str . '</div>';

print_page($header, $error_str);

?>
