<?php

require_once('inc/user.inc.php');
require_once('inc/planet.inc.php');


deathCheck($user);

if (!isset($planet_id)) {
	header('Location: system.php');
	exit();
}


$output_str = '';
$out = '';

$planet = checkPlanet($planet_id);
$planet_id = $planet['planet_id'];
$planet_loc = $userShip['location'];
$has_pass = 0;

#these two are also harboured in the add_planetary.php script (i didn't think they were used regularly enough to warrent becoming truely global vars) - Moriarty
$shield_gen_cost = 50000;
$research_fac_cost = 100000;

// user not in the correct system
if ($planet === false) {
	print_page('Planet', 'That planet does not exist.');
} elseif ($userShip['location'] != $planet_loc) {
	print_page('Planet', 'That planet is not in this system.');
} elseif ($user['turns_run'] < $turns_before_planet_attack && !IS_ADMIN) {
	print_page("No landing","Cannot land, create or attack a planet within the first <b class=b1>$turns_before_planet_attack turns </b>of your account.","?planet=1");
// planet has password and user has entered it correctly.
} elseif (!empty($planet['pass']) && $planet['login_id'] !== $user['login_id'] &&
           isset($p_pass) && $p_pass === $planet['pass']) {
	$has_pass = 1;
	setcookie("p_pass", $p_pass, time() + 600);
// admin and owner don't need password, same goes for no fighters on planet
} elseif ($planet['login_id'] === $user['login_id'] || IS_ADMIN ||
           $planet['fighters'] < 1 || ($user['clan_id'] !== NULL &&
		   $user['clan_id'] === $planet['clan_id'] && empty($planet['pass']))){
	$has_pass = 1;
// there is a password on the planet and the user must enter it.
} elseif (!empty($planet['pass']) && empty($p_pass) && $planet['fighters'] > 0) {
	unset($p_pass, $_GET['p_pass'], $_POST['p_pass']);
	get_var('Access Denied','planet.php','The owner has set a password on this planet. You must enter the password to continue.','p_pass',"");
// invalid password
} elseif (isset($p_pass) && $p_pass != $planet['pass']) {
	$rs = "<p><a href=planet.php?planet_id=$planet_id>Try again</a>";
	print_page("Error","You have entered the wrong password");

} elseif ($has_pass == 0 && $planet['fighters'] > 0 && !IS_ADMIN) {
	print_page("Warning","You may not land on this planet in this manner without first defeating the defending fighters.<p>This will require you attack the planet.");
}

if (isset($new_pass) && isset($change_pass) && isset($has_pass) && valid_input($new_pass)) {
	if(levenshtein($new_pass,$p_user['passwd']) < 2) { //password cannot be too similar to account pass
		$output_str .= "That password is too similar to your account password. Please use a different password.";
	} else {
		if($new_pass == -1){
			$new_pass = 0;
		}

		dbn("update [game]_planets set pass = '$new_pass' where planet_id = '$planet_id'");
		$planet['pass'] = $new_pass;
		$passwd = $new_pass;
		$output_str .= "The password was changed successfuly.";
	}
} elseif(isset($new_pass) && isset($change_pass) && isset($has_pass)){ //invalid password
	print_page("Password Change","That password is invalid. Only use normal letters and numbers, and no spaces.<p><a href='javascript:back()'>Try Again</a>","?planet=1");
} elseif(isset($change_pass) && !isset($new_pass)) {
	if ($user['login_id'] !== $planet['login_id'] && !IS_ADMIN) {
		print_page("Error", "Only the planet owner may change the planet's password.");
	} else {
		get_var('Planet Password','planet.php','What would you like the password to be?<br />Setting the pass to "-1" means will remove the password.','new_pass',"");
	}
}


#ensure $amount is rounded, and an integer.
$amount = isset($amount) ? round($amount) : 0;

