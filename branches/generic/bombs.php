<?php

require_once('inc/user.inc.php');

if ($user['login_id'] != ADMIN_ID) {
	if ($user['turns_run'] < $turns_before_attack) {
		print_page("Bomb","You can't attack during the first <b>$turns_before_attack</b> turns of having your account.");
	}

	if ($user['ship_id'] == NULL) {
		print_page("Bomb","You May not use a Bomb when you are not commanding a ship. Try buying a ship then set off a Bomb");
	}
}

sudden_death_check($user);

$error_str = "";

#SuperNova Effector
if($sn_effect) {
	get_star();
	if($user['sn_effect'] < 1) {
		print_page("No can Blow","You don't have a SuperNova Effector.");
	} elseif(!isset($sure)) {
		get_var('Use SuperNova Effector','bombs.php','Are you sure you want to use the SuperNova Effector?','sure','');
	} elseif($star['event_random'] == 2) {
		print_page("No can Blow","Sorry. You need a system with a star in to allow you to blow it up. <br>This system is a <b class=b1>Nebula</b>, which means that it is only gases. <br>Try again somewhere else.");
	} elseif($star['event_random'] == 1) {
		print_page("No can Blow","Sorry. You need a system with a star in to allow you to blow it up. <br>The star in this system has already exploded, and has formed a <b class=b1>BlackHole</b>. <br>Try again somewhere else.");
	} elseif($star['event_random'] == 6) {
		print_page("No can Blow","Sorry. You need a system with a star in to allow you to blow it up. <br>The star in this system only just exploded, and is now a <b class=b1>SuperNova Remnant</b>. <br>Try again somewhere else.");
	} elseif($star['event_random'] == 5 || $star['event_random'] == 10) {
		print_page("No can Blow","This star is already fairly likely to Blow-up. <br>There's no point in using your SN Effector here.");
	} elseif($star['star_id'] == 1) {
		dbn("delete from ${db_name}_ships where ship_id = $user[ship_id] && ship_id != '1'");
		db("select ship_id,location from ${db_name}_ships where login_id='$user[login_id]'");
		$n_s_1 = dbr();
		if($n_s_1['ship_id']){
			dbn("update ${db_name}_users set ship_id = '$n_s_1[ship_id]' && location = '$n_s_1[location]' where login_id='$user[login_id]'");
			$user['ship_id'] = $n_s_1['ship_id'];
			$user['location'] = $n_s_1['location'];
		} elseif($user_ship['shipclass'] != 2) {
			$user = create_escape_pod($user); //dump user into an EP
		} else {
			dbn("update ${db_name}_users set location = '1', ship_id = NULL where login_id = '$user[login_id]'");
			$user['location'] = 1;
			$user['ship_id'] = NULL;
		}
		post_news("One of <b class=b1>$user[login_name]</b>s ships was destroyed by a mutiny of the crew.");
		print_page("Mutiny","<b>What!?!?!</b> You'd try and destroy the <b class=b1>Sol system</b>? <br>What sort of <b>Maniac</b> are you? <br>Fortunatly the crew on your ship knew better, and so <b class=b1>mutineed </b>to stop you destroying everything they hold fair. <p>Your ship was destroyed during the mutiny.");
	} elseif(!isset($sure)) {
		get_var('Use SuperNova Effector','bombs.php','Are you sure?','sure','');
	} else {
		if($user['login_id'] != ADMIN_ID){
			dbn("update ${db_name}_users set sn_effect = 0 where login_id = " . $user['login_id']);
		}
		dbn("update ${db_name}_stars set event_random = 10 where star_id = $user[location]");
		post_news("<b class=b1>$user[login_name]</b> released a SuperNova Effector in star system #<b>$user_ship[location]</b>");
		post_news('Due to the Release of a SuperNova Effector in system #<b>' .
		 $user_ship[location] . '</b> by <b class=b1>' . $user[login_name] .
		 '</b> only a few moments ago, the star is set to go SuperNova. This means that everything in the system will be destroyed. The star will explode within the next <b>48 hours</b>.');
		print_page("Detonation Complete","The SuperNova Effector has successfully detonated.<br>This star will go SuperNova within the next <b>48 hours</b>.");
	}
}

