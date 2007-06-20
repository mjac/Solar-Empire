<?php

require_once('inc/user.inc.php');

sudden_death_check($user);

#quark disrupter.
if(isset($quark)) {
//if($user[on_planet] != $planet_num) {
	db("select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$sv_turns = 30;

	if($user['turns'] < $sv_turns) {
		print_page("Quark Displacer","Sorry, you can't use the Quark Displacer, as you do not have enough turns. <br>To fire this weapon you need <b>$sv_turns</b> turns, and you only have <b>$user[turns]</b>.");
	} elseif($planet_info['planet_id'] == 1) {
		print_page("Quark Displacer","This weapon may not be fired at <b class=b1>Earth</b>. In fact by trying, you've probably broken a couple of laws. So scram.");
	} elseif($user['location'] != $planet_info['location']) {
		print_page("Quark Displacer","The planet <b class=b1>$planet_info[planet_name]</b> is not in this system.");
	} elseif($user['turns_run'] < $turns_before_planet_attack && $user['login_id'] != ADMIN_ID) {
		print_page("Quark Displacer","You can't attack during the first <b>$turns_before_planet_attack</b> turns of having your account.");
	} elseif($flag_planet_attack == 0) {
		print_page("Quark Displacer","The admin presenelty has planet attacking disabled.");
	} elseif($planet_info['login_id'] == 1) {
		print_page("Quark Displacer","This is an <b class=b1>Admin </b>planet, and cannot be attacked.");
	} elseif(!$planet_info['fighters']) {
		print_page("Quark Displacer","There is no point in attacking this planet with the Quark Displacer, as there are no fighters left on it to destroy.<p><a href=planet.php?planet_id=$planet_info[planet_id]>Land</a> on it, and then claim it to make it yours.");
	} elseif(!ereg("sv",$user_ship['config'])) {
		print_page("Quark Displacer","This ship does not have a Quark Displacer on it.");
	} elseif(!isset($sure)) {
		get_var('Quark Displacer','attack.php',"Are you sure you want to use the Quark Displacer against <b class=b1>$planet_info[planet_name]</b>?",'sure','yes');
	}	else {
		charge_turns($sv_turns);

		$sv_damage = mt_rand(600,1400);

	/*
		if($sv_damage > $planet_info[fighters]) {
			$sv_damage = $planet_info[fighters];
			}*/

		$out_str = "";

		dbn("update ${db_name}_planets set fighters = fighters - '$sv_damage' where planet_id = $planet_num");
		$planet_info['fighters'] -= $sv_damage;
		post_news("<b class=b1>$user[login_name]</b> fired a Quark Displacer at <b class=b1>$planet_info[planet_name]</b>.");
		if($planet_info['fighters'] < 1) {
			send_message($planet_info['login_id'],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Quark Displacer, doing <b>$sv_damage</b> damage, and completely destroying all the planets defences.");
			dbn("update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info['fighters'];
			$planet_info['fighters'] = 0;
			$out_str .= "You have done <b>$sv_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, using <b>$sv_turns</b> turns. <p>You completly destroyed all defences on <b class=b1>$planet_info[planet_name]</b>. <br> - <a href=planet.php?planet_id=$planet_info[planet_id]>Land</a><br>";
		} else {
			send_message($planet_info['login_id'],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Quark Displacer, doing <b>$sv_damage</b> damage. There was no way for your planetary defences to retaliate.");
			$f_killed = $sv_damage;
			$out_str .= "You have done <b>$sv_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, at a cost of <b>$sv_turns</b> turns";
		}
		dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[login_id]");
		print_page("Quark Disrupter",$out_str);
	}
}


#terra maelstrom
if(isset($terra)) {

//if($user[on_planet] != $planet_num) {
	db("select * from ${db_name}_planets where planet_id = '$planet_num'");
	$planet_info = dbr();
	$sw_turns = 50;
	$base_percent = 2;

	if($user['turns'] < $sw_turns) {
		print_page("Terra Maelstrom","Sorry, you can't use the Terra Maelstrom, as you do not have enough turns. <br>To fire this weapon you need at <b class=b1>least</b> <b>$sw_turns</b> turns. You have <b>$user[turns]</b>.");
	} elseif($planet_info['planet_id'] == 1) {
		print_page("Terra Maelstrom","Are you trying to start some trouble? Because I'm sure that the Navy of Sol is around here somewhere to put you right.");
	} elseif($user['location'] != $planet_info['location']) {
		print_page("Terra Maelstrom","The planet <b class=b1>$planet_info[planet_name]</b> is not in this system.");
	} elseif($user['turns_run'] < $turns_before_planet_attack && $user['login_id'] != ADMIN_ID) {
		print_page("Terra Maelstrom","You can't attack during the first <b>$turns_before_planet_attack</b> turns of having your account.");
	} elseif($flag_planet_attack == 0) {
		print_page("Quark Displacer","The admin presentlty has planet attacking disabled.");
	} elseif($planet_info['login_id'] == 1) {
		print_page("Terra Maelstrom","This is an <b class=b1>Admin</b> planet, and cannot be attacked.");
	} elseif(!$planet_info['fighters']) {
		print_page("Terra Maelstrom","There is no point in attacking this planet with the Terra Maelstrom, as there are no fighters left on it to destroy.<p><a href=planet.php?planet_id=$planet_info[planet_id]>Land</a> on it, and then claim it to make it yours.");
	} elseif($enable_superweapons == 0) {
		print_page("Terra Maelstrom","The admin has disabled the use of terra maelstroms.");
	} elseif(!ereg("sw",$user_ship['config'])) {
		print_page("Terra Maelstrom","'Ready aim...<br>oh wait a minute, we don't HAVE a Terra Maelstrom installed Captain!'");
	}	else {

		#base amount of damage, done for the X turns.
		$sq_damage = mt_rand(4000,6000);

		#if planet has more than that many fighters, use an alternate system:
		if($planet_info['fighters'] > $sq_damage && $user['turns'] > $sw_turns){

			#work out how many fighters may be killed in one shot (between 65 and 75 percent) as a max.
			$max_fig_kills = round(($planet_info['fighters'] / 100) * mt_rand(65,75));

			#work out based on the max fighters that can be killed the num of fighters killed per turn.
			$killed_per_turn = round($max_fig_kills / $max_turns);

			#damage done is based on num turns used times fighters killed per turn used. simple
			$damage_done = round($killed_per_turn * $user['turns']);

			#random factor. allows for an increased randomness in damage done.
			$damage_done += round(mt_rand(-$damage_done * .05,$damage_done * .05));

	#old method of doing damage with terra maelstrom
	#		$t_dam_done = round(($user[turns] - $sw_turns) / 10) + $base_percent;
	#		$damage_done = round($planet_info[fighters] /100 * ($base_percent + $t_dam_done));
	#		$damage_done += round(mt_rand(-$damage_done * .15,$damage_done * .15));
		}

		#damage done by alternate system isn't as much as using the sure fire method (fixed damage for fixed turns)
		if($sq_damage > $damage_done){
			$sw_damage = $sq_damage;
			$turn_cost = $sw_turns;
		} else { #damage done by alternate method is greater than normal damage done.
			$turn_cost = $user['turns'];
			$sw_damage = $damage_done;
		}

		if(!isset($sure)) {
			get_var('Terra Maelstrom','attack.php',"Are you sure you want to use the Terra Maelstrom against the planet <b class=b1>$planet_info[planet_name]</b>?<br><br>This will use <b>$turn_cost</b> turns and kill about <b>$damage_done</b> fighters.",'sure','yes');
		}

		charge_turns($turn_cost);
		$out_str ="";

		dbn("update ${db_name}_planets set fighters = fighters - '$sw_damage' where planet_id = $planet_num");
		$planet_info['fighters'] -= $sw_damage;
		post_news("<b class=b1>$user[login_name]</b> fired a Terra Maelstrom at <b class=b1>$planet_info[planet_name]</b>.");
		if($planet_info['fighters'] < 1) {
			send_message($planet_info['login_id'],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Terra Maelstrom, doing <b>$sw_damage</b> damage, and completely destroying all the planets defences.");
			dbn("update ${db_name}_planets set fighters = 0 where planet_id = $planet_num");
			$f_killed = $planet_info['fighters'];
			$planet_info['fighters'] = 0;
			$out_str .= "You have done <b>$sw_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, using <b>$turn_cost</b> turns. <p>You completly destroyed all defences on <b class=b1>$planet_info[planet_name]</b>. <br> - <a href=planet.php?planet_id=$planet_info[planet_id]>Land</a><br>";

		} else {
			send_message($planet_info['login_id'],"The <b class=b1>$user_ship[ship_name]</b> fired at your planet <b class=b1>$planet_info[planet_name]</b> with a Terra Maelstrom, doing <b>$sw_damage damage</b>. There was no way for your planetary defences to retaliate.");
			$f_killed = $sw_damage;
			$planet_info['fighters'] -= $sw_damage;
			$out_str .= "You have done <b>$sw_damage</b> damage to planet <b class=b1>$planet_info[planet_name]</b>, at a cost of <b>$turn_cost</b> turns";
		}
		dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$f_killed' where login_id = $user[login_id]");
		dbn("update ${db_name}_users set fighters_lost = fighters_lost + '$f_killed' where login_id = $planet_info[login_id]");
		print_page("Terra Maelstrom",$out_str);
	}
}

#==============
#ship to ship attacking
#==============

#determine if attacking is allowed, or attacking in sol is allowed.
if($flag_space_attack == 0) {
	print_page("Attack"," The Admin has disabled ship to ship attacks.");
} elseif($flag_sol_attack == 0 && $user[location] == 1 && $user[login_id] != 1){
	print_page("Attack","The Admin has disabled all forms of attack in the Sol System (system #<b>1</b>).");
}

if($target > 0) {
	// Get target records
	db("select * from ${db_name}_ships where ship_id = '$target'");
	$target_ship = dbr(1);
	db("select * from ${db_name}_users where login_id = '$target_ship[login_id]'");
	$target = dbr(1);

	db(attack_planet_check($db_name,$user));
	$planets = dbr(1);


	// check and make sure attack is possable
	if($user['turns'] < $space_attack_turn_cost) {
		$error_str = "Sorry, you can't attack because you have less than <b>$space_attack_turn_cost</b> turns to use.";
	} elseif(($user['location'] != $target_ship['location'])) {
		$error_str = "That ship is no longer there. Try again when you are in the same system as it.";
	} elseif(!empty($planets) && $user['login_id'] != ADMIN_ID) {
		$error_str = "Stop playing with the URL's. You may not attack a ship that is defended by a planet.";
	} elseif($user['turns_run'] < $turns_before_attack && $user['login_id'] != ADMIN_ID) {
		$error_str = " You can't attack during the first <b>$turns_before_attack</b> turns of having your account.";
	} elseif($target['turns_run'] < $turns_safe && $user['login_id'] != ADMIN_ID) {
		$error_str = " The owner of this still under the inital <b>$turns_safe</b> turns protection period.";
	} elseif(ereg("na",$user_ship['config'])) {
		$error_str = " Your ship doesn't have the ability to attack.";
	} elseif(ereg("po",$user_ship['config'])) {
		$error_str = " Your ship doesn't have the ability to attack other ships.";
	} elseif(ereg("hs",$target_ship['config']) && !ereg("sc",$user_ship['config']) && $user['login_id'] != ADMIN_ID) {
		$error_str = " You cannot attack a fully cloaked ship, without a scanner. These can be brought from the accessories & upgrade store on Earth.";
	} elseif($target['login_id'] == $user['login_id']) {
		$error_str = " You may not attack yourself.";
	} elseif($user['ship_id'] == NULL) {
		$error_str = " A <b class=b1>Ship Destroyed</b> may not attack, as it doesn't excist in the first place.";
	} elseif(($user['clan_id'] == $target['clan_id']) && $user['clan_id'] > 0) {
		$error_str = "You may not attack a clan member.";
	//} elseif(($user[nap_id] == $target[nap_id]) && $user[nap_id] > 0) {
	//	$error_str = "You may not attack a member of a clan you have a NAP with.";
	} elseif(isset($sure) != 'yes' && (ereg("hs",$target_ship['config']) || $user['login_id'] == ADMIN_ID)) {
		get_var('Attack','attack.php',"Are you sure you want to attack ".print_name($target)."?",'sure','yes');
	} elseif(!isset($sure) && ereg("hs",$target_ship['config'])) {
		get_var('Attack','attack.php',"Are you sure you want to attack a ship when you do not know who it's owner is?",'sure','yes');
	}else {

		#determine if there is a fleet defending ship in the system
		db("select * from ${db_name}_ships where login_id = '$target[login_id]' && location = '$user[location]' && defend_fleet = 1 order by fighters desc");
		$new_target_ship = dbr();

		if(!empty($new_target_ship['ship_id']) && $target_ship['ship_id'] != $new_target_ship['ship_id']){

			#a 1 in 10 chance the fleet defender won't be able to get to the ship to defend it in time.
			if(round(mt_rand(1,10)) != 2) {
				$target_ship = $new_target_ship;
				$def_str = "<br>The fleet defender <b class=b1>$new_target_ship[ship_name]</b>($new_target_ship[class_name]) spotted the incoming hostile ship and intercepted before battle had been joined.<br>";
			} else {
				$def_str = "<br>The <b class=b1>$new_target_ship[ship_name]</b>($new_target_ship[class_name]) was unable to reach the site of the fray before the battle was over.<br>";
				unset($new_target_ship);
			}
		}


		charge_turns($space_attack_turn_cost);

		#function to set stuff to 0.
		function leveller($input){
			if($input < 0){
				return 0;
			} else{
				return $input;
			}
		}

		#load attack, defense bonuses.
		$u_bonus = bonus_calc($user_ship);
		$t_bonus = bonus_calc($target_ship);

		$short_str = "<br><b class=b1>$user[login_name]</b> used the <b class=b1>$user_ship[ship_name]</b> ($user_ship[class_name]) to attack <b class=b1>$target[login_name]</b>'s <b class=b1>$target_ship[ship_name]</b> ($target_ship[class_name]) in system #<b>$user[location]</b>.";


		$tech_str = "Location of Combat: System <b>#$user[location]</b><br>Ship statistics";
		$tech_str .= make_table(array("","<b class=b1>".$user_ship['ship_name']."</b> (Attacker)","<b class=b1>".$target_ship['ship_name']."</b> (Defender)"));

		$tech_str .= make_row(array("<b class=b1>Owner</b>",$user_ship['login_name'],$target_ship['login_name']));
		$tech_str .= make_row(array("<b class=b1>Ship Type</b>",$user_ship['class_name'],$target_ship['class_name']));

		$tech_str .= make_row(array("<b class=b1>Fighters</b>",$user_ship['fighters'],$target_ship['fighters']));
		$tech_str .= make_row(array("<b class=b1>Shields</b>",$user_ship['shields'],$target_ship['shields']));

		#work out the damage capacity.
		$u_dam_cap = $user_ship['fighters'] + $user_ship['shields'];
		$t_dam_cap = $target_ship['fighters'] + $target_ship['shields'];


		#print a line for each defense the user has.
		if($user_ship['num_ew'] > 0 || $target_ship['num_ew'] > 0){
			$tech_str .= make_row(array("<b class=b1>Electronic Warfare Pods (EW)</b>",$user_ship['num_ew'],$target_ship['num_ew']));
		}

		if($user_ship['num_ot'] > 0 || $target_ship['num_ot'] > 0){
			$tech_str .= make_row(array("<b class=b1>Offensive LASER Turrets (OT)</b>",$user_ship['num_ot'],$target_ship['num_ot']));
		}

		if($user_ship['num_dt'] > 0 || $target_ship['num_dt'] > 0){
			$tech_str .= make_row(array("<b class=b1>Defensive LASER Turrets (DT)</b>",$user_ship['num_dt'],$target_ship['num_dt']));
		}

		if($user_ship['num_pc'] > 0 || $target_ship['num_pc'] > 0){
			$tech_str .= make_row(array("<b class=b1>Plasma Cannons (OT)</b>",$user_ship['num_pc'],$target_ship['num_pc']));
		}

		if($user_ship['num_sa'] > 0 || $target_ship['num_sa'] > 0){
			$tech_str .= make_row(array("<b class=b1>Silicon Armour Modules (SA)</b>",$user_ship['num_sa'],$target_ship['num_sa']));
			$u_dam_cap += $u_bonus['sa'];
			$t_dam_cap += $t_bonus['sa'];
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
		$t_d_sh = 0;
		$u_d_sh = 0;

		$t_dam = 0;
		$u_dam = 0;

		#should use the ?r_ship replicas of the orignals to work out whats left on the ship to eliminate.
		$tr_ship = $target_ship;
		$ur_ship = $user_ship;

		#electronic warfare pods cancel each other out
		if($user_ship['num_ew'] == $target_ship['num_ew'] && $user_ship['num_ew'] > 0){
			$tech_ew = "<br>Electronic Warfare modules cancelled each other out.";
			$t_bonus['ewa'] = 0;
			$t_bonus['ewd'] = 0;
			$u_bonus['ewa'] = 0;
			$u_bonus['ewd'] = 0;
		} elseif($user_ship['num_ew'] > $target_ship['num_ew'] && $user_ship['num_ew'] > 0){ #attacker has more EW pods
			$tech_ew = "<br>The <b class=b1>$user_ship[ship_name]'s</b> Electronic Warfare Modules overpowered the <b class=b1>$target_ship[ship_name]'s</b>.";

			$u_bonus['ewd'] -= $t_bonus['ewa'];
			$t_bonus['ewa'] = 0;
			$t_bonus['ewd'] = 0;

		} elseif($user_ship['num_ew'] < $target_ship['num_ew'] && $target_ship['num_ew'] > 0){ #defender has more EW pods
			$tech_ew = "<br>The <b class=b1>$target_ship[ship_name]'s</b> Electronic Warfare Modules overpowered the <b class=b1>$user_ship[ship_name]'s</b>.";
			$t_bonus['ewa'] -= $u_bonus['ewd'];
			$t_bonus['ewd'] -= $u_bonus['ewa'];
			$u_bonus['ewa'] = 0;
			$u_bonus['ewd'] = 0;
		}

		#ensures none of the EW bonuses go below 0.
		$u_bonus['ewa'] = leveller($u_bonus['ewa']);
		$u_bonus['ewd'] = leveller($u_bonus['ewd']);
		$t_bonus['ewa'] = leveller($t_bonus['ewa']);
		$t_bonus['ewd'] = leveller($t_bonus['ewd']);

		#offensive turret damage merged.
		$t_bonus['at'] = $t_bonus['ot'] + $t_bonus['pc'];
		$u_bonus['at'] = $u_bonus['ot'] + $u_bonus['pc'];

		#--------------
		# EW's defenses v's offensive turrets
		#--------------

		#defensives for attacker are greater than the defenders turrets can handle.
		if($u_bonus['ewd'] >= $t_bonus['at'] && $t_bonus['at'] > 0){
			#$out_str .= "<br>The <b class=b1>$user_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> nullify the <b class=b1>$target_ship['ship_name']'s</b> <b>Offensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of OT Damage Stopped</b>","All","None"));
			$u_bonus['ewd'] -= $t_bonus['at'];
			$t_bonus['at'] = 0;

		#Attacking turrets nullify the EW pods defensives.
		} elseif($u_bonus['ewd'] < $t_bonus['at'] && $u_bonus['ewd'] > 0){
			#$out_str .= "<br>The <b class=b1>$user_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> fail to stop the <b class=b1>$target_ship['ship_name']'s</b> <b>Offensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of OT Damage Stopped</b>","$u_bonus[ewd]","None"));
			$t_bonus['at'] -= $u_bonus['ewd'];
			$u_bonus['ewd'] = 0;

		#defensives for defender are greater than the attackers turrets can handle.
		} elseif($t_bonus['ewd'] >= $u_bonus['at'] && $u_bonus['at'] > 0){
			#$out_str .= "<br>The <b class=b1>$target_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> nullify the <b class=b1>$user_ship['ship_name']'s</b> <b>Offensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of OT Damage Stopped</b>","None","All"));
			$t_bonus['ewd'] -= $u_bonus['at'];
			$u_bonus['at'] = 0;

		#Attacking turrets nullify the EW pods defensives.
		} elseif($t_bonus['ewd'] < $u_bonus['at'] && $t_bonus['ewd'] > 0){
			#$out_str .= "<br>The <b class=b1>$target_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> fail to stop the <b class=b1>$user_ship['ship_name']'s</b> <b>Offensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of OT Damage Stopped</b>","None","$t_bonus[ewd]"));
			$u_bonus['at'] -= $t_bonus['ewd'];
			$t_bonus['ewd'] = 0;
		}

		#=============
		# EW attacks defensive turrets
		#=============


		#EW for attacker greater than the defenders turrets can handle.
		if($u_bonus['ewa'] >= $t_bonus['dt'] && $t_bonus['dt'] > 0){
			#$out_str .= "<br>The <b class=b1>$user_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> nullify the <b class=b1>$target_ship['ship_name']'s</b> <b>Defensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of DT Damage Stopped</b>","All","None"));
			$u_bonus['ewa'] -= $t_bonus['dt'];
			$t_bonus['dt'] = 0;

		#Defensive turrets nullify the EW pods attack.
		} elseif($u_bonus['ewa'] < $t_bonus['dt'] && $u_bonus['ewa'] > 0){
			#$out_str .= "<br>The <b class=b1>$user_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> fail to take out the <b class=b1>$target_ship['ship_name']'s</b> <b>Defensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of DT Damage Stopped</b>","$u_bonus[ewa]","None"));
			$t_bonus['dt'] -= $u_bonus['ewa'];
			$u_bonus['ewa'] = 0;

		#defensives for defender are greater than the attackers turrets can handle.
		} elseif($t_bonus['ewa'] >= $u_bonus['dt'] && $u_bonus['dt'] > 0){
			#$out_str .= "<br>The <b class=b1>$target_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> nullify the <b class=b1>$user_ship['ship_name']'s</b> <b>Defensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of DT Damage Stopped</b>","None","All"));
			$t_bonus['ewa'] -= $u_bonus['dt'];
			$u_bonus['dt'] = 0;

		#Attacking turrets nullify the EW pods defensives.
		} elseif($t_bonus['ewa'] < $u_bonus['dt'] && $t_bonus['ewa'] > 0){
			#$out_str .= "<br>The <b class=b1>$target_ship['ship_name']'s</b> <b>Electronic Warfare Pods</b> fail under the strain of trying to take out the <b class=b1>$user_ship['ship_name']'s</b> <b>Defensive Turrets</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Amount of DT Damage Stopped</b>","None","$u_bonus[ewa]"));
			$u_bonus['dt'] -= $t_bonus['ewa'];
			$t_bonus['ewa'] = 0;
		}


		#combine the offensive and defensive remnants to take out the enemy fighters.
		$u_bonus['ew'] = $u_bonus['ewa'] + $u_bonus['ewd'];
		$t_bonus['ew'] = $t_bonus['ewa'] + $t_bonus['ewd'];

		#========
		#EW attacks enemy fighters
		#========
		#EW takes out the defenders fighters
		if($u_bonus['ew'] >= $tr_ship['fighters'] && $tr_ship['fighters'] > 0){
			#$out_str .= "<br>The <b>Electronic Warfare Pods</b> of the <b class=b1>$user_ship['ship_name']</b> were successfully used to eliminate all <b>$tr_ship['fighters'] Fighters</b> from the <b class=b1>$target_ship['ship_name']</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Num. Fighters Destroyed</b>","All ($tr_ship[fighters])","None"));
			$t_d_fig += $tr_ship['fighters'];
			$u_bonus['ew'] -= $tr_ship['fighters'];
			$u_dam += $tr_ship['fighters'];
			$tr_ship['fighters'] = 0;

		#EW takes out some of the defenders fighters.
		} elseif($u_bonus['ew'] < $tr_ship['fighters'] && $u_bonus['ew'] > 0) {
			#$out_str .= "<br>The <b>Electronic Warfare Pods</b> of the <b class=b1>$user_ship['ship_name']</b> were successfully used to eliminate <b>$u_bonus['ew'] Fighters</b> from the <b class=b1>$target_ship['ship_name']</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Num. Fighters Destroyed</b>","$u_bonus[ew]","None"));
			$t_d_fig += $u_bonus['ew'];
			$u_dam += $u_bonus['ew'];
			$tr_ship['fighters'] -= $u_bonus['ew'];
			$u_bonus = 0;

		#EW takes out the attackers fighters
		} elseif($t_bonus['ew'] >= $ur_ship['fighters'] && $ur_ship['fighters'] > 0){
			#$out_str .= "<br>The <b>Electronic Warfare Pods</b> of the <b class=b1>$target_ship['ship_name']</b> were successfully used to eliminate <b>$ur_ship['fighters'] fighters</b> of the <b class=b1>$user_ship['ship_name']</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Num. Fighters Destroyed</b>","None","All ($ur_ship[fighters])"));
			$u_d_fig += $ur_ship['fighters'];
			$t_bonus['ew'] -= $ur_ship['fighters'];
			$t_dam += $ur_ship['fighters'];
			$ur_ship['fighters'] = 0;

		#EW takes out some of the attackers fighters.
		} elseif($t_bonus['ew'] < $ur_ship['fighters'] && $t_bonus['ew'] > 0) {
			#$out_str .= "<br>The <b>Electronic Warfare Pods</b> on the <b class=b1>$target_ship['ship_name']</b> were successfully used to eliminate <b>$t_bonus['ew']</b> of the <b>Fighters</b> from the <b class=b1>$user_ship['ship_name']</b>.";
			$tech_ew1 .= make_row(array("<b class=b1>Num. Fighters Destroyed</b>","None","$t_bonus[ew]"));
			$u_d_fig += $t_bonus['ew'];
			$ur_ship['fighters'] -= $t_bonus['ew'];
			$t_dam += $t_bonus['ew'];
			$t_bonus['ew'] = 0;
		}

		#==================
		#end of EW part of the attacking system
		#==================


		if(!empty($tech_ew1)){
			$tech_str .= "$tech_ew<br><br>Electronic Warfare Modules:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$target_ship['ship_name']."</b>")).$tech_ew1."</table>";
		} elseif(!empty($tech_ew)){
			$tech_str .= "<br>".$tech_ew;
		}


		#=======
		#start of defensive turret section
		#=======

		#attacking ship takes out defenders fighters with defensive turret.
		if($u_bonus['dt'] >= $tr_ship['fighters'] && $tr_ship['fighters'] > 0){
			#$out_str .= "<br>The <b>Defensive Turrets</b> on the <b class=b1>$user_ship['ship_name']</b> destroyed all <b>$tr_ship['fighters'] Fighters</b> from the <b class=b1>$target_ship['ship_name']</b>.";
			$u_bonus['dt'] -= $tr_ship['fighters'];
			$u_dt_d = $tr_ship['fighters'];
			$target_ship['fighters'] = 0;
			$tr_ship['fighters'] = 0;

		} elseif ($u_bonus['dt'] < $tr_ship['fighters'] && $u_bonus['dt'] > 0){ #attacker takes out some defensive fighters
			#$out_str .= "<br>The <b>Defensive Turrets</b> on the <b class=b1>$user_ship['ship_name']</b> destroyed <b>$u_bonus['dt'] Fighters</b> from the <b class=b1>$target_ship['ship_name']</b>.";
			$tr_ship['fighters'] -= $u_bonus['dt'];
			$target_ship['fighters'] -= $u_bonus['dt'];
			$u_dt_d = $u_bonus['dt'];
			$u_bonus['dt'] = 0;
		} else {
			$u_dt_d = 0;
		}

		#attacking ship takes out defenders fighters with defensive turret.
		if($t_bonus['dt'] >= $ur_ship['fighters'] && $ur_ship['fighters'] > 0){
			#$out_str .= "<br>The <b>Defensive Rurrets</b> on the <b class=b1>$target_ship['ship_name']</b> destroyed all <b>$ur_ship['fighters'] fighters</b> from the <b class=b1>$user_ship['ship_name']</b>.";
			$t_bonus['dt'] -= $ur_ship['fighters'];
			$t_dt_d = $ur_ship['fighters'];
			$ur_ship['fighters'] = 0;
			$user_ship['fighters'] = 0;
		} elseif ($t_bonus['dt'] < $ur_ship['fighters'] && $t_bonus['dt'] > 0){ #defender takes out some attacking fighters
			#$out_str .= "<br>The <b>Defensive Turrets</b> on the <b class=b1>$target_ship['ship_name']<b> destroyed <b>$t_bonus['dt'] Fighters</b> from the <b class=b1>$user_ship['ship_name']</b>.";
			$ur_ship['fighters'] -= $t_bonus['dt'];
			$user_ship['fighters'] -= $t_bonus['dt'];
			$t_dt_d = $t_bonus['dt'];
			$t_bonus['dt'] = 0;
		} else {
			$t_dt_d = 0;
		}

		if($u_dt_d > 0 || $t_dt_d > 0){
			$u_dam += $u_dt_d;
			$t_dam += $t_dt_d;
			$tech_str .= "<br><br>Defensive Turrets:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$target_ship['ship_name']."</b>")).make_row(array("<b class=b1>Num. Fighters Destroyed</b>","$u_dt_d","$t_dt_d"))."</table>";

			#update the database with fighter kills for dt.
			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$u_dt_d', fighters_lost = fighters_lost + '$t_dt_d' where login_id = '$user[login_id]'");

			dbn("update ${db_name}_users set fighters_killed = fighters_killed + '$t_dt_d', fighters_lost = fighters_lost + '$u_dt_d' where login_id = '$target[login_id]'");

		}


		#defensive turrets complete.



		#==========
		# offensive turrets v's armour
		#==========
		$usa_dead = 0;

		if($u_bonus['at'] >= $t_bonus['sa'] && $t_bonus['sa'] > 0){ #SA eliminated on defending ship
			#$out_str .= "<br>The <b>Offensive Turrets</b> on the <b class=b1>$user_ship['ship_name']</b> obliterated the <b>Silicon Armour</b> on the <b class=b1>$target_ship['ship_name']</b>.";
			$u_bonus['at'] -= $t_bonus['sa'];
			$u_pcsa_d = $t_bonus['sa'];
			$usa_dead = 1;
			$t_bonus['sa'] = 0;
		} elseif($u_bonus['at'] < $t_bonus['sa'] && $u_bonus['at'] > 0){ #turrets defended against on defending ship
			#$out_str .= "<br>The <b>Silicon Armour</b> on the <b class=b1>$target_ship['ship_name']</b> stopped the attacks from the <b>Offensive Turrets</b> on the <b class=b1>$user_ship['ship_name']</b>.";
			$t_bonus['sa'] -= $u_bonus['at'];
			$u_pcsa_d = $u_bonus['at'];
			$u_bonus['at'] = 0;
		} else {
			$u_pcsa_d = 0;
		}

		$tsa_dead = 0;

		if($t_bonus['at'] >= $u_bonus['sa'] && $u_bonus['sa'] > 0){ #SA eliminated on attacking ship
			#$out_str .= "<br>The <b>Offensive Turrets</b> on the <b class=b1>$target_ship['ship_name']</b> obliterated the <b>Silicon Armour</b> on the <b class=b1>$user_ship['ship_name']</b>.";
			$t_bonus['at'] -= $u_bonus['sa'];
			$t_pcsa_d = $u_bonus['sa'];
			$tsa_dead = 1;
			$u_bonus['sa'] = 0;
		} elseif($t_bonus['at'] < $u_bonus['sa'] && $t_bonus['at'] > 0){ #turrets defended against on attacking ship.
			#$out_str .= "<br>The <b>Silicon Armour</b> on the <b class=b1>$user_ship['ship_name']</b> stopped the attacks from the <b>Offensive Turrets</b> on the <b class=b1>$target_ship['ship_name']</b>.";
			$u_bonus['sa'] -= $t_bonus['at'];
			$t_pcsa_d = $t_bonus['at'];
			$t_bonus['at'] = 0;
		} else {
			$t_pcsa_d = 0;
		}

		if($u_pcsa_d > 0 || $t_pcsa_d > 0){
			$u_dam += $u_pcsa_d;
			$t_dam += $t_pcsa_d;
			$tech_ot .= make_row(array("<b class=b1>Amount of SA Destroyed</b>","$u_pcsa_d","$t_pcsa_d"));
		}


		#==============
		# Offensive turrets v's shields
		#==============
		$ush_dead = 0;

		if($u_bonus['at'] >= $tr_ship['shields'] && ($tr_ship['shields'] > 0 || $usa_dead == 1)){ #shields eliminated on defending ship by turrets.
			#$out_str .= "<br>The <b>Offensive Turrets</b> on the <b class=b1>$user_ship['ship_name']</b> destroyed all <b>$tr_ship['shields']</b> shields protecting the <b class=b1>$target_ship['ship_name']</b>.";
			$u_bonus['at'] -= $tr_ship['shields'];
			$t_d_sh += $tr_ship['shields'];
			$u_sh_d = $tr_ship['shields'];
			$ush_dead = 1;
			$tr_ship['shields'] = 0;
		} elseif($u_bonus['at'] < $tr_ship['shields'] && $u_bonus['at'] > 0){ #turrets stopped by defending ships shields
			#$out_str .= "<br>The <b>Shields</b> on the <b class=b1>$target_ship['ship_name']</b> stopped <b>$tr_ship['shields']</b> damage from the <b>Offensive Turrets</b> of the <b class=b1>$user_ship['ship_name']</b> from getting through.";
			$t_d_sh += $u_bonus['at'];
			$tr_ship['shields'] -= $u_bonus['at'];
			$u_sh_d = $u_bonus['at'];
			$u_bonus['at'] = 0;
		} else {
			$u_sh_d = 0;
		}

		if($t_bonus['at'] >= $ur_ship['shields'] && ($ur_ship['shields'] > 0 || $tsa_dead == 1)){ #shields eliminated on attacking ship by turrets.
			#$out_str .= "<br>The <b>Offensive Turrets</b> on the <b class=b1>$target_ship['ship_name']</b> destroyed all <b>$ur_ship['shields'] Shields</b> protecting the <b class=b1>$user_ship['ship_name']</b>.";
			$t_bonus['at'] -= $ur_ship['shields'];
			$u_d_sh += $ur_ship['shields'];
			$t_sh_d = $ur_ship['shields'];
			$tsh_dead = 1;
			$ur_ship['shields'] = 0;
		} elseif($t_bonus['at'] < $ur_ship['shields'] && $t_bonus['at'] > 0){ #turrets stopped by attacking ships shields
			#$out_str .= "<br>The <b>Shields</b> on the <b class=b1>$user_ship['ship_name']</b> stopped <b>$ur_ship['shields']</b> damage from the <b>Offensive Turrets</b> of the <b class=b1>$target_ship['ship_name']</b> from getting through.";
			$u_d_sh += $t_bonus['at'];
			$ur_ship['shields'] -= $t_bonus['at'];
			$t_sh_d = $t_bonus['at'];
			$t_bonus['at'] = 0;
		} else {
			$t_sh_d = 0;
		}

		if($u_sh_d > 0 || $t_sh_d > 0){
			$u_dam += $u_sh_d;
			$t_dam += $t_sh_d;
			$tech_ot .= make_row(array("<b class=b1>Shields Destroyed</b>","$u_sh_d","$t_sh_d"));
		}


		#==============
		# Offensive turrets v's fighters
		#==============
		$t_destroyed = 0;
		if($u_bonus['at'] >= $tr_ship['fighters'] && ($tr_ship['fighters'] > 0 || $ush_dead == 1)){ #fighters eliminated on defending ship by turrets.
			#$out_str .= "<br>The <b>Offensive Turrets</b> on the <b class=b1>$user_ship['ship_name']</b> destroyed all <b>$tr_ship['fighters'] Fighters</b> defending the <b class=b1>$target_ship['ship_name']</b>.";
			$u_bonus['at'] -= $tr_ship['fighters'];
			$t_d_fig += $tr_ship['fighters'];
			$u_pcfig_d = $tr_ship['fighters'];
			$tr_ship['fighters'] = 0;
			$t_destroyed = 1;
		} elseif($u_bonus['at'] < $tr_ship['fighters'] && $u_bonus['at'] > 0){ #turrets stopped by defending ships fighters
			#$out_str .= "<br>The <b>Fighters</b> on the <b class=b1>$target_ship['ship_name']</b> were targetted by the <b>Offensive Turrets</b> of the <b class=b1>$user_ship['ship_name']</b>. <b>$u_bonus['at']</b> were destroyed.";
			$t_d_fig += $u_bonus['at'];
			$tr_ship['fighters'] -= $u_bonus['at'];
			$u_pcfig_d = $u_bonus['at'];
			$u_bonus['at'] = 0;
		} else {
			$u_pcfig_d = 0;
		}

		$u_destroyed = 0;
		if($t_bonus['at'] >= $ur_ship['fighters'] && ($ur_ship['fighters'] > 0 || $tsh_dead == 1)){ #fighters eliminated on attacking ship by turrets.
			#$out_str .= "<br>The <b>Offensive Turret</b>s on the <b class=b1>$target_ship['ship_name']</b> destroyed all <b>$ur_ship['fighters'] Fighters</b> defending the <b class=b1>$user_ship['ship_name']</b>.";
			$t_bonus['at'] -= $ur_ship['fighters'];
			$u_d_fig += $ur_ship['fighters'];
			$t_pcfig_d = $ur_ship['fighters'];
			$ur_ship['fighters'] = 0;
			$u_destroyed = 1;
		} elseif($t_bonus['at'] < $ur_ship['fighters'] && $t_bonus['at'] > 0){ #turrets stopped by attacking ships fighters
			#$out_str .= "<br>The <b>Fighters</b> on the <b class=b1>$user_ship['ship_name']</b> were targetted by the <b>Offensive Turrets</b> of the <b class=b1>$target_ship['ship_name']</b>. <b>$t_bonus['at']</b> were destroyed.";
			$u_d_fig += $t_bonus['at'];
			$ur_ship['fighters'] -= $t_bonus['at'];
			$t_pcfig_d = $t_bonus['at'];
			$t_bonus['at'] = 0;
		} else {
			$t_pcfig_d = 0;
		}


		if($u_pcfig_d > 0 || $t_pcfig_d > 0){
			$u_dam += $u_pcfig_d;
			$t_dam += $t_pcfig_d;
			$tech_ot .= make_row(array("<b class=b1>Fighters Destroyed</b>","$u_pcfig_d","$t_pcfig_d"));
		}


		if(!empty($tech_ot)){
			$tech_str .= "<br><br>Offensive Turrets:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$target_ship['ship_name']."</b>")).$tech_ot."</table>";
		}

		#most upgrades complete


		if($user_ship['fighters'] > 0){
			$attack_damage =  round($user_ship['fighters'] * 0.65);
			$attack_damage += mt_rand(round(-$user_ship['fighters'] * 0.06),round($user_ship['fighters'] * 0.06));
		} else {
			$attack_damage = 0;
		}

		if($target_ship['fighters'] > 0){
			$counter_damage =  round($target_ship['fighters'] * 0.85);
			$counter_damage +=mt_rand(round(-$target_ship['fighters'] * 0.06),round($target_ship['fighters'] * 0.06));
		} else {
			$counter_damage = 0;
		}

		$xtra_attack = 1;
		$less_attack = 1;

		$xtra_counter = 1;
		$less_counter = 1;

		#take into account ship speed, and ship size.
		$xtra_attack += $target_ship['move_turn_cost'] + $target_ship['size'];
		$xtra_counter += $user_ship['move_turn_cost'] + $user_ship['size'];

		$less_attack += $user_ship['move_turn_cost'];
		$less_counter += $target_ship['move_turn_cost'];


		#ship experiance taken into account.
		$at_points = ($user_ship['points_killed'] + 1) / 100;
		if($at_points > 20){
			$at_points = 20;
		}

		$co_points = ($target_ship['points_killed'] + 1) / 100;
		if($co_points > 20){
			$co_points = 20;
		}

		$xtra_attack += $at_points;
		$xtra_counter += $co_points;

		function inc_dam($stat,$ship,$num){
		#user battleship
			if (eregi($stat,$ship['config'])){
				return $num;
			}
		}


		#take in ship specialties
		$xtra_attack += inc_dam("bs",$user_ship,10);
		$xtra_counter += inc_dam("bs",$target_ship,10);

		$xtra_attack += inc_dam("hs",$user_ship,3.5);
		$xtra_counter += inc_dam("hs",$target_ship,3.5);

		$xtra_attack += inc_dam("ls",$user_ship,1);
		$xtra_counter += inc_dam("ls",$target_ship,1);

		$xtra_attack += inc_dam("sc",$user_ship,5);
		$xtra_counter += inc_dam("sc",$target_ship,5);

		$xtra_attack += inc_dam("fr",$user_ship,4);
		$xtra_counter += inc_dam("fr",$target_ship,4);

		$xtra_attack += inc_dam("po",$target_ship,25);

		$less_attack += inc_dam("fr",$user_ship,7.5);
		$less_counter += inc_dam("fr",$target_ship,7.5);

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
			$tech_str .= "<br><br>Fighter Damage:".make_table(array("","<b class=b1>".$user_ship['ship_name']."</b>","<b class=b1>".$target_ship['ship_name']."</b>")).make_row(array("<b class=b1>Damage Done</b>","$attack_damage","$counter_damage"));
			$end_table = "</table>";
		}

#		$short_str .= "<br><br>The <b class=b1>$user_ship[ship_name]'s</b> <b>Fighters</b> did <b>$attack_damage</b> attacking damage to the <b class=b1>$target_ship[ship_name]</b>.";
#		$short_str .= "<br>The <b class=b1>$target_ship[ship_name]'s</b> <b>Fighters</b> did <b>$counter_damage</b> counter damage to the <b class=b1>$user_ship[ship_name]</b>.";


		#==========
		#silicon armour modules absorb some damage
		#==========
		#fighter damage absorbed by the SA modules
		if($u_bonus['sa'] >= $counter_damage && $counter_damage > 0){
			$out_str .= "<br>All damage dealt by the <b class=b1>$target_ship[ship_name]'s</b> <b>Fighters</b> has been absorbed by the <b>Silicon Armour Modules</b> on the <b class=b1>$user_ship[ship_name]</b>.";
			$u_bonus['sa'] -= $counter_damage;
			$u_figssa_d = $counter_damage;
			$counter_damage = 0;

		} elseif($u_bonus['sa'] < $counter_damage && $u_bonus['sa'] > 0){ #sa can't take all the damage
			$out_str .= "<br><b>Silicon Armour Modules</b> on the <b class=b1>$target_ship[ship_name]</b> managed to stop <b>$u_bonus[sa]</b> of <b>Fighter</b> damage getting through to the <b class=b1>$user_ship[ship_name]</b> before withering away.";
			$counter_damage -= $u_bonus['sa'];
			$u_figssa_d = $u_bonus['sa'];
			$u_bonus['sa'] = 0;
		} else {
			$u_figssa_d = 0;
		}

		#fighter damage absorbed by the SA modules
		if($t_bonus['sa'] >= $attack_damage && $attack_damage > 0){
			$out_str .= "<br>All damage dealt by the <b class=b1>$user_ship[ship_name]'s</b> <b>Fighters</b> has been absorbed by the <b>Silicon Armour Modules</b> on the <b class=b1>$target_ship[ship_name]</b>.";
			$t_bonus['sa'] -= $attack_damage;
			$t_figssa_d = $attack_damage;
			$attack_damage = 0;

		} elseif($t_bonus['sa'] < $attack_damage && $t_bonus['sa'] > 0){ #sa can't take all the damage
			$out_str .= "<br><b>Silicon Armour Modules</b> on the <b class=b1>$user_ship[ship_name]</b> managed to stop <b>$t_bonus[sa]</b> of <b>Fighter</b> damage getting through to the <b class=b1>$target_ship[ship_name]</b> before being withered down.";
			$attack_damage -= $t_bonus['sa'];
			$t_figssa_d = $t_bonus['sa'];
			$t_bonus['sa'] = 0;
		} else {
			$t_figssa_d = 0;
		}

		if($u_figssa_d > 0 || $t_figssa_d > 0){
			$u_dam += $u_pfigssa_d;
			$t_dam += $t_figssa_d;
			$tech_str .= make_row(array("<b class=b1>Stopped by Enemy SA</b>","$t_figssa_d","$u_figssa_d"));
		}


		#get everything re-aligned
		$target_ship = $tr_ship;
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

		if($target_ship['shields'] >= $attack_damage && $attack_damage > 0){
			$t_figssh_d = $attack_damage;
			$theory_attack = 0;

		} elseif($target_ship['shields'] < $attack_damage && $target_ship['shields'] > 0){
			$t_figssh_d = $target_ship['shields'];
			$theory_attack = $attack_damage - $target_ship['shields'];
		} else {
			$t_figssh_d = 0;
			$theory_attack = $attack_damage;
		}

		if($u_figssh_d > 0 || $t_figssh_d > 0){
			$tech_str .= make_row(array("<b class=b1>Stopped by Enemy Shields</b>","$t_figssh_d","$u_figssh_d"));
		}


		#some fighters maths for the complicated view of the battle.
		if($user_ship['fighters'] >= $theory_counter && $theory_counter > 0){
			$u_figsfigs_d = $theory_counter;
		} elseif($user_ship['fighters'] < $theory_counter && $user_ship['fighters'] > 0){
			$u_figsfigs_d = $user_ship['fighters'];
		} else {
			$u_figsfigs_d = 0;
		}

		if($target_ship['fighters'] >= $theory_attack && $theory_attack > 0){
			$t_figsfigs_d = $theory_attack;
		} elseif($target_ship['fighters'] < $theory_attack && $target_ship['fighters'] > 0){
			$t_figsfigs_d = $target_ship['fighters'];
		} else {
			$t_figsfigs_d = 0;
		}

		if($u_figsfigs_d > 0 || $t_figsfigs_d > 0){
			$theory_attack = $theory_attack - $t_figsfigs_d;
			$theory_counter = $theory_counter - $u_figsfigs_d;
			$tech_str .= make_row(array("<b class=b1>Used to Destroy Enemy Fighters</b>","$t_figsfigs_d","$u_figsfigs_d"));
			$tech_str .= make_row(array("<b class=b1>Un-used Firepower</b>",$theory_attack, $theory_counter));
		}

		$tech_str .= $end_table."<br><br>Totals:".make_table(array("","<b class=b1>".$user_ship['ship_name'],"<b class=b1>".$target_ship['ship_name']."</b>"));
		$tech_str .= make_row(array("<b class=b1>Total Damage Taken</b>",$t_dam - $theory_counter, $u_dam - $theory_attack));
		$tech_str .= make_row(array("<b class=b1>Total Damage Done</b>",$u_dam - $theory_attack, $t_dam - $theory_counter));


		#determine if the ship was destroyed or not.
		if($attack_damage > $target_ship['fighters'] + $target_ship['shields']){
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
			dbn("update ${db_name}_ships set points_killed = points_killed + '$target_ship[point_value]' where ship_id = '$user_ship[ship_id]'");
		} else {
			$send_to_func_t = $attack_damage;
			$t_des_text = "No";
			dbn("update ${db_name}_ships set points_killed = points_killed + '$user_ship[point_value]' where ship_id = '$target_ship[ship_id]'");
		}

		$tech_str .= make_row(array("<b class=b1>Ship Destroyed?</b>",$u_des_text, $t_des_text))."</table>";

		$temp101 = $u_dam - $theory_attack;
		$short_str .= "<br><br>The <b class=b1>$user_ship[ship_name]</b> did a total of <b>$temp101</b> damage.";
		$temp101 = $t_dam - $theory_counter;
		$short_str .= "<br>The <b class=b1>$target_ship[ship_name]</b> did a total of <b>$temp101</b> damage.";

		// Deal Attacking Damage
		$dead_ship = $target_ship;

		$dam_take = damage_ship($send_to_func_t,$t_d_fig,$t_d_sh,$user,$target,$target_ship);
		if($dam_take == 1) {

			post_news("<b class=b1>$user[login_name]</b> destroyed <b class=b1>$target[login_name]</b>'s $target_ship[class_name].");

			#Raiding of a ship
			if(ereg("rd",$user_ship['config'])) {
				$target_ship_bays = $target_ship['cargo_bays'] - ($target_ship[metal] - $target_ship[fuel] - $target_ship['elect'] - $target_ship[colon] - $target_ship[organ]- $target_ship['scrap']);
				$dead_cols = 0;
				if($user_ship[colon] < 20) {
					$error_str .= "<p>Your crew decided not to try and raid the <b class=b1>$dead_ship[ship_name]</b>, as there where not <b>20</b> Colonists on-board.";
				} elseif($target_ship_bays > 0) {

					$rand101 = mt_rand(0,3);

					if($rand101 > 1){
						$dead_cols = mt_rand(5,20);
						$cash_given = round((($target_ship_bays * 10) / ($dead_cols / 2)) * 15);
						$error_str .= "<p>Your Raid was a success. Though there where the inevitable casualties (<b>$dead_cols</b> colonists lost in all), you successfully looted the <b class=b1>$dead_ship[ship_name]</b> before it blew up.<br>The plunder gained you <b>$cash_given</b> Credits once it was sold on the Black Market.";
						dbn("update ${db_name}_ships set colon = colon - $dead_cols where ship_id = $user[ship_id]");
						give_cash($cash_given);
					} else {

						$boom_damage = mt_rand(5,15);
						$user_dead_ship = $user_ship;
						$boom_take = damage_ship($boom_damage,$target,$user,$user_ship);
						if($boom_take == 1) {
							post_news("<b class=b1>$user[login_name]</b>'s <b class=b1>$user_dead_ship[ship_name]</b> was destroyed whilst trying to raid the <b class=b1>$dead_ship[ship_name]</b>");
							print_page ("Raid Failed","$error_str <br>Your attempt to raid the ship failed. The target ship exploded before you could get to it. <br>The <b>$boom_damage</b> damage done by the explosion destroyed your <b class=b1>$user_dead_ship[ship_name]</b>.<br>You are now in command of the <b class=b1>$user_ship[ship_name]</b>.");
						} elseif($boom_take == 2){
							post_news("<b class=b1>$user[login_name]</b> was reduced to an Escape Pod when the <b class=b1>$dead_ship[ship_name]</b> blew up as it was being raided, taking the attacking ship (<b class=b1>$user_dead_ship[ship_name]</b>) with it.");
							print_page ("Raid Failed","$error_str <br>Your attempt to raid the ship failed. The target ship exploded before you could get to it. <br>The <b>$boom_damage</b> damage done by the explosion destroyed your <b class=b1>$user_dead_ship[ship_name]</b>.<br>You are floating around in an escape pod which was jettisoned to a random system (#<b>$user[location]</b>).");
						} else{
							$error_str .= "<br>Though your raid attempt failed, you where fortunate not to get destroyed by the explosion of the target ship which did <b>$boom_damage</b> damage to your ship.";
						}
					}
				} else {
					$error_str .= "<p>Your attempt to raid the <b class=b1>$dead_ship[ship_name]</b> failed as it had nothing in its cargo bays.";
				}
			}

			if($dead_ship['shipclass'] == 2) { #just lost their EP... oh dear....
					$mess_str .= "<br><br>Your <b class=b1>Escape Pod</b> was blown to <b>Smitherines</b> by the <b class=b1>$user_ship[ship_name]</b>.";
					$user_str .= "<br><br>You <b>Annihilated</b> the <b class=b1>Escape Pod</b>.";

				if($sudden_death){
					$mess_str .= "<br>As the game is in <b>Sudden Death(SD)</b>, you are out of the game permanently.";
					$user_str .= "<br>As the game is in Sudden Death(SD), <b class=b1>$target[login_name]</b> is out of the game permanently.";
					post_news("<b class=b1>$target[login_name]</b> just got splatted, and is out of the game permanently.");
				}
				if($target['bounty'] > 0) {
					$user_str .= "<p>You have claimed the <b>$target[bounty]</b> Credit bounty that was on <b class=b1>$target[login_name]</b>s head.";
					post_news("The <b>$target[bounty]</b> bounty on <b class=b1>$target[login_name]</b> has been claimed by <b class=b1>$user[login_name]</b>");
					dbn("update ${db_name}_users set cash = cash + $target[bounty] where login_id = '$user[login_id]'");
					dbn("update ${db_name}_users set bounty = 0 where login_id = '$target[login_id]'");
				}
			} else { #otherwise they just lost another boring old ship
				$short_str .= "<br><br>The <b class=b1>$target_ship[ship_name]</b> was destroyed in the attack.";
			}

		#target just got "podded"....
		} elseif ($dam_take==2){
			$short_str .= "<br><br>The <b class=b1>$target_ship[ship_name]</b> was destroyed in the attack, and the owner ejected in an <b class=b1>Escape Pod</b>.";

			send_message($target['login_id'],"<b class=b1>Warning</b>. You have just been Podded.<br>It would be highly advisable for you buy another ship before you end up rather dead.");

			post_news("<b class=b1>$user[login_name]</b> destroyed $target_ship[class_name] podding <b class=b1>$target[login_name]</b>.");
		} else { #ship not destroyed
			$short_str .= "<br><br>The <b class=b1>$target_ship[ship_name]</b> survived the encounter.";
		}

		// Deal Counter Damage
		$dead_ship = $user_ship;

		$dam_take = damage_ship($send_to_func_u,$u_d_fig,$u_d_sh,$target,$user,$user_ship);

		if($dam_take == 1) {
			post_news("<b class=b1>$target[login_name]</b> destroyed <b class=b1>$user[login_name]</b>\'s $dead_ship[class_name].");
			if($dead_ship['shipclass'] == 2) { #ship lost was an EP
				$mess .= "<br><br>You waxed the <b class=b1>Escape Pod</b>.";
				$user_str .= "<br><br><b class=b1>$target[login_name]</b> successfully obliterated your Escape Pod.";
				if($sudden_death){
					$user_str .= "<br>As the game is in Sudden Death(SD) you are out of the game permanently.";
					post_news("<b class=b1>$user[login_name]</b> has been completely killed, and is out of the game permanently.");
				}
				if($user['bounty'] > 0) {
					send_message($target['login_id'],"<p>You have claimed the <b>$user[bounty]</b> Credit bounty that was on <b class=b1>$user[login_name]s</b> head.");
					post_news("The <b>$user[bounty]</b> bounty on <b class=b1>$user[login_name]</b> has been claimed by <b class=b1>$target[login_name]</b>");
					dbn("update ${db_name}_users set cash = cash + $user[bounty] where login_id = '$target[login_id]'");
					dbn("update ${db_name}_users set bounty = 0 where login_id = '$user[login_id]'");
				}
				#player looses their ship
			} else {
				$mess .= "<br><br>You destroyed the <b class=b1>$user_ship[ship_name]</b>.";
				$user_str .= "<br>Your <b class=b1>$dead_ship[class_name]</b> was destroyed in the fight.";
				$dead_ship_check = 56;
			}
		} elseif ($dam_take==2){
			$mess .= "<br><br>You destroyed the <b class=b1>$user_ship[ship_name]</b>. <b class=b1>$user[login_name]</b> is now floating around somewhere in an <b>Escape Pod</b>.";
			$user_str .= "<br>Your <b class=b1>$dead_ship[class_name]</b> was destroyed in the fight.";
			$user_str = "<br>You ejected in a Escape Pod.";
			post_news("<b class=b1>$target[login_name]</b> destroyed <b class=b1>$user[login_name]</b>\'s $dead_ship[class_name].");
		} else { #ship survived
			$short_str .= "<br><br>The <b class=b1>$user_ship[ship_name]</b> survived the encounter.";
		}
	}
}


db("select attack_report from ${db_name}_user_options where login_id = '$target[login_id]'");
$target_options = dbr();


#determine if the simple, or the complex report should be sent.
if($target_options['attack_report'] == 1 || $target_ship['size'] < 4 || ereg("fr",$target_ship['config'])){
	send_message($target['login_id'],addslashes($def_str.$short_str.$mess));
} else {
	send_message($target['login_id'],addslashes($def_str.$tech_str.$out_str3));
}


// update user stats for status bar
db("select * from ${db_name}_users where login_id = $user[login_id]");
$user = dbr();
$user_ship = userShip($user['ship_id']);

#print the name of the new users ship, as well as the users new location.
if($dead_ship_check == 56){
	$end_str .= "<br><br>Your command has been transfered to the <b class=b1>$user_ship[ship_name]</b>($user_ship[class_name]), in system #<b>$user_ship[location]</b>.";
}

#determine if the simple, or the complex report should be presented.
if($user_options['attack_report'] == 1){
	print_page("Attack",$def_str.$short_str.$error_str.$user_str.$end_str);
} else {
	print_page("Attack",$def_str.$tech_str.$error_str.$user_str.$end_str);
}

?>