#destroy planet
if(isset($destroy)) {
	db("select * from [game]_planets where planet_id = $planet_id");
	if($user['login_id'] === $planet['login_id'] || ($user['clan_id'] === $planets['clan_id'] && $user['clan_id'] !== NULL)) {
		if (!isset($sure)) {
			get_var('Destroy Planet','planet.php','Are you sure you want to destroy this planet?','sure','yes');
		} else {
			dbn("update [game]_stars set planetary_slots = planetary_slots + 1 where star_id = '$userShip[location]'");
			dbn("delete from [game]_planets where planet_id = $planet_id");
			post_news("$user[login_name] destroyed the planet $planet[planet_name]");
			print_page("Planet Destroyed", "Planet $planet[planet_name] destroyed");
		}
	}else{
		print_page("Unable to comply.", "You cannot destroy this planet, as you do not own it, nor does a clan-mate.");
	}
} elseif(isset($claim)) {
	if($planet['login_id'] === $user['login_id']) {
		$output_str .= "<br />You can't claim a planet from yourself.<p>";
	} elseif(($user['clan_id'] === $planet['clan_id'] || $planet['fighters'] != 0) && $user['joined_game'] > (time() - ($min_before_transfer * 86400))) {
		$output_str .= "<br />You can not transfer a planet before the min_before_transfer time is up.<p>";
	} else {
		msgSendSys($planet['login_id'], "<b class=b1>$user[login_name]</b> claimed the planet <b class=b1>$planet[planet_name]</b> from you.");
		post_news("<b class=b1>$user[login_name]</b> has claimed the planet <b class=b1>$planet[planet_name]</b>.");
		dbn("update [game]_planets set login_id = $user[login_id], pass = '' where planet_id = '$planet[planet_id]'");
		$planet['pass'] = 0;
		$planet['clan_id'] = $user['clan_id'];
		$planet['login_id'] = $user['login_id'];
		$planet['login_name'] = $user['login_name'];
		$has_pass = 1;
		$output_str .= "You have successfully claimed the planet.";
	}

/*
* Autoshift
*/
} elseif(isset($autoshift)){
	#get ship information/see if there is enough capacity etc.
	$info = $db->query('SELECT COUNT(*), SUM(cargo_bays - metal - fuel - ' .
	 'elect - organ - colon) FROM [game]_ships WHERE login_id = %u AND ' .
	 'location = %u', array($user['login_id'], $userShip['location']));
	$ship_count = $db->fetchRow($info, ROW_NUMERIC);
	$colonist_cap = $ship_count[1];			#total cargo capacity of fleet in system
	$colonist = $ship_count[1];				#total cargo capacity of fleet in system
	$ship_count = $ship_count[0];			#number of ships in system that have cargo capacity
	db("select ship_id from [game]_ships where login_id = '$user[login_id]' AND location = '$userShip[location]' AND config REGEXP 'ws'");					#ensure there is a transverser with the ws upgrade
	$lead = dbr();

	#figure out what the user is dealing in.
	if($autoshift == 1){
		$tech_mat = "colon";
		$text_mat = "Colonists";
	} elseif($autoshift == 2){
		$tech_mat = "metal";
		$text_mat = "Metal";
	} elseif($autoshift == 3){
		$tech_mat = "fuel";
		$text_mat = "Fuel";
	} elseif($autoshift == 4){
		$tech_mat = "elect";
		$text_mat = "Electronics";
	} elseif($autoshift == 5){
		$tech_mat = "organ";
		$text_mat = "Organics";
	}

	if(!isset($ship_count)) {#ensure there is some cargo cap
		$output_str .= "You do not have any ships with free cargo capacity.<p>";
	} elseif ($user['turns'] < 2) {#ensure there is some cargo cap
		$output_str.= "Your turn count is less than 2. Thats not going to get you anywere and back!";
	} elseif (!isset($lead['ship_id'])) { #ensure there is a transverser with the ws upgrade
		$output_str .= <<<END
<p>You <strong>do not</strong> have a <em>transverser</em> in this system 
that has a <em>wormhole stabiliser</em> upgrade: this is required to 
autoshift materials.</p>

END;
	} elseif(!isset($dest_system)){ #get the user to select a system from where the colonists are to come from
		$new_page = "Please select a planet from which the $text_mat are going to come:";
		db2("select planet_id,planet_name from [game]_planets where login_id = '$user[login_id]' AND location != '$userShip[location]' AND ((colon - (alloc_fight + alloc_organ + alloc_elect) > 0 AND $autoshift = 1) OR ($autoshift > 1 AND $tech_mat > 0))"); #gets users planet other than ones in the present system.
		$other_sys = dbr2();

		if(!isset($other_sys['planet_id']) && $autoshift > 1){ #determine if there is a suitable target.
			$output_str .= "You have no planets with a supply of $text_mat.<p>";
		} else {
			$new_page .= "<form action=\"planet.php\" method=\"post\" name=\"autoshifting\">";
			$new_page .= "<select name=\"dest_system\">";

			if($autoshift==1){
				$new_page .= "<option value=-1>Sol/Earth</option>";
			}

			while ($other_sys) {
				$new_page .= "<option value=$other_sys[planet_id]>$other_sys[planet_name]</option>";
				$other_sys = dbr2();
			}
			$new_page .= "</select>";
			$new_page .= "<input type=hidden name=autoshift value='$autoshift'>";
			$new_page .= "<input type=hidden name=planet_id value=$planet_id>";
			$new_page .= "<p><input type=\"submit\" value=\"Submit\"></form>";
			print_page("Autoshift",$new_page);
		}
	} else { #user has selected destination.
		if ($dest_system == -1) { #user is getting the colonists from Sol. Thus needs to pay, and there is an infinite source.
			$turn = round(get_star_dist($userShip['location'], 1) / 2 + 1) * 2;	#do maths to work out turn cost to get there
			if($user['turns'] < $turn) { #ensure user has enough turns to get there
				$output_str .= "You do not have enough turns.<br />It requires <b>$turn</b> turns just to get to <b class=b1>Sol</b> and back. Thats before ship loading.<p>";
			} else { #main autoshifting bit for taking colonists from Sol
				#determine if player can afford the costs, and if they can, then do the processing
				$c_c = $cost_colonist * $colonist;
				if($user['cash'] < $c_c || $user['turns'] < $turn + $ship_count){
					if($cost_colonist > 0){
						$colonist = floor($user['cash'] / $cost_colonist);
					}
					if($colonist_cap > $colonist || $user['turns'] < $turn + $ship_count){
						$free_turns = $user['turns'] - $turn;
						$bays_used = 0;
						$count_quick = 0;

						db2("select sum(cargo_bays-metal-fuel-elect-organ-colon),ship_id from [game]_ships where login_id = '$user[login_id]' AND location = '$userShip[location]' AND (cargo_bays-metal-fuel-elect-organ-colon) > 0 group by ship_id order by (cargo_bays-metal-fuel-elect-organ-colon) desc");
						$quick_ship = dbr2();
						while ($quick_ship && $bays_used < $colonist && $free_turns > $count_quick) {
							$bays_used += $quick_ship[0];
							++$count_quick;
							$quick_ship = dbr2();
						}
						$ship_count = $count_quick;
						$colonist_cap = $bays_used;
						if($colonist > $colonist_cap){
							$colonist = $colonist_cap;
						}
					}
				}
				$turn += $ship_count;
				$c_c = $colonist * $cost_colonist;
				if($sure != 'yes') { #ensure the user wants to carry out the autoshift.
					get_var('Autoshift','planet.php',"Are you sure you want to autoshift using the folling stats:
					<br /><b>$ship_count</b> ship(s).
					<br /><b>$colonist</b> Total Colonist Capacity.
					<p>From <b class=b1>Sol</b>(<b>#1</b>) to the planet <b class=b1>$planet[planet_name]</b>(#<b>$planet[location]</b>).
					<p>At a total cost of <b>$turn</b> turns and <b>$c_c</b> Credits?",'sure','');
				} else { #update the game cos the user does want to do the autoshifting.
					dbn("update [game]_planets set colon = colon + '$colonist' where planet_id = '$planet_id'");
					giveTurnsPlayer(-$turn);
					giveMoneyPlayer(-$c_c);
					$output_str .= "Transportation of <b>$colonists</b> Colonists Complete.<p>";
				}
			}

		} else { #user is getting the materials from a system other than Sol. Thus different maths and stuff needs to be done as there is a finite number of materials, but no cash cost.

			db("select location,login_id,planet_name,$tech_mat,planet_id,alloc_fight,alloc_elect,alloc_organ from [game]_planets where planet_id = '$dest_system'");
			$from_sys=dbr();
			$turn = round(get_star_dist($userShip['location'],$from_sys['location'])/1.8 +1)*2; #work out turn cost
			#echo $turns_can_use = floor(($user['turns']- $turn) * 1.35);
			if($user['turns'] < $turn) { #ensure user has enough turns to get there
				$output_str .= "You do not have enough turns.<br />It requires <b>$turn</b> turns to get to <b class=b1>$from_sys[planet_name](#<b>$from_sys[location]</b>)</b> and back.<p>";
			} elseif(!isset($from_sys)){
				$output_str .= "That planet is not a viable target.<p>";
			} elseif($from_sys['login_id'] != $user['login_id']){
				$output_str .= "An aspiring pirate I see. <br />That planet is not yours to take from.<p>";
			} elseif(($from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight'] + 	$from_sys['alloc_organ'])) < 1 && $autoshift == 1){
				$output_str .= "The planet you are getting the colonists from does not have any colonists available to transport.<br />It is only possible to transport Idle colonists.<p>";
			} elseif($autoshift > 1 && $from_sys[$tech_mat] <= 0){
				$output_str .= "That planet does not have any $text_mat available.<p>";
			} else { #main autoshifting bit for taking materials from target planet
				if($autoshift == 1){
					$available = $from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight'] + $from_sys['alloc_organ']);
				} else {
					$available = $from_sys[$tech_mat];
				}

				if($available >= $colonist_cap){ #there are more goods on target planet than there is cargo capacity.
					$col_to_take = $colonist_cap;
				} else { #got more goods capacity than goods on planet.
					$col_to_take = $available;
				}

				if($sure != 'yes') { #ensure the user wants to carry out the autoshift.
					get_var('Autoshift','planet.php',"Are you sure you want to autoshift using the folling stats:
					<p>Shift <b>$col_to_take</b> $text_mat from $from_sys[planet_name](#<b>$from_sys[location]</b>) to the planet <b class=b1>$planet[planet_name]</b>(#<b>$planet[location]</b>) using <b>$turn</b> turns?",'sure','');
				} else { #update the game as the user does want to do the autoshifting.
					dbn("update [game]_planets set $tech_mat = $tech_mat + '$col_to_take' where planet_id = '$planet_id'");				#give goods to recieving planet
					dbn("update [game]_planets set $tech_mat = $tech_mat - '$col_to_take' where planet_id = '$from_sys[planet_id]'");	#take goods from sending planet.
					giveTurnsPlayer(-$turn);
					$output_str .= "Transportation of <b>$col_to_take</b> $text_mat Complete.<p>";
				}
			}
		}
	}

#take or leave a physical resource using 1 ship.
} elseif (isset($mineral_alloc)) {
	if (conditions($user, $planet)) { #ensure user is allowed to play with this sort of stuff.
		$output_str .= "Physical resources may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} else {
		#ensure all are rounded & valid
		if (isset($set_fighters)) {
			$process['fighters'] = round($set_fighters);
		}
		if (isset($set_colon)) {
			$process['colon'] = round($set_colon);
		}
		if (isset($set_metal)) {
			$process['metal'] = round($set_metal);
		}
		if (isset($set_fuel)) {
			$process['fuel'] = round($set_fuel);
		}
		if (isset($set_elect)) {
			$process['elect'] = round($set_elect);
		}
		if (isset($set_organ)) {
			$process['organ'] = round($set_organ);
		}

		foreach($process as $key => $set_to){
			if ($set_to >= 0 && $set_to != $planet[$key] && 
			     (($userShip['max_fighters'] > 0 && $key == "fighters") || 
			     ($userShip['cargo_bays'] > 0 && $key != "fighters"))) {
				$old_ent = $planet[$key]; # to ensure a user only gets charged for things that are actually changed.

				if($user['turns'] < 1){
					$output_str .= "You need 1 turn per resource you are planning on transfering (metal, fuel, elect, colon, organ, fighters).";
				} else {
					if($set_to > $userShip[$key] + $planet[$key]){ #ensure user doesn't go over the limit.
						$set_to = $userShip[$key] + $planet[$key];
					}

					if($set_to > $planet[$key]){ #user putting onto planet.
						$take_from_user = $set_to - $planet[$key];
						$userShip[$key] -= $take_from_user;
						dbn("update [game]_ships set $key = $key - '$take_from_user' where ship_id = $userShip[ship_id]");
						$planet[$key] = $set_to;
						dbn("update [game]_planets set $key = '$set_to' where planet_id = $planet_id");
					} else { #taking from planet.
						$give_to_user = $planet[$key] - $set_to; #ensure no ship limits are broken.
						if($give_to_user > $userShip['max_fighters'] && $key == "fighters"){
							$give_to_user = $userShip['max_fighters'] - $userShip['fighters'];
						} elseif($give_to_user > $userShip['empty_bays'] && $key != "fighters"){
							$give_to_user = $userShip['empty_bays'];
						}
						$set_to = $planet[$key] - $give_to_user;

						$userShip[$key] += $give_to_user;
						dbn("update [game]_ships set $key = $key + '$give_to_user' where ship_id = $userShip[ship_id]");
						$planet[$key] = $set_to;
						dbn("update [game]_planets set $key = '$set_to' where planet_id = $planet_id");
					}
					empty_bays($userShip); #ensure things are kept up to date.

					if($old_ent != $set_to){ #charge the user 1 turn per resource transfered.
						giveTurnsPlayer(-1);
						if(isset($type) && $type == 1 && $set_to < $old_ent){#if colonists have been messed with.
							$planet['alloc_fight'] = 0;
							$planet['alloc_elect'] = 0;
							$planet['alloc_organ'] = 0;
							dbn("update [game]_planets set alloc_fight = 0, alloc_elect=0, alloc_organ=0 where planet_id = $planet_id");
							$out .= "<p>As you took away some colonists, any remaining colonists assigned to production duties have become idle.";
						}
					}
				}
			}
		}
	}

#=============
#this code allows users to take and leave charges at the shield generator
#=============

/*if(isset($shield)) {
	if($shield == 0) { // Take
	if(conditions($user,$planet)) {
		$output_str .= "Shields may not be taken from this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} elseif($amount < 1) {
			$def = $planet[shield_charge];
			if($def > ($userShip[max_shields] - $userShip[shields])) {
				$def = $userShip[max_shields] - $userShip[shields];
			}
			get_var('Charge Shields','planet.php','How many shield charges do you want to take?','amount',$def);
		} else {
			if($amount > $planet[shield_charge]) { // has on planet
				$output_str .= "There are not <b>$amount</b> Shield Charges on this planet.<p>";
			} elseif($amount > ($userShip[max_shields] - $userShip[shields])) { // can have that many
				$output_str .= "Your ship can not hold that many Shields.<p>";
			} else {
				dbn("update [game]_planets set shield_charge = shield_charge - $amount where planet_id = $user[on_planet]");
				dbn("update [game]_ships set shields = shields + $amount where ship_id = $user[ship_id]");
				$userShip[shields] += $amount;
			}
		}
	} elseif($shield == 1) { // Leave
	if(conditions($user,$planet)) {
		$output_str .= "Shields may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($amount < 1) {
			$def = $userShip[shields];
			if($def > ($planet[shield_gen] * 1000) - $planet[shield_charge]) {
				$def = ($planet[shield_gen] * 1000) - $planet[shield_charge];
			}
			get_var('Leave shields','planet.php','How many shields do you want to leave?','amount',$def);
		} else {
			if($amount > $userShip[shields]) { // has on ship
				$output_str .= "There is not <b>$amount</b> shields on your ship.<p>";
		} elseif(($amount + $planet[shield_charge]) > ($planet[shield_gen] * 1000)) { // no more space on planet
		} else {
				dbn("update [game]_planets set shield_charge = shield_charge + $amount where planet_id = $user[on_planet]");
				dbn("update [game]_ships set shields = shields - $amount where ship_id = $user[ship_id]");
				$userShip[shields] -= $amount;
			}
		}
	}*/

#dump the fleet
} elseif(isset($do_all) && isset($type)){
	#first, determine what is to be dealt with.
	if($type == 0){
		$tech_mat = "fighters";
		$text_mat = "Fighters";
	} elseif($type == 1){
		$tech_mat = "colon";
		$text_mat = "Colonists";
	} elseif($type == 2){
		$tech_mat = "metal";
		$text_mat = "Metal";
	} elseif($type == 3){
		$tech_mat = "fuel";
		$text_mat = "Fuel";
	} elseif($type == 4){
		$tech_mat = "elect";
		$text_mat = "Electronics";
	} elseif($type == 5){
		$tech_mat = "organ";
		$text_mat = "Organics";
	}

	if($user['turns'] < 1){
		$output_str .= "This action will expend at least 1 turn.";
	} elseif($do_all == 2){ #leave goods
		#get ship info of ships that are valid.
		db("select sum($tech_mat) as goods, count(ship_id) as ship_count from [game]_ships where login_id = '$user[login_id]' AND location = '$planet_loc' AND $tech_mat > 0");
		$results = dbr();

		$turn_cost = ceil($results['ship_count'] * 0.75);

		if(!isset($results['goods'])) { #ships empty?
			$output_str .=  "You have no <b class=b1>$text_mat</b> in any ships in this system.";
		} elseif($user['turns'] < $turn_cost) { #enough turns?
			$output_str .= "It will cost <b>$turn_cost</b> turns to perform this action.<br />At present you do not posses that many turns.";
			unset($results);
		} elseif(conditions($user,$planet)) { #check to see if been in game for long enough
			$output_str .= "$text_mat may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif(!(isset($sure) && $sure == 'yes')) { #confirmation
			get_var("Leave all $text_mat",'planet.php',"Are you sure you want to leave all the <b class=b1>$text_mat</b>(<b>$results[goods]</b>) from the <b>$results[ship_count]</b> ships with it on in this system onto the planet below, at a cost of <b>$turn_cost</b> turns?",'sure','yes');
		} else {
			dbn("update [game]_planets set $tech_mat = $tech_mat + '$results[goods]' where planet_id = $planet_id");
			dbn("update [game]_ships set $tech_mat = 0 where login_id = '$user[login_id]' AND location = '$planet_loc' AND $tech_mat > 0");
			giveTurnsPlayer(-$turn_cost);
			$userShip[$tech_mat] = 0;
			empty_bays($userShip);
			$output_str .= "<b>$results[ship_count]</b> ships unloaded at a cost of <b>$turn_cost</b> turns.";

			db("select * from [game]_planets where planet_id = '$planet_id'");
			$planet = dbr(1);
		}
	} elseif($do_all == 1){ #taking the goods

		$taken = 0; //goods taken from planet so far.
		$ship_counter = 0;

		if(conditions($user,$planet)) {#been in game long enough?
			$output_str .= "Fighters may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($planet[$tech_mat] < 1) { #can't take stuff if there isn't any to take
			$output_str.= "This planet has no <b class=b1>$text_mat</b> on it.";
		} elseif($type == 0){ #fighters
			db2("select ship_id,(max_fighters - fighters) as free,ship_name, fighters, colon, metal, cargo_bays, fuel, elect, organ from [game]_ships where login_id = '$user[login_id]' AND location = '$planet_loc' AND (max_fighters - fighters) > 0 order by free desc");
		} elseif($type != 0) {
			db2("select ship_id, (cargo_bays - metal - fuel - elect - organ - colon) as free, ship_name, fighters, colon, metal, cargo_bays, fuel, elect, organ from [game]_ships where login_id = '$user[login_id]' AND location = '$planet_loc' AND (cargo_bays - metal - fuel - elect - organ - colon) > 0 order by free desc");
		}

		$ships = dbr2();

		if (!isset($ships['ship_id'])) { #check to see if there are any ships
			$output_str.= "There are no ships that have any space free for <b class=b1>$text_mat</b>.";
		} else {
			while ($ships) {
				//planet can load ship w/ spare fighters maybe.
				if ($ships['free'] < ($planet[$tech_mat] - $taken)) {
					++$ship_counter;
					if (isset($sure) && $sure == "yes") { #only run during the real thing.
						dbn("update [game]_ships set $tech_mat = $tech_mat + $ships[free] where ship_id = '$ships[ship_id]'");
						$out .= "<br /><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$ships[free]</b> <b class=b1>$text_mat</b> to maximum capacity.";
						if($ships['ship_id'] == $userShip['ship_id'] && $type == 0){ #update user ship
							$userShip['fighters'] = $userShip['max_fighters'];
						} elseif($ships['ship_id'] == $userShip['ship_id'] && $type > 0){ #update user ship
							$userShip[$tech_mat] += $ships['free'];
							$userShip['empty_bays'] -= $ships['free'];
						}
					}
					$taken += $ships['free'];

					#ensure user has enough turns, or stop the loop where the user is.
					if($user['turns'] == ceil($ship_counter * 0.75)){
						$turns_txt = "You do not have enough turns to load your whole fleet.<br />But you do have enough to perform this limited action:<p>";
						$out .=  "<br />You did not have enough turns to continue with the operations any further.";
						unset($ships);
						break;
					}
				//planet will run out of fighters.
				} elseif ($ships['free'] >= ($planet[$tech_mat] - $taken)) {
					++$ship_counter;
					$t868 = $ships[$tech_mat] + ($planet[$tech_mat] - $taken);
					if (isset($sure) && $sure == "yes") { // only run during the real thing.
						dbn("UPDATE [game]_ships SET $tech_mat = '$t868' where ship_id = '$ships[ship_id]'");

						$out .= "<br /><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$t868</b> <b class=b1>$text_mat</b>";

						if($ships['ship_id'] == $userShip['ship_id'] && $type == 0){ #update user ship
							$userShip['fighters'] = $t868;
						}elseif($ships['ship_id'] == $userShip['ship_id'] && $type > 0){ #update user ship
							$userShip[$tech_mat] = $t868;
							empty_bays($userShip);
						}
					}
					$taken += $t868 - $ships[$tech_mat];
					unset($ships);
					break;
				}
				$ships = dbr2();
			} #end loop of ships

			$turn_cost = ceil($ship_counter * 0.75);

			if($user['turns'] < $turn_cost) {
				$output_str.= "You need <b>5</b> turns to load all ships in a system.";
			} elseif (!(isset($sure) && $sure == "yes")) {
				get_var('Load all ships','planet.php', (isset($turns_txt) ? $turns_txt : '') . "Are you sure you want to load <b>$ship_counter</b> ships in this system with <b>$taken</b> <b class=b1>$text_mat</b> at a cost of about <b>$turn_cost</b> turns?",'sure','yes');
			} else {
				dbn("update [game]_planets set $tech_mat = $tech_mat - $taken where planet_id = $planet_id");
				giveTurnsPlayer(-$turn_cost);

				if($type == 1){ #colonists
					$planet['alloc_fight'] = 0;
					$planet['alloc_elect'] = 0;
					$planet['alloc_organ'] = 0;
					dbn("update [game]_planets set alloc_fight = 0, alloc_elect=0, alloc_organ=0 where planet_id = $planet_id");
					$out .= "<p>As you took away some colonists, any remaining colonists assigned to production duties have become idle.";
				}

				print_page("$text_mat Loaded","<b>$ship_counter</b> ships had their bays augmented by a total of <b>$taken</b> <b class=b1>$text_mat</b> from the planet <b class=b1>$planet[planet_name]</b>:<br />".$out."<p>The total turn cost was <b>$turn_cost</b>.");
			}
		}
	}


} elseif(isset($all_shield)) { // Charge all shields on all ships in system.
	$taken = 0; //Shields taken from planet so far.
	$ship_counter = 0;
	if (!(isset($sure) && $sure == "yes")) {
		get_var('Charge all ships','planet.php',"Are you sure you want to charge all the Ships in this system with <b class=b1>shields</b>?",'sure','yes');
	} elseif(conditions($user,$planet)) {
		$output_str .= "Shields may not be taken from this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} elseif($user[turns] < 3) {
		print_page("Error","You need <b>3</b> turns to charge all ships in a system.");
	} elseif($planet[shield_charge] < 1) {
		print_page("Error","This planet has no shield charges on it.");
	} else {
		db2("select ship_id,shields,max_shields,ship_name from [game]_ships where login_id = '$user[login_id]' AND location = '$planet_loc' AND max_shields > 0 AND shields < max_shields");
		while($ships = dbr2()) {
			//planet can charge ship w/ spare shields maybe.
			$free = $ships[max_shields] - $ships[shields];
			if($free <= ($planet[shield_charge] - $taken)) {
				$ship_counter++;
				dbn("update [game]_ships set shields = max_shields where ship_id = '$ships[ship_id]'");
				$out .= "<br /><b class=b1>$ships[ship_name]</b> had its shields increased by <b>$free</b> to full.";
				if($ships[ship_id] == $userShip[ship_id]){
					$userShip[shields] = $userShip[max_shields];
				}
				$taken += $free;
			//planet will run out of shields.
			} elseif($free >= ($planet[shield_charge] - $taken)) {
				$ship_counter++;
				$t868 = $ships[shields] + ($planet[shield_charge] - $taken);
				dbn("update [game]_ships set shields = '$t868' where ship_id = '$ships[ship_id]'");
				if($ships[ship_id] == $userShip[ship_id]){
					$userShip[shields] = $t868;
				}
				$taken += $t868 - $ships[shields];
				$out .= "<br /><b class=b1>$ships[ship_name]</b>s shields were charged to <b>$t868</b> shields.";
				break;
			}
			if(($planet['shield_charge'] - $taken) < 1){
				break;
			}
		}
		dbn("update [game]_planets set shield_charge = shield_charge - $taken where planet_id = $planet_id");
		if($ship_counter > 0){
				giveTurnsPlayer(-3);
			print_page("Shields Charged","<b>$ship_counter</b> ships had their shields charged by the planet <b class=b1>$planet[planet_name]</b>:<br />".$out);
		} else {
			print_page("No Ships","No ships where charged as all ships in this system have full shields.");
		}
	}

//assign colonists
} elseif (isset($assigning)) {
	#ensure all are rounded & valid
	$num_pop_set_1 = round($num_pop_set_1);
	settype($num_pop_set_1, "integer");

	$num_pop_set_2 = round($num_pop_set_2);
	settype($num_pop_set_2, "integer");

	$num_pop_set_3 = round($num_pop_set_3);
	settype($num_pop_set_3, "integer");

	$set_tax_rate = round($set_tax_rate);
	settype($set_tax_rate, "integer");


	if($num_pop_set_1 >= 0 && $num_pop_set_1 != $planet['alloc_fight']) { // Fighters
		if($num_pop_set_1 > idle_colonists() + $planet['alloc_fight']){ #ensure user doesn't go over the limit.
			$num_pop_set_1 = idle_colonists() + $planet['alloc_fight'];
		}
		$planet['alloc_fight'] = $num_pop_set_1;
		dbn("update [game]_planets set alloc_fight = $num_pop_set_1 where planet_id = $planet_id");
	}

	if($num_pop_set_2 >= 0 && $num_pop_set_2 != $planet['alloc_elect']) { // Electronics
		if($num_pop_set_2 > idle_colonists() + $planet['alloc_elect']){ #ensure user doesn't go over the limit.
			$num_pop_set_2 = idle_colonists() + $planet['alloc_elect'];
		}
		$planet['alloc_elect'] = $num_pop_set_2;
		dbn("update [game]_planets set alloc_elect = $num_pop_set_2 where planet_id = $planet_id");
	}

	if($num_pop_set_3 >= 0 && $num_pop_set_3 != $planet['alloc_organ']) { // Organics
		if($num_pop_set_3 > idle_colonists() + $planet['alloc_organ']){ #ensure user doesn't go over the limit.
			$num_pop_set_3 = idle_colonists() + $planet['alloc_organ'];
		}
		$planet['alloc_organ'] = $num_pop_set_2;
		dbn("update [game]_planets set alloc_organ = $num_pop_set_3 where planet_id = $planet_id");

	}

	if($set_tax_rate >= 0 && $set_tax_rate <= 20) { // tax rate within boundaries
		$planet['tax_rate'] = $set_tax_rate;
		dbn("update [game]_planets set tax_rate = $set_tax_rate where planet_id = $planet_id");
	}

} elseif(isset($monetary)){
	#ensure all are rounded & valid
	$set_cash = round($set_cash);
	settype($set_cash, "integer");

	if($set_cash >= 0 && $set_cash != $planet['cash']){ #cash dispensary
		if($set_cash > $user['cash'] + $planet['cash']){ #ensure user doesn't go over the limit.
			$set_cash = $user['cash'] + $planet['cash'];
		}

		if($set_cash > $planet['cash']){ #user putting money onto planet.
			$take_from_user = $set_cash - $planet['cash'];
			giveMoneyPlayer(-$take_from_user);
			$planet['cash'] = $set_cash;
			dbn("update [game]_planets set cash = $set_cash where planet_id = $planet_id");
		} else { #taking money from planet.
			$give_to_user = $planet['cash'] - $set_cash;
			giveMoneyPlayer($give_to_user);
			$planet['cash'] = $set_cash;
			dbn("update [game]_planets set cash = $set_cash where planet_id = $planet_id");
		}
	}
} elseif (isset($set_rep) && $set_rep >=0 && $set_rep <= 2) {
	dbn("update [game]_planets set daily_report = '$set_rep' where planet_id = $planet_id");
	$planet['daily_report'] = $set_rep;
} elseif (isset($mil) && $mil == 1){ #offensive/defensive planet.
	$flag = 1;

	$args = array($planet['location'], $user['login_id']);
	$clan = '';
	if ($planet['clan_id'] !== NULL) {
	    $args[] = $user['clan_id'];
	    $clan = ' OR clan_id != %u';
	}

	$ships = $db->query('SELECT COUNT(*) FROM [game]_ships AS s ' .
	 'LEFT JOIN [game]_users AS u ON s.login_id = u.login_id ' .
	 'WHERE s.location = %u AND u.login_id != %u AND (clan_id IS NULL' .
	 $clan . ')', $args);

	if ($db->fetchRow($ships) || attack_planet_check($user)) {
		$flag = 0;
	}

	if($flag || IS_ADMIN) {
		dbn("update [game]_planets set fighter_set = $mil where planet_id = $planet_id");
		$planet['fighter_set'] = 1;
	} else {
		$output_str .= "Planet unable to go into attack mode with enemy ships in star system, or with an enemy hostile planet in the system.<p>";
	}
} elseif(isset($mil) && $mil == 0) {
	dbn("update [game]_planets set fighter_set = $mil where planet_id = $planet_id");
	$planet['fighter_set'] = 0;
}

if(isset($rename)){
	if($user['login_id'] !== $planet['login_id']){
		$output_str .= "This planet is not yours to re-name.";
	} elseif(isset($name_to)) {
		$name_to = correct_name($name_to);
		if(!$name_to || strlen($name_to) < 3) {
			$rs = "<p><a href=javascript:history.back()>Try Again</a>";
			print_page("Invalid Name","That is not a valid name. Must have more than three characters.");
		}

		$output_str .= "Planet re-named from <b class=b1>$planet[planet_name]</b> to <b class=b1>$name_to</b>.";
		dbn("update [game]_planets set planet_name = '$name_to' where planet_id = '$planet[planet_id]'");
		post_news("$user[login_name] renamed the planet $planet[planet_name] to $name_to.");
	} else {
		$output_str .= <<<END
<h1>New planet name (up to 32 characters)</h1>
<form method="post" action="planet.php">
	<p><input type="output_str" name="name_to" size="30" 
	 value="$planet[planet_name]" /></p>
	<p><input type="hidden" name="rename" value="1" />
	<input type="hidden" name="planet_id" value="$planet[planet_id]" />
	<input type="submit" value="Rename" class="button" /><p>
</form>

END;
	}
	print_page("Rename Planet", $output_str);
}

$messages = $output_str;

$output_str = "<h1>" . esc($planet['planet_name']) . "</h1>\n";

if ($planet['login_id'] === NULL) {
	$output_str .= "<p>No-one owns this planet.</p>\n";
} elseif ($planet['login_id'] == $user['login_id']) {
	$output_str .= "<p>You are in control of this planet.</p>\n";
} else {
	$output_str .= "<p>" . print_name($planet) . " owns this planet.</p>\n";
}

if ($userOpt['show_pics']) {
	$output_str .= "<p><img src=\"img/planets/$planet[planet_img].jpg\" alt=\"Planet\" /></p>";
}
if(!empty($messages)){
	$output_str .= "<p>$messages</p>\n";
}

if ($has_pass == 1) {
	$passOption = 'Password is ' . (empty($planet['pass']) ? 'not set' :
	 ('<em>' . esc($planet['pass']) . '</em>'));
	if ($user['login_id'] === $planet['login_id']) {
		$passOption .= ", <a href=\"planet.php?change_pass=1&amp;planet_id=$planet_id\">change it</a>";
	}

	$output_str .= <<<END
<h2>Options</h2>
<ul>
	<li>Change the <a href="planet.php?planet_id=$planet_id&rename=1">planet name</a></li>
	<li>$passOption</li>

END;

	if ($planet['login_id'] !== $user['login_id']) {
		$output_str .= "\t<li><a href=\"planet.php?planet_id=$planet_id&claim=1\">Claim $planet[planet_name]</a></li>\n";
	} elseif ($planet['login_id'] === $user['login_id'] ||
	           $planet['clan_id'] === $user['clan_id']) {
		$output_str .= "\t<li><a href=\"planet.php?planet_id=$planet_id&amp;destroy=1\">Destroy planet</a></li>\n";
	}


	$output_str .= "\t<li>Send daily production report ";

	if($planet['daily_report'] == 0){
		$output_str.= "never, ";
	} else {
		$output_str.= "<a href=\"planet.php?set_rep=0&amp;planet_id=$planet_id\">never</a>, ";
	}
	if($planet['daily_report'] == 1){
		$output_str.= "if planet produces or ";
	} else {
		$output_str.= "<a href=\"planet.php?set_rep=1&amp;planet_id=$planet_id\">if planet produces</a> or ";
	}
	if($planet['daily_report'] == 2){
		$output_str.= "always";
	} else {
		$output_str.= "<a href=\"planet.php?set_rep=2&amp;planet_id=$planet_id\">always</a>";
	}
	$output_str .= "</li>\n";


	$fig_str = $planet['fighter_set'] == 0 ? 
	 "<a href=\"planet.php?planet_id=$planet_id&amp;mil=1\">Passive</a>" :
	 "<a href=\"planet.php?planet_id=$planet_id&amp;mil=0\">Hostile</a>";

	$idleColons = idle_colonists();

	$output_str .= <<<END
</ul>

<h2>Monetary</h2>
<form method="post" action="planet.php">
	<table class="simple">
		<tr>
			<th>Planet Cash</th>
			<td><input type="text" name="set_cash" value="$planet[cash]" size="8" class="text" /></td>
			<td><input type="hidden" name="planet_id" value="$planet_id" />
			<input type="hidden" name="monetary" value="1" />
			<input type="submit" value="Change" class="button" /></td>
		</tr>
	</table>
</form>

<h2>Physical Goods</h2>
<form method="post" action="planet.php">
	<table class="simple">
		<tr>
			<th>Fighters</th>
			<td><input type="text" name="set_fighters" value="$planet[fighters]" size="8" class="text" /></td>
			<td><a href="planet.php?planet_id=$planet_id&do_all=1&amp;type=0">Load fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&do_all=2&amp;type=0">Empty fleet</a></td>
			<td>$fig_str</td>
		</tr>

		<tr>
			<th>Colonists</th>
			<td><input type="text" name="set_colon" value="$planet[colon]" size="8" class="text" /></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=1&amp;type=1">Load fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=2&amp;type=1">Empty fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;autoshift=1">Autoshift</a></td>
		</tr>

		<tr>
			<th>Metal</th>
			<td><input type="text" name="set_metal" value="$planet[metal]" size="8" class="text" /></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=1&amp;type=2">Load fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=2&amp;type=2">Empty fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;autoshift=2">Autoshift</a></td>
		</tr>

		<tr>
			<th>Fuel</th>
			<td><input type="text" name="set_fuel" value="$planet[fuel]" size="8" class="text" /></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=1&amp;type=3">Load fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=2&amp;type=3">Empty fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;autoshift=3">Autoshift</a></td>
		</tr>

		<tr>
			<th>Electronics</th>
			<td><input type="text" name="set_elect" value="$planet[elect]" size="8" class="text" /></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=1&amp;type=4">Load fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=2&amp;type=4">Empty fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;autoshift=4">Autoshift</a></td>
		</tr>

		<tr>
			<th>Organics</th>
			<td><input type="text" name="set_organ" value="$planet[organ]" size="8" class="text" /></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=1&amp;type=5">Load fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;do_all=2&amp;type=5">Empty fleet</a></td>
			<td><a href="planet.php?planet_id=$planet_id&amp;autoshift=5">Autoshift</a></td>
		</tr>

		<tr>
			<td colspan="5"><input type="hidden" name="planet_id" value="$planet_id" />
			<input type="hidden" name="mineral_alloc" value="1" />
			<input type="submit" value="Set" class="button" />
			<input type="reset" value="Reset" class="button" /></td>
		</tr>
	</table>
</form>

<h2>Colonist allocation</h2>
<form method="post" action="planet.php">
	<table class="simple">
		<tr>
			<th>Tax rate (0 - 20%)</th>
			<td><input type="text" name="set_tax_rate" value="$planet[tax_rate]" size="3" class="text" />%</td>
		</tr>
		<tr>
			<th>Produce fighters</th>
			<td><input type="text" name="num_pop_set_1" value="$planet[alloc_fight]" size="6" class="text" /></td>
		</tr>
		<tr>
			<th>Produce electronics</th>
			<td><input type="text" name="num_pop_set_2" value="$planet[alloc_elect]" size="6" class="text" /></td>
		</tr>
		<tr>
			<th>Produce organics</th>
			<td><input type="text" name="num_pop_set_3" value="$planet[alloc_organ]" size="6" class="text" /></td>
		</tr>
		<tr>
			<th>Idle colonists</th>
			<td>$idleColons</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Set" class="button" />
			<input type="reset" value="Reset" class="button" />
			<input type="hidden" name="planet_id" 
			 value="$planet[planet_id]" />
			<input type="hidden" name="assigning" value="1" /></td>
		</tr>
	</table>
</form>

<h2>Facilities</h2>

END;

	#launch pad creation.
	if ($planet['launch_pad'] == 0) {
		$output_str .= "<p><a href=\"add_planetary.php?planet_id=$planet_id&amp;launch_pad=1\">Missile Launch Pad</a> - <b>100000</b> Credits, 200 Metal and 100 Fuel";
	} elseif ($planet['launch_pad'] > time()) { #counting down
		$output_str .= "<p>Your <em>missile launch pad</em> is not ready.</p>\n";

	} elseif ($planet['launch_pad'] <= time()) { #missle construction
		$output_str .= "<p>This planet has a <b class=b1>Missile Launch Pad</b>: <a href=add_planetary.php?planet_id=$planet_id&missile=1>Construct Omega Missile</a> - <b>100000</b> Credits, 50 Electronics, 200 Metal, 100 Fuel, 10 Turns";
		if ($planet['missile'] > 0) {
			$output_str .= "<p>This planet has $planet[missile] <em>omega missile(s)</em>: <a href=add_planetary.php?planet_id=$planet_id&launch_missile=-1>launch Omega Missile</a></p>";
		}
	}

	if(!$planet['shield_gen']){
		$output_str .= "<p><a href=\"add_planetary.php?planet_id=$planet_id&amp;shield_gen=1\">Shield Generator</a> - <b>$shield_gen_cost</b>";
	} else {
		$t545 = $planet['shield_gen']*1000;
		$output_str .= "<p>Shield Charges: <b>$planet[shield_charge]</b> / <b>$t545</b> - <a href=planet.php?planet_id=$planet_id&all_shield=1>Charge All</a>";
	}


	$planet = checkPlanet($planet_id);
# only show the "claim" link to someone who doesn't own the planet
} else {
	$output_str .= "<p><a href=\"planet.php?planet_id=$planet_id&amp;claim=1\">Claim $planet[planet_name]</a></p>";
}

print_page("Planet",$output_str);

?>