#Alpha Bomb
if($alpha) {
// checks

db(attack_planet_check($db_name,$user));
$planets = dbr();

	if(empty($planets) || $user['login_id'] == ADMIN_ID) {
		if($user['alpha'] < 1) {
			$error_str = "You don't have a Alpha Bomb.";
		} elseif($flag_sol_attack == 0 && $user['location'] == 1 && $user['login_id'] != ADMIN_ID) {
			$error_str = "The Admin has disabled all forms of attack in the Sol System (system #<b>1</b>)..";
		} elseif(!isset($sure)) {
			get_var('Use Alpha Bomb','bombs.php','Are you sure you want to use an Alpha Bomb?','sure','');
		} else {
			if($user['login_id'] != ADMIN_ID){
				dbn("update ${db_name}_users set alpha = alpha - 1 where login_id = $user[login_id]");
			}

			post_news("<b class=b1>$user[login_name]</b> imploded a Alpha Bomb in system #$user_ship[location]");
			get_star();

			$lastresort = mysql_query("select s.ship_id,s.ship_name,s.login_id,s.class_name from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' && u.login_id != 1 && s.ship_id > 1 && s.login_id = u.login_id && u.turns_run > '$turns_safe'") or mysql_die("");

			$ship_counter = 0;
			$victims = array();
			while($target_ship = mysql_fetch_array($lastresort)) {
				dbn("update ${db_name}_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
				$ship_counter++;
				$victims[$target_ship['login_id']] .= "\n<br><b class=b1>$target_ship[ship_name]</b> ($target_ship[class_name])";
			}

			#loop to send out a message to each player.
			foreach($victims as $victim_id => $ship_list) {
				$ships_hit = substr_count($ship_list, "<b class=b1>");

				#don't send a message to the user if they are hit by a bomb.
				if($victim_id == $user['login_id']){
					continue;
				}
				send_message($victim_id,"<b class=b1>$user[login_name]</b> unleashed an Alpha Bomb in Star System #<b>$user_ship[location]</b>.<br>The bomb hit <b>$ships_hit</b> of your ships, completely eliminating all of their shields.<br>Shown below is a complete listing of your all ships hit by the blast:<br>$ship_list");
			}

			$error_str .= "You have successfully released an Alpha Bomb in system #<b>$star[star_id]</b>, hitting <b>$ship_counter</b> ship(s) in all, and reducing all shields on those ships to <b>0</b>.";

		}

		db("select * from ${db_name}_users where login_id = '$login_id'");
		$user = dbr(1);
		db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
		$user_ship = dbr(1);
		empty_bays($user_ship);
	} else {
		$error_str = "You cannot use a Alpha Bomb in a system with an Attack Planet in it.";
	}

print_page("Alpha Bomb",$error_str);
}

#===========
#Damage Bombs
#===========

#determine type of bomb
if($bomb_type == 1){ #gamma bomb
	$b_text = "Gamma";
	$sql_text = "gamma";
} elseif($bomb_type == 2){ #delta Bomb
	$b_text = "Delta";
	$sql_text = "delta";
}

// checks
db(attack_planet_check($db_name,$user));
$planets = dbr();

if (empty($planets) || $user['login_id'] == ADMIN_ID) {
	if($user['gamma'] < 1 && $bomb_type==1) {
		$error_str = "You don't have a Gamma Bomb.";
	} elseif($user['delta'] < 1 && $bomb_type==2) {
		$error_str = "You don't have a Delta Bomb.";
	} elseif($flag_sol_attack == 0 && $user['location'] == 1 && $user['login_id'] != ADMIN_ID) {
		$error_str = "The Admin has disabled all forms of attack in the Sol System (system #<b>1</b>).";
	} elseif(!isset($sure)) {
		get_var('Use $b_text Bomb','bombs.php',"Are you sure you want to detonate a $b_text Bomb?",'sure','');
	} else {

		if($user['login_id'] != ADMIN_ID){
			dbn("update ${db_name}_users set ${b_text} = ${b_text} - 1 where login_id = $user[login_id]");
		}

		post_news("<b class=b1>$user[login_name]</b> unleashed a $b_text Bomb in star system #<b>$user_ship[location]</b>");

		get_star();
		if ($bomb_type==1) { #gamma bomb
			$bomb_damage = 200;
		} elseif ($bomb_type==2) { #delta bomb
			#clear all shields on all ships before we start.
			db("select s.ship_id from ${db_name}_ships s, ${db_name}_users u where s.location = '$user[location]' && u.login_id	!= 1 && s.ship_id > 1 && s.login_id = u.login_id && u.turns_run > '$turns_safe'");

			while($target_ship = dbr(1)){
				dbn("update ${db_name}_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
			}
			$target_ship = "";

			$bomb_damage = 5000;
		}

		if ($star['event_random'] == 2){
			$bomb_damage *= 3;
		}

		$ship_counter = 0;
		$dam_victim = array();
		$destroyed_ships = 0;

		$lastresort = mysql_query("select s.fighters,s.shields,s.ship_id,s.metal,s.fuel,s.location,s.login_id,s.class_name,s.ship_name,s.point_value,u.login_name, s.num_sa as num_sa from ${db_name}_ships s,${db_name}_users u where s.location = '$user[location]' && s.ship_id > '1' && s.login_id >'1' && s.login_id = u.login_id && u.turns_run >= '$turns_safe'") or mysql_die("Bombs are messed up.");

		$elim = 0;

		#loop through players to damage.
		while($target_ship = mysql_fetch_array($lastresort)) {
			#db("select login_name,login_id,ship_id from ${db_name}_users where login_id = '$target_ship[login_id]'");
			#$target = dbr();


			$ship_counter++;

			#silicon armour taken into effect.
			$this_bomb = $bomb_damage - ($target_ship['num_sa'] * $upgrade_sa);
			$temp121 = 0;
			$temp121 = damage_ship($this_bomb,0,0,$user,$target_ship,$target_ship);

			#Used to limit messages sent, so each player only gets 1 message.
			$dam_victim[$target_ship['login_id']] .= "\n<br><b class=b1>$target_ship[ship_name]</b> ($target_ship[class_name])";
			if($temp121 > 0) {
				$dam_victim[$target_ship['login_id']] .= " - Destroyed";
				$elim++;
			}
		} # end bomb while loop.

		$elim = 0;
		#loop to send out a message to each player.
		foreach($dam_victim as $victim_id => $ship_list) {

			$ships_hit = substr_count($ship_list, "<b class=b1>");
			$ships_killed = substr_count($ship_list, "- Destroyed");
			$elim += $ships_killed;

			#don't send a message to the user.
			if($victim_id == $user['login_id']){
				continue;
			}
			send_message($victim_id,"<b class=b1>$user[login_name]</b> unleashed a $b_text Bomb in Star System #<b>$user_ship[location]</b>.<br>The bomb hit <b>$ships_hit</b> of your ships doing <b>$bomb_damage</b> damage to each.<br><br>Of those hit, <b>$ships_killed</b> were destroyed by the blast.<br>Shown below is a compelte listing of all your ships hit by the bomb:<br>$ship_list");
		}

		if($elim == 0){
			$elim = "None";
		}

		$error_str .= "You have successfully released a $b_text Bomb in system #<b>$star[star_id]</b>, hitting <b>$ship_counter</b> ships in all, and doing <b>".$ship_counter*$bomb_damage."</b> damage in total, or <b>$bomb_damage</b> damage to each ship. Of those hit, <b>$elim</b> were destroyed.";
		$error_str .= "<br><br>To put that into a more appropriate context:";
		$error_str .= "<p><b class=b2>kaaaaBBBBBBOOOOOOOOOOOOOOOOOOOOOOOOOOOOOMMMMMMMMM!!!!!!!</b>";
	}

	db("select * from ${db_name}_users where login_id = '$user[login_id]'");
	$user = dbr(1);
	db("select * from ${db_name}_ships where ship_id = '$user[ship_id]'");
	$user_ship = dbr(1);
	empty_bays($user_ship);
} else {
	$error_str = "You cannot use a $b_text Bomb in a system with an Attack Planet in it.";

}

print_page("Use $b_text Bomb",$error_str);
?>