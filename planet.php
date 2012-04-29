<?php

require_once('inc/user.inc.php');

# -----------------------
#functions for planets

$output_str = "";

#work out idle colonist count.
function idle_colonists()
{
	global $planet;
	return $planet['colon'] - $planet['alloc_fight'] - $planet['alloc_elect'] - $planet['alloc_organ'];
}


#ensure a user can transfer stuff.
function conditions($user,$planet)
{
	global $min_before_transfer;
	if ($user['joined_game'] > (time() - ($min_before_transfer * 86400)) &&
	     ($user['login_id'] != $planet['login_id'] && $planet['fighters'] != 0) &&
	     $user['login_id'] != ADMIN_ID) {
		return 1;
	} else {
		return 0;
	}
}


#function used to damage ships once they have attacked a planet.
function do_damage($amount,$from,$target,$target_ship)
{
	global $db_name,$error_str,$user,$user_ship,$yesbount;
	if($amount >= 1) {
		if($amount < $target_ship['fighters'] + $target_ship['shields']) { #ship not destroyed
			dbn("update ${db_name}_users set last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
			$shield_damage = $amount;
			if($shield_damage > $target_ship['shields']) {
				$shield_damage = $target_ship['shields'];
			}
			$amount -= $shield_damage;
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$amount' where login_id = '$from[login_id]'");
			dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$amount' where login_id = '$target[login_id]'");
			dbn("update ${db_name}_ships set fighters = fighters - $amount, shields = shields - $shield_damage where ship_id = '$target_ship[ship_id]'");
			return 0;
		} else { #ship destroyed
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$target_ship[fighters]', ships_killed = ships_killed + 1, ships_killed_points = ships_killed_points + $target_ship[point_value] where login_id = '$from[login_id]'");
			if($target_ship['shipclass'] == 2) { #escape pod destroyed
				dbn("delete from ${db_name}_ships where ship_id = $target_ship[ship_id]"); // delete ship record
				dbn("update ${db_name}_users set location = 1, ships_lost = ships_lost + 1, ships_lost_points = ships_lost_points + $target_ship[point_value], fighters_lost = fighters_lost + '$target_ship[fighters]', ship_id = NULL,last_attack = ".time().", last_attack_by = '$from[login_name]' where login_id = '$target[login_id]'");
				$target['ship_id'] = NULL;
			} else { #normal ship destroyed
				//Minerals go to the system
				dbn("update ${db_name}_stars set fuel = fuel + ".($target_ship['fuel']*(mt_rand(200,800)/1000)).", metal = metal + ".($target_ship['metal']*(mt_rand(400,900)/1000))." where star_id = $target_ship[location]");
				dbn("delete from ${db_name}_ships where ship_id = $target_ship[ship_id]"); // delete ship record


				if($target['ship_id'] != $target_ship['ship_id']) {
					$new_ship_id = $target['ship_id'];
				} else {
					#first check to see if user has other ships in same system. (will command one with most fighters).
					db("select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' && location = '$target_ship[location]' order by fighters desc limit 1");
					$other_ship = dbr(1);

					#if user has not got other ships in same system, will send command ship in a different system (preference being for ships that are not towing. if many not towing the one with the most fighters will be selected).
					if(!$other_ship) {
						db("select ship_id from ${db_name}_ships where login_id = '$target_ship[login_id]' order by towed_by asc, fighters desc limit 1");
						$other_ship = dbr(1);
					}
				}
				if($other_ship){
					$new_ship_id = $other_ship['ship_id'];
				} else {
					$temp = create_escape_pod($target);			// build the escape pod
					$new_ship_id = $temp['ship_id'];
					// run to random sector?
				}
				db("select * from ${db_name}_ships where ship_id = '$new_ship_id'");
				$user_ship = dbr(1);

				dbn("update ${db_name}_users set ships_lost = ships_lost + 1, ships_lost_points = ships_lost_points + $target_ship[point_value], location='$user_ship[location]', ship_id = '$new_ship_id', last_attack =".time().", last_attack_by = '$from[login_name]', fighters_lost = fighters_lost + '$target_ship[fighters]' where login_id = '$target[login_id]'");
				$user['ship_id'] = $new_ship_id;
			}
			return 1;
		}
	}
	return 0;
} # end do_damage function





#End of planet functions.
# ---------------------------------
sudden_death_check($user);

db("select * from ${db_name}_planets where planet_id = '$planet_id'");
$planet = dbr();
$planet_loc = $planet['location'];
$has_pass = 0;

#these two are also harboured in the add_planetary.php script (i didn't think they were used regularly enough to warrent becoming truely global vars) - Moriarty
$shield_gen_cost = 50000;
$research_fac_cost = 100000;


//user not in the correct system
if($user['location'] != $planet_loc) {
	print_page("Planet","That planet is not in this system.","?planet=1");

} elseif ($user['turns_run'] < $turns_before_planet_attack && $user['login_id'] != ADMIN_ID){
	print_page("No landing","Cannot land, create or attack a planet within the first <b class=b1>$turns_before_planet_attack turns </b>of your account.","?planet=1");

//user attacking the planet
} elseif(isset($attack_planet)) {

	db("select * from ${db_name}_planets where planet_id = '$planet_id'");
	$planet = dbr(1);
	if($user['location'] != $planet['location']) {
		print_page("Planet","That planet is not in this star system.<br> An easy mistake. After all, planets move all the time!");
	} elseif($user['login_id'] != $planet['login_id'] && ($user['clan_id'] != $planet['clan_id'] || $user['clan_id'] < 1 ) && $planet['fighters'] > 0 && $user['login_id'] != ADMIN_ID) {
		if ($flag_planet_attack == 0) {
			print_page("Attacking Planet","The admin has disabled planet attacks.");
		} elseif($user['turns'] < $planet_attack_turn_cost) {
			print_page("Attack Failed","You don't have the <b>$planet_attack_turn_cost</b> turns to use for the attack.");
		} elseif(ereg("so",$user_ship['config'])) {
			print_page("Planet","Your ship is not equipped to attack planets.");
		} elseif(ereg("no",$user_ship['config'])) {
			print_page("Attacking Planet","You may not attack using a ship that cannot attack.");
		} elseif(!isset($sure)) {
			get_var('Attacking Planet','planet.php',"Are you sure you want to attack the planet <b class=b1>$planet[planet_name]</b>?",'sure','yes');
		} else {
			charge_turns($planet_attack_turn_cost);

			#load attack, defense bonuses.
			$u_bonus = bonus_calc($user_ship);

			$short_str = "<br><b class=b1>$user[login_name]</b> used the <b class=b1>$user_ship[ship_name]</b> ($user_ship[class_name]) to attack <b class=b1>$planet[login_name]</b>'s planet: <b class=b1>$planet[planet_name]</b> in system #<b>$user[location]</b>.";


			$tech_str = "<br>Statistics:";
			$tech_str .= make_table(array("","<b class=b1>".$user_ship['ship_name']."</b> (Attacker)","<b class=b1>".$planet['planet_name']."</b> (Defender)"));

			$tech_str .= make_row(array("<b class=b1>Owner</b>",$user_ship['login_name'],$planet['login_name']));
			$tech_str .= make_row(array("<b class=b1>Ship Type</b>",$user_ship['class_name'],"Planet"));

			$tech_str .= make_row(array("<b class=b1>Fighters</b>",$user_ship['fighters'],$planet['fighters']));
			$tech_str .= make_row(array("<b class=b1>Shields</b>",$user_ship['shields'],"0"));

			#work out the damage capacity.
			$u_dam_cap = $user_ship['fighters'] + $user_ship['shields'];
			$t_dam_cap = $planet['fighters'];


			#print a line for each defense the user has.
			if($user_ship['num_ew'] > 0){
				$tech_str .= make_row(array("<b class=b1>Electronic Warfare Pods (EW)</b>",$user_ship['num_ew'],"0"));
			}

			if($user_ship['num_ot'] > 0){
				$tech_str .= make_row(array("<b class=b1>Offensive LASER Turrets (OT)</b>",$user_ship['num_ot'],"0"));
			}

			if($user_ship['num_dt'] > 0){
				$tech_str .= make_row(array("<b class=b1>Defensive LASER Turrets (DT)</b>",$user_ship['num_dt'],"0"));
			}

			if($user_ship['num_pc'] > 0){
				$tech_str .= make_row(array("<b class=b1>Plasma Cannons (OT)</b>",$user_ship['num_pc'],"0"));
			}

			if($user_ship['num_sa'] > 0){
				$tech_str .= make_row(array("<b class=b1>Silicon Armour Modules (SA)</b>",$user_ship['num_sa'],"0"));
				$u_dam_cap += $u_bonus['sa'];
			}

			$tech_str .= make_row(array("<b class=b1>Total Damage Capacity</b>",$u_dam_cap,$t_dam_cap));

			$out_str = "";
			$tech_str .= "</table>";

			#don't hurt the admin.
			if ($target['login_id'] == 1) {
				$u_bonus = NULL;
			}
			if ($user['login_id'] == ADMIN_ID) {
				$t_bonus = NULL;
			}

			$t_d_fig = 0;
			$u_d_fig = 0;

			$t_dam = 0;
			$u_dam = 0;

			#should use the ?r_ship replicas of the orignals to work out whats left on the ship to eliminate.
			$tr_ship = $planet;
			$ur_ship = $user_ship;


			if($user_ship['num_ew'] > 1){
				$tech_str .= "<br><br>Electronic Warfare Modules cannot be used in planetary attacks, as the planets atmosphere interferes.";
			}



			#=======
			#start of defensive turret section
			#=======

			#attacking ship takes out defenders fighters with defensive turret.
			if($u_bonus['dt'] >= $tr_ship['fighters'] && $tr_ship['fighters'] > 0){
				$u_bonus['dt'] -= $tr_ship['fighters'];
				$u_dt_d = $tr_ship['fighters'];
				$planet['fighters'] = 0;
				$tr_ship['fighters'] = 0;

			} elseif ($u_bonus['dt'] < $tr_ship['fighters'] && $u_bonus['dt'] > 0){ #attacker takes out some defensive fighters
				$tr_ship['fighters'] -= $u_bonus['dt'];
				$planet['fighters'] -= $u_bonus['dt'];
				$u_dt_d = $u_bonus['dt'];
				$u_bonus['dt'] = 0;
			} else {
				$u_dt_d = 0;
			}

			if($u_dt_d > 0){
				$u_dam += $u_dt_d;
				$tech_str .= "<br><br>Defensive Turrets:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$planet['planet_name']."</b>")).make_row(array("<b class=b1>Num. Fighters Destroyed</b>","$u_dt_d","0"))."</table>";
			}

			#defensive turrets complete.

			$u_bonus['at'] = $u_bonus['ot'] + $u_bonus['pc'];

			#==============
			# Offensive turrets v's fighters
			#==============
			if($u_bonus['at'] >= $tr_ship['fighters'] && ($tr_ship['fighters'] > 0 || $ush_dead == 1)){ #fighters eliminated on defending ship by turrets.
				$u_bonus['at'] -= $tr_ship['fighters'];
				$t_d_fig += $tr_ship['fighters'];
				$u_pcfig_d = $tr_ship['fighters'];
				$tr_ship['fighters'] = 0;
				$t_destroyed = 1;
			} elseif($u_bonus['at'] < $tr_ship['fighters'] && $u_bonus['at'] > 0){ #turrets stopped by defending ships fighters
				$t_d_fig += $u_bonus['at'];
				$tr_ship['fighters'] -= $u_bonus['at'];
				$u_pcfig_d = $u_bonus['at'];
				$u_bonus['at'] = 0;
			} else {
				$u_pcfig_d = 0;
			}


			if($u_pcfig_d > 0){
				$u_dam += $u_pcfig_d;
				$tech_ot .= make_row(array("<b class=b1>Fighters Destroyed</b>","$u_pcfig_d","0"));
			}


			if(!empty($tech_ot)){
				$tech_str .= "<br><br>Offensive Turrets:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$planet['planet_name']."</b>")).$tech_ot."</table>";
			}

			#most upgrades complete


			if($user_ship['fighters'] > 0){
				$attack_damage = round($user_ship['fighters'] * 0.65);
				$attack_damage += mt_rand(round(-$user_ship['fighters'] * 0.06),round($user_ship['fighters'] * 0.06));
			} else {
				$attack_damage = 0;
			}

			if($planet['fighters'] > 0){
				$counter_damage = round($planet['fighters']);
				$counter_damage += mt_rand(round(-$planet['fighters'] * 0.09),round($planet['fighters'] * 0.09));
			} else {
				$counter_damage = 0;
			}

			$xtra_attack = 1;
			$less_attack = 1;

			$xtra_counter = 1;
			$less_counter = 1;

			#take into account ship speed, and ship size.
			$xtra_attack += $user_ship['move_turn_cost'] + 15;
			$xtra_counter += 1 + $user_ship['size'];

			$less_attack += $user_ship['size'];
			$less_counter += 15;


			#ship experiance taken into account.
			$at_points = ($user_ship['points_killed'] + 1) / 100;
			if($at_points > 20){
				$at_points = 20;
			}
			$xtra_attack += $at_points;


			function inc_dam($stat,$ship,$num){
			#user battleship
				if (eregi($stat,$ship['config'])){
					return $num;
				}
			}


			#take in ship specialties
			$xtra_attack += inc_dam("bs",$user_ship,10);

			$xtra_attack += inc_dam("hs",$user_ship,3.5);

			$xtra_attack += inc_dam("ls",$user_ship,1);

			$xtra_attack += inc_dam("po",$user_ship,15);

			$less_attack += inc_dam("hs",$user_ship,4.5);
			$less_counter += inc_dam("hs",$target_ship,4.5);

			$less_attack += inc_dam("ls",$user_ship,3);
			$less_counter += inc_dam("ls",$target_ship,3);

			#do the final calculations
			$attack_damage += $attack_damage * ($xtra_attack /100);
			$attack_damage -= $attack_damage * ($less_attack /100);


			$counter_damage += $counter_damage * ($xtra_counter /100);
			$counter_damage -= $counter_damage * ($less_counter /100);


			$attack_damage = round($attack_damage);
			$counter_damage = round($counter_damage);

			#ensure nothing is negative, and that the admin doesn't get hit.
			if($attack_damage < 0 || $target['login_id'] == 1){
				$attack_damage = 0;
			}

			if($counter_damage < 0 || $user['login_id'] == ADMIN_ID){
				$counter_damage = 0;
			}

			$u_dam += $attack_damage;
			$t_dam += $counter_damage;


			if(($t_destroyed != 1 && $attack_damage > 0) || ($u_destroyed != 1 && $counter_damage > 0)){
				$tech_str .= "<br><br>Fighter Damage:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$planet['planet_name']."</b>")).make_row(array("<b class=b1>Damage Done</b>","$attack_damage","$counter_damage"));
				$end_table = "</table>";
			}

			#==========
			#silicon armour modules absorb some damage
			#==========
			#fighter damage absorbed by the SA modules
			if($u_bonus['sa'] >= $counter_damage && $counter_damage > 0){
				$out_str .= "<br>All damage dealt by the <b class=b1>$target_ship[ship_name]'s</b> <b>Fighters</b> has been absorbed by the <b>Silicon Armour Modules</b> on the <b class=b1>$user_ship[ship_name]</b>.";
				$u_bonus['sa'] -= $counter_damage;
				$u_figssa_d = $counter_damage;
				$counter_damage = 0;

			} elseif($u_bonus[sa] < $counter_damage && $u_bonus['sa'] > 0){ #sa can't take all the damage
				$out_str .= "<br><b>Silicon Armour Modules</b> on the <b class=b1>$target_ship[ship_name]</b> managed to stop <b>$u_bonus[sa]</b> of <b>Fighter</b> damage getting through to the <b class=b1>$user_ship[ship_name]</b> before withering away.";
				$counter_damage -= $u_bonus['sa'];
				$u_figssa_d = $u_bonus['sa'];
				$u_bonus['sa'] = 0;
			} else {
				$u_figssa_d = 0;
			}

			if($u_figssa_d > 0){
				$u_dam += $u_pfigssa_d;
				$tech_str .= make_row(array("<b class=b1>Stopped by Enemy SA</b>","0","$u_figssa_d"));
			}


			#get everything re-aligned
			$planet = $tr_ship;
			$user_ship = $ur_ship;


			#some shield maths for the complicated overview of the battle.
			if($user_ship['shields'] >= $counter_damage && $counter_damage > 0){
				$u_figssh_d = $counter_damage;
				$theory_counter = 0;

			} elseif($user_ship['shields'] < $counter_damage && $user_ship['shields'] > 0){
				$u_figssh_d = $user_ship['shields'];
				$theory_counter = $counter_damage - $user_ship['shields'];
			} else {
				$u_figssh_d = 0;
				$theory_counter = $counter_damage;
			}

			if($u_figssh_d > 0){
				$tech_str .= make_row(array("<b class=b1>Stopped by Enemy Shields</b>","0","$u_figssh_d"));
			}


			#some fighters maths for the complicated view of the battle.
			if($user_ship['fighters'] >= $theory_counter && $theory_counter > 0){
				$u_figsfigs_d = $theory_counter;

			} elseif($user_ship['fighters'] < $theory_counter && $user_ship['fighters'] > 0){
				$u_figsfigs_d = $user_ship['fighters'];
			} else {
				$u_figsfigs_d = 0;
			}

			if($planet['fighters'] >= $theory_attack && $theory_attack > 0){
				$t_figsfigs_d = $theory_attack;
			} elseif($planet['fighters'] < $theory_attack && $planet['fighters'] > 0){
				$t_figsfigs_d = $planet['fighters'];
			} else {
				$t_figsfigs_d = 0;
			}

			if($u_figsfigs_d > 0 || $t_figsfigs_d > 0){
				$theory_attack = $theory_attack - $t_figsfigs_d;
				$theory_counter = $theory_counter - $u_figsfigs_d;
				$tech_str .= make_row(array("<b class=b1>Used to Destroy Enemy Fighters</b>","$t_figsfigs_d","$u_figsfigs_d"));
				$tech_str .= make_row(array("<b class=b1>Un-used Firepower</b>",$theory_attack, $theory_counter));
			}

			$tech_str .= $end_table."<br><br>Totals:".make_table(array("","<b class=b1>".$user_ship['ship_name'],"<b class=b1>".$planet['planet_name']."</b>"));
			$tech_str .= make_row(array("<b class=b1>Total Damage Taken</b>",$t_dam - $theory_counter, $u_dam - $theory_attack));
			$tech_str .= make_row(array("<b class=b1>Total Damage Done</b>",$u_dam - $theory_attack, $t_dam - $theory_counter));


			#determine if the ship was destroyed or not.
			if($attack_damage > $planet['fighters']){
				$t_destroyed = 1;
			}
			if($counter_damage > $user_ship['fighters'] + $user_ship['shields']){
				$u_destroyed = 1;
			}

			#set a few vars if the ship was destroyed or not.
			if($u_destroyed ==1){
				$send_to_func_u = -1;
				$u_des_text = "Yes";
			} else {
				$send_to_func_u = $counter_damage;
				$u_des_text = "No";
			}

			if($t_destroyed ==1){
				$send_to_func_t = -1;
				$t_des_text = "Yes";
			} else {
				$send_to_func_t = $attack_damage;
				$t_des_text = "No";
			}

			$tech_str .= make_row(array("<b class=b1>Enemy Eliminated?</b>",$u_des_text, $t_des_text))."</table>";

			$temp101 = $u_dam - $theory_attack;
			$short_str .= "<br><br>The ship <b class=b1>$user_ship[ship_name]</b> did a total of <b>$temp101</b> damage.";
			$temp101 = $t_dam - $theory_counter;
			$short_str .= "<br>The planet <b class=b1>$planet[planet_name]</b> did a total of <b>$temp101</b> damage.";

			#user looses ship whilst attacking
			$planet['login_id'] = $planet['login_id'];
			if(do_damage($counter_damage,$planet,$user,$user_ship)) {
				post_news("<b class=b1>$user[login_name]</b>\'s $user_ship[class_name] was destroyed while trying to attack <b class=b1>$planet[planet_name].</b>");
				if($t_destroyed == 1){ #planet gets taken out none-the-less
					$attack_damage = $planet['fighters'];
				}
				dbn("update ${db_name}_planets set fighters = fighters - '$attack_damage' where planet_id = $planet[planet_id]");
				dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$attack_damage' where login_id = '$planet[login_id]'");
				dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$attack_damage' where login_id = '$user[login_id]'");

				if($user['ship_id'] == NULL) { #now dead
					$user_str .= "<br><br><b class=b1>$planet[planet_name]</b> successfully blew your Escape Pod to smitherines.";
					$mess .= "<br><br>You greased the Escape Pod.";
				// $output_str .= "<br>You are out of the game for $hours_after_death hours.";
				} elseif($user_ship['shipclass'] == 2) {#Now in an ep
					$user_str .= "<br><br>Your ship was destroyed. You ejected in an Escape Pod.";
					$mess .= "<br><br><b class=b1>$user[login_name]</b> is now floating around the galaxy in an Escape Pod.";
				} else {#got another ship
					$user_str .= "<br><br>Your ship was destroyed.<br>You are now in command of the <b class=b1>$user_ship[ship_name]</b>.";
					$mess .= "<br><br>You destroyed the attacking ship.";
				}
				if($t_destroyed==1){
					$user_str .= "<br><br>However you did still manage to take out all the planets fighters in the attack.";
					$mess .= "<br>However, the attacking ship did get through your planetary defences.";

				}

			#user takes out all planets defences
			} elseif($attack_damage >= $planet['fighters']) {
				$user_str .= "<br><br>The planets defences have been eradicated.<br>Your ship survived the encounter.";
				$rs = "<br><br><a href=planet.php?planet_id=$planet_id>Land</a>".$rs;
				$mess .= "<br><br>You planets defenses were all de-commisioned by way of being destroyed during the battle.<br>The attacking ship survived the encounter.";
				dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$planet[fighters]' where login_id = '$planet[login_id]'");
				dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$planet[fighters]', on_planet = '$planet_id' where login_id = '$user[login_id]'");
				dbn("update ${db_name}_planets set fighters = 0 where planet_id = '$planet[planet_id]'");
				$planet['fighters'] = 0;

			#nothing dies.
			} else {
				$user_str .= "Nothing was eliminated.";
				$mess .= "Nothing was eliminated.";
				dbn("update ${db_name}_planets set fighters = fighters - '$attack_damage' where planet_id = '$planet[planet_id]'");
				dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$attack_damage' where login_id = '$planet[login_id]'");
				dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$attack_damage' where login_id = '$user[login_id]'");
			}

			db("select attack_report from ${db_name}_user_options where login_id = '$planet[login_id]'");
			$target_options = dbr(1);

			#get recent user information.
			db("select * from ${db_name}_users where login_id = '$user[login_id]'");
			$user = dbr();
			$user_ship = userShip($user['ship_id']);

			#determine if the simple, or the complex report should be sent.
			if($target_options['attack_report'] == 1){
				send_message($planet['login_id'],addslashes($short_str.$mess));
			} else {
				send_message($planet['login_id'],addslashes($tech_str.$mess));
			}

			#determine if the simple, or the complex report should be presented.
			if($user_options['attack_report'] == 1){
				print_page("Attack",$short_str.$user_str);
			} else {
				print_page("Attack",$tech_str.$user_str);
			}

		}
	} else {
		dbn("update ${db_name}_users set on_planet = '$planet_id' where login_id = '$user[login_id]'");
		$user['on_planet'] = $planet_id;
	}


//planet has password and user has entered it correctly.
} elseif (!empty($planet['pass']) && $planet['login_id'] != $user['login_id'] && isset($p_pass) && $p_pass == $planet['pass']) {
	$has_pass = 1;
	SetCookie("p_pass",$p_pass,time()+600);

//admin and owner don't need password, same goes for no fighters on planet
} elseif($planet['login_id'] == $user['login_id'] || $user['login_id'] == ADMIN_ID || $planet['fighters'] < 1 || ($user['clan_id'] > 0 && $user['clan_id'] == $planet['clan_id'] && empty($planet['pass']))){
	$has_pass = 1;

//there is a password on the planet and the user must enter it.
} elseif(!empty($planet['pass']) && empty($p_pass) && $planet['fighters'] > 0) {
	unset($p_pass, $_GET['p_pass'], $_POST['p_pass']);
	get_var('Access Denied','planet.php','The owner has set a password on this planet. You must enter the password to continue.','p_pass',"");

//invalid password
} elseif(isset($p_pass) && $p_pass != $planet['pass']){
	$rs = "<p><a href=planet.php?planet_id=$planet_id>Try again</a>";
	print_page("Error","You have entered the wrong password");

} elseif($has_pass == 0 && $planet['fighters'] > 0 && $user['login_id'] != ADMIN_ID){
	print_page("Warning","You may not land on this planet in this manner without first defeating the defending fighters.<p>This will require you attack the planet.");
}

//the user is on the planet.
if($has_pass == 1) {
	$user['on_planet'] = $planet_id;
}



if(isset($new_pass) && isset($change_pass) && isset($has_pass) && valid_input($new_pass)){
	if(levenshtein($new_pass,$p_user['passwd']) < 2) { //password cannot be too similar to account pass
		$output_str .= "That password is too similar to your account password. Please use a different password.";
	} else {
		if($new_pass == -1){
			$new_pass = 0;
		}

		dbn("update ${db_name}_planets set pass = '$new_pass' where planet_id = '$planet_id'");
		$planet['pass'] = $new_pass;
		$passwd = $new_pass;
		$output_str .= "The password was changed successfuly.";
	}

} elseif(isset($new_pass) && isset($change_pass) && isset($has_pass)){ //invalid password
	print_page("Password Change","That password is invalid. Only use normal letters and numbers, and no spaces.<p><a href='javascript:back()'>Try Again</a>","?planet=1");
} elseif(isset($change_pass) && !isset($new_pass)) {
	if($user['login_id'] != $planet['login_id'] && $user['login_id'] != ADMIN_ID){
		print_page("Error", "Only the planet owner may change the planet's password.");
	} else {
		get_var('Planet Password','planet.php','What would you like the password to be?<br>Setting the pass to "-1" means will remove the password.','new_pass',"");
	}
}


#ensure $amount is rounded, and an integer.
if(isset($amount)) {
	$amount = (int)round($amount);
}

if($user['on_planet'] > 0) {
	$planet_id = $user['on_planet'];
}

$rs = "<p><a href=planet.php?planet_id=$planet_id>Return to Planet</a><br>";


#destroy planet
if(isset($destroy)) {
	db("select * from ${db_name}_planets where planet_id = $user[on_planet]");
	if($user['login_id'] == $planet['login_id'] || ($user['clan_id'] == $planets['clan_id'] && $user['clan_id'] > 0)) {
		if($uv_planets >= 0 && $user['terra_imploder'] <= 0){
			$output_str.= "The present game setup means that only a Terra Imploder can destroy a planet. You do not have one.<br><br>";
		} elseif($sure != 'yes') {
			get_var('Destroy Planet','planet.php','Are you sure you want to destroy this planet?','sure','yes');
		} else {
			if($uv_planets >= 0 && $user['login_id'] != ADMIN_ID){
				dbn("update ${db_name}_users set on_planet = 0, terra_imploder = terra_imploder - 1 where login_id = $user[login_id]");
				$terra_txt = " with a Terra Imploder";
				$terra_txt2 = " using 1 Terra Implosion Device";
			} else {
				dbn("update ${db_name}_users set on_planet = 0 where login_id = $user[login_id]");
			}
			dbn("update ${db_name}_stars set planetary_slots = planetary_slots + 1 where star_id = '$user[location]'");
			dbn("delete from ${db_name}_planets where planet_id = $user[on_planet]");
			post_news(addslashes("<b class=b1>$user[login_name]</b> destroyed the planet <b class=b1>$planet[planet_name]</b>".$terra_txt));
			$rs = '<p><a href=location.php>Back to the Star System</a><br>';
			print_page("Planet Destroyed","Planet <b class=b1>$planet[planet_name]</b> destroyed".$terra_txt2);
		}
	}else{
		print_page("Unable to comply.","You cannot destroy this planet, as you do not own it, nor does a clan-mate.");
	}
} elseif(isset($claim)) {

	//db("select * from ${db_name}_planets where planet_id = '$user[on_planet]'");
	//$planet = dbr();

	if($planet['login_id'] == $user['login_id']) {
		$output_str .= "<br>You can't claim a planet from yourself.<p>";
	} elseif(($user['clan_id'] == $planet['clan_id'] || $planet['fighters'] != 0) && $user['joined_game'] > (time() - ($min_before_transfer * 86400))) {
		$output_str .= "<br>You can not transfer a planet before the min_before_transfer time is up.<p>";
	} elseif($planet['research_fac'] > 0 && $flag_research == 1 && $sure != 'yes') {
		get_var('Claim planet','planet.php','<b class=b1>Warning.</b><br>Claiming this planet will result in the destruction of the research centre by its present occupants.<br><br>Are you sure you want to claim this planet and have the research centre destroyed in the process?','sure','yes');
	} else {
		send_message($planet['login_id'],"<b class=b1>$user[login_name]</b> claimed the planet <b class=b1>$planet[planet_name]</b> from you.");
		post_news("<b class=b1>$user[login_name]</b> has claimed the planet <b class=b1>$planet[planet_name]</b>.");
		dbn("update ${db_name}_planets set clan_id = '$user[clan_id]', login_id = '$user[login_id]', login_name = '$user[login_name]', research_fac = 0, pass = 0 where planet_id = '$planet[planet_id]'");
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
	db("select count(ship_id), sum(cargo_bays-metal-fuel-elect-organ-colon) from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-organ-colon) > 0");
	$ship_count = dbr();
	$colonist_cap = $ship_count[1];			#total cargo capacity of fleet in system
	$colonist = $ship_count[1];				#total cargo capacity of fleet in system
	$ship_count = $ship_count[0];			#number of ships in system that have cargo capacity
	db("select ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && config REGEXP 'ws'");					#ensure there is a transverser with the ws upgrade
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
	} elseif($user['turns'] < 2) {#ensure there is some cargo cap
		$output_str.= "Your turn count is less than 2. Thats not going to get you anywere and back!";
	} elseif(!isset($lead['ship_id'])) { #ensure there is a transverer with the ws upgrade
		$output_str .= "You do not have a Transverser in this system that has the <b>Wormhole Stabiliser</b> Upgrade. <br>This is required to allow for Autoshifting of anything.<p>";
	} elseif(!isset($dest_system)){ #get the user to select a system from where the colonists are to come from
		$new_page = "Please select a planet from which the $text_mat are going to come:";
		db2("select planet_id,planet_name from ${db_name}_planets where login_id = '$user[login_id]' && location != '$user[location]' && ((colon - (alloc_fight + alloc_organ + alloc_elect) > 0 && $autoshift = 1) || ($autoshift > 1 && $tech_mat > 0))"); #gets users planet other than ones in the present system.
		$other_sys = dbr2();

		if(!isset($other_sys['planet_id']) && $autoshift > 1){ #determine if there is a suitable target.
			$output_str .= "You have no planets with a supply of $text_mat.<p>";
		} else {
			$new_page .= "<form action=planet.php method=POST name=autoshifting>";
			$new_page .= "<select name=dest_system>";

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
			$new_page .= "<p><INPUT type=submit value='Submit'></form>";
			print_page("Autoshift",$new_page);
		}
	} else { #user has selected destination.


		if($dest_system == -1){ #user is getting the colonists from Sol. Thus needs to pay, and there is an infinite source.
			$turn = round(get_star_dist($user['location'], 1) / 2 + 1) * 2;	#do maths to work out turn cost to get there
			if($user[turns] < $turn) { #ensure user has enough turns to get there
				$output_str .= "You do not have enough turns.<br>It requires <b>$turn</b> turns just to get to <b class=b1>Sol</b> and back. Thats before ship loading.<p>";
			} else { #main autoshifting bit for taking colonists from Sol
				#determine if player can afford the costs, and if they can, then do the processing
				$c_c = $cost_colonist * $colonist;
				if($user[cash] < $c_c || $user[turns] < $turn + $ship_count){
					if($cost_colonist > 0){
						$colonist = floor($user[cash] / $cost_colonist);
					}
					if($colonist_cap > $colonist || $user[turns] < $turn + $ship_count){
						$free_turns = $user[turns] - $turn;
						$bays_used = 0;
						$count_quick = 0;

						db2("select sum(cargo_bays-metal-fuel-elect-organ-colon),ship_id from ${db_name}_ships where login_id = '$user[login_id]' && location = '$user[location]' && (cargo_bays-metal-fuel-elect-organ-colon) > 0 group by ship_id order by (cargo_bays-metal-fuel-elect-organ-colon) desc");
						$quick_ship = dbr2();
						while($quick_ship && $bays_used < $colonist && $free_turns > $count_quick){
							$bays_used += $quick_ship[0];
							$count_quick++;
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
					<br><b>$ship_count</b> ship(s).
					<br><b>$colonist</b> Total Colonist Capacity.
					<p>From <b class=b1>Sol</b>(<b>#1</b>) to the planet <b class=b1>$planet[planet_name]</b>(#<b>$planet[location]</b>).
					<p>At a total cost of <b>$turn</b> turns and <b>$c_c</b> Credits?",'sure','');
				} else { #update the game cos the user does want to do the autoshifting.
					dbn("update ${db_name}_planets set colon = colon + '$colonist' where planet_id = '$planet_id'");
					charge_turns($turn);
					take_cash($c_c);
					$output_str .= "Transportation of <b>$colonists</b> Colonists Complete.<p>";
				}
			}

		} else { #user is getting the materials from a system other than Sol. Thus different maths and stuff needs to be done as there is a finite number of materials, but no cash cost.

			db("select location,login_id,planet_name,$tech_mat,planet_id,alloc_fight,alloc_elect,alloc_organ from ${db_name}_planets where planet_id = '$dest_system'");
			$from_sys=dbr();
			$turn = round(get_star_dist($user['location'],$from_sys['location'])/1.8 +1)*2; #work out turn cost
			#echo $turns_can_use = floor(($user['turns']- $turn) * 1.35);
			if($user['turns'] < $turn) { #ensure user has enough turns to get there
				$output_str .= "You do not have enough turns.<br>It requires <b>$turn</b> turns to get to <b class=b1>$from_sys[planet_name](#<b>$from_sys[location]</b>)</b> and back.<p>";
			} elseif(!isset($from_sys)){
				$output_str .= "That planet is not a viable target.<p>";
			} elseif($from_sys['login_id'] != $user['login_id']){
				$output_str .= "An aspiring pirate I see. <br>That planet is not yours to take from.<p>";
			} elseif(($from_sys['colon'] - ($from_sys['alloc_elect'] + $from_sys['alloc_fight'] + 	$from_sys['alloc_organ'])) < 1 && $autoshift == 1){
				$output_str .= "The planet you are getting the colonists from does not have any colonists available to transport.<br>It is only possible to transport Idle colonists.<p>";
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
					dbn("update ${db_name}_planets set $tech_mat = $tech_mat + '$col_to_take' where planet_id = '$planet_id'");				#give goods to recieving planet
					dbn("update ${db_name}_planets set $tech_mat = $tech_mat - '$col_to_take' where planet_id = '$from_sys[planet_id]'");	#take goods from sending planet.
					charge_turns($turn);
					$output_str .= "Transportation of <b>$col_to_take</b> $text_mat Complete.<p>";
				}
			}
		}
	}

#take or leave a physical resource using 1 ship.
} elseif(isset($mineral_alloc)){

	if(conditions($user,$planet)) { #ensure user is allowed to play with this sort of stuff.
		$output_str .= "Metal may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} else {

		#ensure all are rounded & valid
		$process['fighters'] = round($set_fighters);
		$process['colon'] = round($set_colon);
		$process['metal'] = round($set_metal);
		$process['fuel'] = round($set_fuel);
		$process['elect'] = round($set_elect);
		$process['organ'] = round($set_organ);

		foreach($process as $key => $set_to){
			if ($set_to >= 0 && $set_to != $planet[$key] && (($user_ship['max_fighters'] > 0 && $key == "fighters") || ($user_ship['cargo_bays'] > 0 && $key != "fighters"))) { #ensure valid for continuation to avoid wasting processing time/power

				$old_ent = $planet[$key]; #to ensure a user only gets charged for things that are actually changed.

				if($user['turns'] < 1){
					$output_str .= "You need 1 turn per resource you are planning on transfering (metal, fuel, elect, colon, organ, fighters).";
				} else {
					if($set_to > $user_ship[$key] + $planet[$key]){ #ensure user doesn't go over the limit.
						$set_to = $user_ship[$key] + $planet[$key];
					}

					if($set_to > $planet[$key]){ #user putting onto planet.
						$take_from_user = $set_to - $planet[$key];
						$user_ship[$key] -= $take_from_user;
						dbn("update ${db_name}_ships set $key = $key - '$take_from_user' where ship_id = $user_ship[ship_id]");
						$planet[$key] = $set_to;
						dbn("update ${db_name}_planets set $key = '$set_to' where planet_id = $user[on_planet]");
					} else { #taking from planet.

						$give_to_user = $planet[$key] - $set_to; #ensure no ship limits are broken.
						if($give_to_user > $user_ship['max_fighters'] && $key == "fighters"){
							$give_to_user = $user_ship['max_fighters'] - $user_ship['fighters'];
						} elseif($give_to_user > empty_bays($user_ship) && $key != "fighters"){
							$give_to_user = empty_bays($user_ship);
						}
						$set_to = $planet[$key] - $give_to_user;

						$user_ship[$key] += $give_to_user;
						dbn("update ${db_name}_ships set $key = $key + '$give_to_user' where ship_id = $user_ship[ship_id]");
						$planet[$key] = $set_to;
						dbn("update ${db_name}_planets set $key = '$set_to' where planet_id = $user[on_planet]");
					}
					empty_bays($user_ship); #ensure things are kept up to date.

					if($old_ent != $set_to){ #charge the user 1 turn per resource transfered.
						charge_turns(1);
						if($type == 1 && $set_to < $old_ent){#if colonists have been messed with.
							$planet['alloc_fight'] = 0;
							$planet['alloc_elect'] = 0;
							$planet['alloc_organ'] = 0;
							dbn("update ${db_name}_planets set alloc_fight = 0, alloc_elect=0, alloc_organ=0 where planet_id = '$user[on_planet]'");
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
			if($def > ($user_ship[max_shields] - $user_ship[shields])) {
				$def = $user_ship[max_shields] - $user_ship[shields];
			}
			get_var('Charge Shields','planet.php','How many shield charges do you want to take?','amount',$def);
		} else {
			if($amount > $planet[shield_charge]) { // has on planet
				$output_str .= "There are not <b>$amount</b> Shield Charges on this planet.<p>";
			} elseif($amount > ($user_ship[max_shields] - $user_ship[shields])) { // can have that many
				$output_str .= "Your ship can not hold that many Shields.<p>";
			} else {
				dbn("update ${db_name}_planets set shield_charge = shield_charge - $amount where planet_id = $user[on_planet]");
				dbn("update ${db_name}_ships set shields = shields + $amount where ship_id = $user[ship_id]");
				$user_ship[shields] += $amount;
			}
		}
	} elseif($shield == 1) { // Leave
	if(conditions($user,$planet)) {
		$output_str .= "Shields may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($amount < 1) {
			$def = $user_ship[shields];
			if($def > ($planet[shield_gen] * 1000) - $planet[shield_charge]) {
				$def = ($planet[shield_gen] * 1000) - $planet[shield_charge];
			}
			get_var('Leave shields','planet.php','How many shields do you want to leave?','amount',$def);
		} else {
			if($amount > $user_ship[shields]) { // has on ship
				$output_str .= "There is not <b>$amount</b> shields on your ship.<p>";
		} elseif(($amount + $planet[shield_charge]) > ($planet[shield_gen] * 1000)) { // no more space on planet
		} else {
				dbn("update ${db_name}_planets set shield_charge = shield_charge + $amount where planet_id = $user[on_planet]");
				dbn("update ${db_name}_ships set shields = shields - $amount where ship_id = $user[ship_id]");
				$user_ship[shields] -= $amount;
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
		db("select sum($tech_mat) as goods, count(ship_id) as ship_count from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && $tech_mat > 0");
		$results = dbr();

		$turn_cost = ceil($results['ship_count'] * 0.75);

		if(!isset($results['goods'])) { #ships empty?
			$output_str .=  "You have no <b class=b1>$text_mat</b> in any ships in this system.";
		} elseif($user[turns] < $turn_cost) { #enough turns?
			$output_str .= "It will cost <b>$turn_cost</b> turns to perform this action.<br>At present you do not posses that many turns.";
			unset($results);
		} elseif(conditions($user,$planet)) { #check to see if been in game for long enough
			$output_str .= "$text_mat may not be left on this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
		} elseif($sure != "yes") { #confirmation
			get_var("Leave all $text_mat",'planet.php',"Are you sure you want to leave all the <b class=b1>$text_mat</b>(<b>$results[goods]</b>) from the <b>$results[ship_count]</b> ships with it on in this system onto the planet below, at a cost of <b>$turn_cost</b> turns?",'sure','yes');
		} else {
			dbn("update ${db_name}_planets set $tech_mat = $tech_mat + '$results[goods]' where planet_id = '$user[on_planet]'");
			dbn("update ${db_name}_ships set $tech_mat = 0 where login_id = '$user[login_id]' && location = '$planet_loc' && $tech_mat > 0");
			charge_turns($turn_cost);
			$user_ship[$tech_mat] = 0;
			empty_bays($user_ship);
			$output_str .= "<b>$results[ship_count]</b> ships unloaded at a cost of <b>$turn_cost</b> turns.";

			db("select * from ${db_name}_planets where planet_id = '$planet_id'");
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
			db2("select ship_id,(max_fighters - fighters) as free,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && (max_fighters - fighters) > 0 order by free desc");
		} elseif($type != 0) {
			db2("select ship_id, (cargo_bays - metal - fuel - elect - organ - colon) as free, ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && (cargo_bays - metal - fuel - elect - organ - colon) > 0 order by free desc");
		}

		$ships = dbr2();

		if(!isset($ships[ship_id])){ #check to see if there are any ships
			$output_str.= "There are no ships that have any space free for <b class=b1>$text_mat</b>.";
		} else {

			while($ships) {
				//planet can load ship w/ spare fighters maybe.
				if($ships['free'] < ($planet[$tech_mat] - $taken)) {
					$ship_counter++;
					if($sure == "yes"){ #only run during the real thing.
						dbn("update ${db_name}_ships set $tech_mat = $tech_mat + $ships[free] where ship_id = '$ships[ship_id]'");
						$out .= "<br><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$ships[free]</b> <b class=b1>$text_mat</b> to maximum capacity.";
						if($ships['ship_id'] == $user_ship['ship_id'] && $type == 0){ #update user ship
							$user_ship['fighters'] = $user_ship['max_fighters'];
						} elseif($ships['ship_id'] == $user_ship['ship_id'] && $type > 0){ #update user ship
							$user_ship[$tech_mat] += $ships['free'];
							$user_ship['empty_bays'] -= $ships['free'];
						}
					}
					$taken += $ships['free'];

					#ensure user has enough turns, or stop the loop where the user is.
					if($user['turns'] == ceil($ship_counter * 0.75)){
						$turns_txt = "You do not have enough turns to load your whole fleet.<br>But you do have enough to perform this limited action:<p>";
						$out .=  "<br>You did not have enough turns to continue with the operations any further.";
						unset($ships);
						break;
					}

				//planet will run out of fighters.
				} elseif($ships['free'] >= ($planet[$tech_mat] - $taken)) {
					$ship_counter++;
					$t868 = $ships[$tech_mat] + ($planet[$tech_mat] - $taken);
					if($sure == "yes"){ #only run during the real thing.
						dbn("update ${db_name}_ships set $tech_mat = '$t868' where ship_id = '$ships[ship_id]'");

						$out .= "<br><b class=b1>$ships[ship_name]</b>s bays were supplemented by <b>$t868</b> <b class=b1>$text_mat</b>";

						if($ships['ship_id'] == $user_ship['ship_id'] && $type == 0){ #update user ship
							$user_ship['fighters'] = $t868;
						}elseif($ships['ship_id'] == $user_ship['ship_id'] && $type > 0){ #update user ship
							$user_ship[$tech_mat] = $t868;
							empty_bays($user_ship);
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
			} elseif($sure != "yes") {
				get_var('Load all ships','planet.php',$turns_txt."Are you sure you want to load <b>$ship_counter</b> ships in this system with <b>$taken</b> <b class=b1>$text_mat</b> at a cost of about <b>$turn_cost</b> turns?",'sure','yes');
			} else {
				dbn("update ${db_name}_planets set $tech_mat = $tech_mat - $taken where planet_id = '$user[on_planet]'");
				charge_turns($turn_cost);

				if($type == 1){ #colonists
					$planet['alloc_fight'] = 0;
					$planet['alloc_elect'] = 0;
					$planet['alloc_organ'] = 0;
					dbn("update ${db_name}_planets set alloc_fight = 0, alloc_elect=0, alloc_organ=0 where planet_id = '$user[on_planet]'");
					$out .= "<p>As you took away some colonists, any remaining colonists assigned to production duties have become idle.";
				}

				print_page("$text_mat Loaded","<b>$ship_counter</b> ships had their bays augmented by a total of <b>$taken</b> <b class=b1>$text_mat</b> from the planet <b class=b1>$planet[planet_name]</b>:<br>".$out."<p>The total turn cost was <b>$turn_cost</b>.");
			}
		}
	}


} elseif(isset($all_shield)) { // Charge all shields on all ships in system.
	$taken = 0; //Shields taken from planet so far.
	$ship_counter = 0;
	if($sure != "yes") {
		get_var('Charge all ships','planet.php',"Are you sure you want to charge all the Ships in this system with <b class=b1>shields</b>?",'sure','yes');
	} elseif(conditions($user,$planet)) {
		$output_str .= "Shields may not be taken from this planet by you, until the min_before_transfer time has passed (<b>$min_before_transfer</b> days).<p>";
	} elseif($user[turns] < 3) {
		print_page("Error","You need <b>3</b> turns to charge all ships in a system.");
	} elseif($planet[shield_charge] < 1) {
		print_page("Error","This planet has no shield charges on it.");
	} else {
		db2("select ship_id,shields,max_shields,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '$planet_loc' && max_shields > 0 && shields < max_shields");
		while($ships = dbr2()) {
			//planet can charge ship w/ spare shields maybe.
			$free = $ships[max_shields] - $ships[shields];
			if($free <= ($planet[shield_charge] - $taken)) {
				$ship_counter++;
				dbn("update ${db_name}_ships set shields = max_shields where ship_id = '$ships[ship_id]'");
				$out .= "<br><b class=b1>$ships[ship_name]</b> had its shields increased by <b>$free</b> to full.";
				if($ships[ship_id] == $user_ship[ship_id]){
					$user_ship[shields] = $user_ship[max_shields];
				}
				$taken += $free;
			//planet will run out of shields.
			} elseif($free >= ($planet[shield_charge] - $taken)) {
				$ship_counter++;
				$t868 = $ships[shields] + ($planet[shield_charge] - $taken);
				dbn("update ${db_name}_ships set shields = '$t868' where ship_id = '$ships[ship_id]'");
				if($ships[ship_id] == $user_ship[ship_id]){
					$user_ship[shields] = $t868;
				}
				$taken += $t868 - $ships[shields];
				$out .= "<br><b class=b1>$ships[ship_name]</b>s shields were charged to <b>$t868</b> shields.";
				break;
			}
			if(($planet[shield_charge] - $taken) < 1){
				break;
			}
		}
		dbn("update ${db_name}_planets set shield_charge = shield_charge - $taken where planet_id = '$user[on_planet]'");
		if($ship_counter > 0){
				charge_turns(3);
			print_page("Shields Charged","<b>$ship_counter</b> ships had their shields charged by the planet <b class=b1>$planet[planet_name]</b>:<br>".$out);
		} else {
			print_page("No Ships","No ships where charged as all ships in this system have full shields.");
		}
	}

//assign colonists
} elseif(isset($assinging)) {
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
		dbn("update ${db_name}_planets set alloc_fight = $num_pop_set_1 where planet_id = $user[on_planet]");
	}

	if($num_pop_set_2 >= 0 && $num_pop_set_2 != $planet['alloc_elect']) { // Electronics
		if($num_pop_set_2 > idle_colonists() + $planet['alloc_elect']){ #ensure user doesn't go over the limit.
			$num_pop_set_2 = idle_colonists() + $planet['alloc_elect'];
		}
		$planet['alloc_elect'] = $num_pop_set_2;
		dbn("update ${db_name}_planets set alloc_elect = $num_pop_set_2 where planet_id = $user[on_planet]");
	}

	if($num_pop_set_3 >= 0 && $num_pop_set_3 != $planet['alloc_organ']) { // Organics
		if($num_pop_set_3 > idle_colonists() + $planet['alloc_organ']){ #ensure user doesn't go over the limit.
			$num_pop_set_3 = idle_colonists() + $planet['alloc_organ'];
		}
		$planet['alloc_organ'] = $num_pop_set_2;
		dbn("update ${db_name}_planets set alloc_organ = $num_pop_set_3 where planet_id = $user[on_planet]");

	}

	if($set_tax_rate >= 0 && $set_tax_rate <= 20) { // tax rate within boundaries
		$planet['tax_rate'] = $set_tax_rate;
		dbn("update ${db_name}_planets set tax_rate = $set_tax_rate where planet_id = $user[on_planet]");
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
			take_cash($take_from_user);
			$planet['cash'] = $set_cash;
			dbn("update ${db_name}_planets set cash = $set_cash where planet_id = $user[on_planet]");
		} else { #taking money from planet.
			$give_to_user = $planet['cash'] - $set_cash;
			give_cash($give_to_user);
			$planet['cash'] = $set_cash;
			dbn("update ${db_name}_planets set cash = $set_cash where planet_id = $user[on_planet]");
		}
	}

	if(isset($set_tech) && $flag_research != 0){ #tech units
		$set_tech = round($set_tech);
		settype($set_tech, "integer");

		if($set_tech >= 0 && $set_tech != $planet['tech']){
			if($set_tech > $user['tech'] + $planet['tech']){ #ensure user doesn't go over the limit.
				$set_tech = $user['tech'] + $planet['tech'];
			}

			if($set_tech > $planet['tech']){ #user putting money onto planet.
				$take_from_user = $set_tech - $planet['tech'];
				take_tech($take_from_user);
				$planet['tech'] = $set_tech;
				dbn("update ${db_name}_planets set tech = $set_tech where planet_id = $user[on_planet]");
			} else { #taking money from planet.
				$give_to_user = $planet['tech'] - $set_tech;
				give_tech($give_to_user);
				$planet['tech'] = $set_tech;
				dbn("update ${db_name}_planets set tech = $set_tech where planet_id = $user[on_planet]");
			}
		}
	}

} elseif(isset($set_rep) && $set_rep >=0 && $set_rep <= 2) {
	dbn("update ${db_name}_planets set daily_report = '$set_rep' where planet_id = $user[on_planet]");
	$planet['daily_report'] = $set_rep;

} elseif(isset($mil) && $mil == 1){ #offensive/defensive planet.

	$flag = 1;

	db("select login_id from ${db_name}_ships where location = '$planet[location]' && (clan_id != '$planet[clan_id]' && clan_id > 0) && login_id != '$user[login_id]'");
	$ships = dbr();
	db(attack_planet_check($db_name,$user));
	$planet_check = dbr();

	if($ships || $planet_check){
		$flag = 0;
	}

	if($flag) {
		dbn("update ${db_name}_planets set fighter_set = $mil where planet_id = $user[on_planet]");
		$planet['fighter_set'] = 1;
	} else {
		$output_str .= "Planet unable to go into attack mode with enemy ships in star system, or with an enemy hostile planet in the system.<p>";
	}
} elseif(isset($mil) && $mil == 0) {
	dbn("update ${db_name}_planets set fighter_set = $mil where planet_id = $user[on_planet]");
	$planet['fighter_set'] = 0;
}

if(isset($rename)){
	if($user['login_id'] != $planet['login_id']){
		$text .= "This planet is not yours to re-name.";
	} elseif($name_to) {
		$name_to = correct_name($name_to);
		if(!$name_to || strlen($name_to) < 3) {
			$rs = "<p><a href=javascript:history.back()>Try Again</a>";
			print_page("Invalid Name","That is not a valid name. Must have more than three characters.");
		}
		#$stuff = addslashes($name_to);
		#echo eregi_replace("'","",$name_to);
		$text .= "Planet re-named from <b class=b1>$planet[planet_name]</b> to <b class=b1>$name_to</b>.";
		dbn("update ${db_name}_planets set planet_name = '$name_to' where planet_id = '$planet[planet_id]'");
		post_news("<b class=b1>$user[login_name]</b> renamed the planet <b class=b1>$planet[planet_name]</b> to <b class=b1>$name_to</b>.");
	} else {
		$text .= "Enter a new name for the planet (30 Characters Max):";
		$text .= "<FORM method=POST action=planet.php>";
		$text .= "<input type=text name=name_to size=30 value=\"$planet[planet_name]\">";
		$text .= "<input type=hidden name=rename value=1>";
		$text .= "<input type=hidden name=planet_id value=$planet[planet_id]>";
		$text .= "<p><INPUT type=submit value=Rename></form><p>";
	}
	print_page("Rename Planet",$text);
}


#put any messages into a "message" box.
$messages = $output_str;

#sets the largest span distance. Allows for quicker manipulation of the page.
$span_dist = 2;

$output_str = "<table width=90%>";
$r_name_txt = "";

#determine if user can re-name planet.
if($has_pass == 1) {
	$r_name_txt .= " - <a href=planet.php?planet_id=$planet_id&rename=1>Rename</a>";
}

// begin printing of page
$output_str .= quick_row("Planet Name","<b class=b1>$planet[planet_name]</b>$r_name_txt");


#print no name if the owner has none
if($planet['login_id'] == 0){
	$output_str.= quick_row("Owner by","No-one");
} else {
	$output_str .= quick_row("Owned by","Owned by: <b class=b1>".print_name(array('login_id' =>$planet['login_id']))."</b>");
}


#show the planetary picture
if($user_options['show_pics']){
	$output_str .= "<tr><td colspan=$span_dist><center><img src=img/planets/".$planet['planet_img'].".jpg border=0 alt='Image of the planet'></center></td></tr>";
}

if(!empty($messages)){
	$output_str.= "<tr><td colspan=$span_dist><center>$messages</center></td></tr>";
}
$output_str.= "</table><p>";

#players may only view planetary data when they have the planet as their own.
if($has_pass == 1) {

	$output_str .= "<table>";

	if(empty($planet['pass'])) {
		$temp_str = "";
		if($user['login_id'] == $planet['login_id']){
			$temp_str .= "<a href='planet.php?change_pass=1&planet_id=$planet_id'>Set one</a>";
		}
		$output_str .= quick_row("No Password",$temp_str);
	} elseif($user['login_id'] == $planet['login_id']) { //only the owner may change the password
		$output_str .= quick_row("Password Set to '$planet[pass]'","<a href='planet.php?change_pass=1&planet_id=$planet_id'>Change it.</a>");
	} else {
		$output_str .= quick_row("Password is $planet[pass]","");
	}

	#show the claim link for clan mates.
	if($planet['login_id'] != $user['login_id']){
		$output_str .= quick_row("<a href=planet.php?planet_id=$planet_id&claim=1>Claim $planet[planet_name]</a>","");
	}

	$output_str .= "</table><p><br>Monetary<table><form name=monetary_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id'><input type=hidden name=monetary value='1'>";

	$output_str .= quick_row("Planet Cash","<input type=text name=set_cash value=$planet[cash] size=8>");

	if($flag_research != 0 && $planet['research_fac'] > 0){
			$output_str .= quick_row("Planet Tech. Units","<input type=text name=set_tech value=$planet[tech] size=8>");
	}
	$output_str .="</table><input type=submit value=Change></form>";


	$output_str .= "<p><br>Physical Goods<table><form name=mineral_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id'><input type=hidden name=mineral_alloc value='1'>";

	$fig_str = "";
	#determine fighter status
	if($planet['fighter_set'] == 0) {
		$fig_str .= "<a href=planet.php?planet_id=$planet_id&mil=1>Presently Passive</a>";
	} else {
		$fig_str .= "<a href=planet.php?planet_id=$planet_id&mil=0>Presently Hostile</a>";
	}

	$output_str .= make_row(array("Fighters","<input type=text name=set_fighters value='$planet[fighters]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=0>Load Fleet</a>","<a href=planet.php?planet_id=$planet_id&do_all=2&type=0>Empty Fleet</a>",$fig_str));

	$output_str .= make_row(array("Colonists","<input type=text name=set_colon value='$planet[colon]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=1>Load Fleet</a>","<a href=planet.php?planet_id=$planet_id&do_all=2&type=1>Empty Fleet</a>","<a href=planet.php?planet_id=$planet_id&autoshift=1>AutoShift</a>"));

	$output_str .= make_row(array("Metal","<input type=text name=set_metal value='$planet[metal]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=2>Load Fleet</a>","<a href=planet.php?planet_id=$planet_id&do_all=2&type=2>Empty Fleet</a>","<a href=planet.php?planet_id=$planet_id&autoshift=2>AutoShift</a>"));

	$output_str .= make_row(array("Fuel","<input type=text name=set_fuel value='$planet[fuel]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=3>Load Fleet</a>","<a href=planet.php?planet_id=$planet_id&do_all=2&type=3>Empty Fleet</a>","<a href=planet.php?planet_id=$planet_id&autoshift=3>AutoShift</a>"));

	$output_str .= make_row(array("Electronics","<input type=text name=set_elect value='$planet[elect]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=4>Load Fleet</a>","<a href=planet.php?planet_id=$planet_id&do_all=2&type=4>Empty Fleet</a>","<a href=planet.php?planet_id=$planet_id&autoshift=4>AutoShift</a>"));

	$output_str .= make_row(array("Organics","<input type=text name=set_organ value='$planet[organ]' size=8>","<a href=planet.php?planet_id=$planet_id&do_all=1&type=5>Load Fleet</a>","<a href=planet.php?planet_id=$planet_id&do_all=2&type=5>Empty Fleet</a>","<a href=planet.php?planet_id=$planet_id&autoshift=5>AutoShift</a>"));

	//$output_str .= " - <a href=planet.php?planet_id=$planet_id&fighters=2>Build</a>";
	$output_str .= "<tr><td><input type=submit value=Set></td><td><input type=reset value=Reset></td></tr></form></table><p><br><table>";



	$output_str .= "<tr><td>Colonist Allocation</tr></td><form name=pop_set_form method=post action=planet.php><input type=hidden name=planet_id value='$planet_id'><input type=hidden name=assinging value='1'>";
	$output_str .= quick_row("Tax Rate","<input type=text name=set_tax_rate value='$planet[tax_rate]' size=3>% (0 - 20%)");
	$output_str .= quick_row("To produce Fighters","<input type=text name=num_pop_set_1 value='$planet[alloc_fight]' size=6>");
	$output_str .= quick_row("To produce Electronics","<input type=text name=num_pop_set_2 value='$planet[alloc_elect]' size=6>");
	$output_str .= quick_row("To produce Organics","<input type=text name=num_pop_set_3 value='$planet[alloc_organ]' size=6>");
	$output_str .= quick_row("Idle Colonists",idle_colonists());
	$output_str.= "<tr><td><input type=submit value=Set></td><td><input type=reset value=Reset></td></tr></form>";

	$output_str .= "</table><br>";

	$output_str .= "<br>Send Daily Production Report: ";

	if($planet['daily_report'] == 0){
		$output_str.= "&lt;Never&gt; - ";
	} else {
		$output_str.= "&lt;<a href=planet.php?set_rep=0&planet_id=$planet_id>Never</a>&gt; - ";
	}
	if($planet['daily_report'] == 1){
		$output_str.= "&lt;If Planet Produces&gt; - ";
	} else {
		$output_str.= "&lt;<a href=planet.php?set_rep=1&planet_id=$planet_id>If Planet Produces</a>&gt; - ";
	}
	if($planet['daily_report'] == 2){
		$output_str.= "&lt;Always&gt;";
	} else {
		$output_str.= "&lt;<a href=planet.php?set_rep=2&planet_id=$planet_id>Always</a>&gt;";
	}

	#launch pad creation.
	if(!$planet['launch_pad']){
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&launch_pad=1>Build Missile Launch Pad</a> - <b>100000</b> Credits, 200 Electronics, 100 Metal, 100 Fuel";

	} elseif($planet['launch_pad'] -1 > 1){ #counting down
		$left = $planet['launch_pad'] -1;
		$output_str .= "<p><b>$left</b> hours until <b class=b1>Missile Launch Pad </b>is ready.";

	} elseif($planet['missile'] == 0){ #missle construction
		$output_str .= "<p>This planet has a <b class=b1>Missile Launch Pad</b>:";
		$output_str .= "<br><a href=add_planetary.php?planet_id=$planet_id&missile=1>Construct Omega Missile</a> - <b>100000</b> Credits, 50 Electronics, 200 Metal, 100 Fuel, 10 Turns";

	} else { #launch missile
		$output_str .= "<p>This planet has a <b class=b1>Missile Launch Pad</b> with <b>1</b> <b class=b1>Omega Missile<b>:";
		$output_str .= "<br><a href=add_planetary.php?planet_id=$planet_id&launch_missile=-1>LAUNCH Omega Missile</a>";
	}

	if ($flag_research != 0 && $planet['research_fac'] == 0) {
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&research_fac=1>Build Research Facility</a> - <b>$research_fac_cost</b>";
	}

	if(!$planet['shield_gen']){
		$output_str .= "<p><a href=add_planetary.php?planet_id=$planet_id&shield_gen=1>Build Shield Generator</a> - <b>$shield_gen_cost</b>";
	} else {
		$t545 = $planet['shield_gen']*1000;
		$output_str .= "<p>Shield Charges: <b>$planet[shield_charge]</b> / <b>$t545</b> - <a href=planet.php?planet_id=$planet_id&all_shield=1>Charge All</a>";
	}


	db("select * from ${db_name}_planets where planet_id = '$user[on_planet]'");
	$planet = dbr(1);

	if(($user['login_id'] == $planet['login_id'] || $user['clan_id'] == $planet['clan_id']) && ($uv_planets < 0 || $user['terra_imploder'] > 0)) {
		$output_str .= "<p><a href=planet.php?planet_id=$planet_id&destroy=1>Destroy $planet[planet_name]</a>";
	}


#only show the "claim" link to someone who doesn't own the planet.
} else {
	$output_str .= "<a href=planet.php?planet_id=$planet_id&claim=1>Claim $planet[planet_name]</a>";
}
$rs = "<p><a href=location.php>Takeoff</a><br>";

print_page("Planet",$output_str);
?>