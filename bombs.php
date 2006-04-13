<?php

require_once('inc/user.inc.php');

if (!IS_ADMIN) {
	if ($user['turns_run'] < $gameOpt['turns_before_attack']) {
		print_page("Bomb","You can't attack during the first $gameOpt[turns_before_attack] turns of having your account.");
	}

	if ($user['ship_id'] === NULL) {
		print_page("Bomb","You May not use a Bomb when you are not commanding a ship. Try buying a ship then set off a Bomb");
	}
}

deathCheck($user);

$error_str = "<h1>Bomb detonation</h1>";


#Alpha Bomb
if(isset($alpha)) {
	if (attack_planet_check($user) < 1 || IS_ADMIN) {
		if($user['alpha'] < 1) {
			$error_str = "You don't have a Alpha Bomb.";
		} elseif($gameOpt['flag_sol_attack'] == 0 && $userShip['location'] == 1 && !IS_ADMIN) {
			$error_str = "The Admin has disabled all forms of attack in the Sol System (system #<b>1</b>)..";
		} elseif(!isset($sure)) {
			get_var('Use Alpha Bomb','bombs.php','Are you sure you want to use an Alpha Bomb?','sure','');
		} else {
			$db->query("update [game]_users set alpha = alpha - 1 where login_id = $user[login_id]");

			post_news("$user[login_name] imploded a Alpha Bomb in system #$userShip[location]");
			get_star();

			$lastresort = $db->query('SELECT s.ship_id, s.ship_name, ' .
			 's.login_id FROM [game]_ships AS s LEFT JOIN ' .
			 '[game]_users AS u ON s.login_id = u.login_id ' .
			 'WHERE s.location = %u AND u.login_id != %u AND ' .
			 'u.turns_run > %u', array($userShip['location'], 
			 $gameInfo['admin'], $gameOpt['turns_safe']));

			$ship_counter = 0;
			$victims = array();
			while ($target_ship = $db->fetchRow($lastresort, ROW_NUMERIC)) {
				dbn("update [game]_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
				$ship_counter++;
				$victims[$target_ship['login_id']] .= "\n<br /><b class=b1>$target_ship[ship_name]</b> ($target_ship[class_name])";
			}

			#loop to send out a message to each player.
			foreach($victims as $victim_id => $ship_list) {
				$ships_hit = substr_count($ship_list, "<b class=b1>");

				#don't send a message to the user if they are hit by a bomb.
				if($victim_id == $user['login_id']){
					continue;
				}
				msgSendSys($victim_id,"<b class=b1>$user[login_name]</b> unleashed an Alpha Bomb in Star System #<b>$userShip[location]</b>.<br />The bomb hit <b>$ships_hit</b> of your ships, completely eliminating all of their shields.<br />Shown below is a complete listing of your all ships hit by the blast:<br />$ship_list");
			}

			$error_str .= "You have successfully released an Alpha Bomb in system #<b>$star[star_id]</b>, hitting <b>$ship_counter</b> ship(s) in all, and reducing all shields on those ships to <b>0</b>.";

		}

		checkPlayer($user['login_id']);
		checkShip($user['ship_id']);
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

if (attack_planet_check($user) < 1 || IS_ADMIN) {
	if($user['gamma'] < 1 && $bomb_type==1) {
		$error_str = "You don't have a Gamma Bomb.";
	} elseif($user['delta'] < 1 && $bomb_type==2) {
		$error_str = "You don't have a Delta Bomb.";
	} elseif($gameOpt['flag_sol_attack'] == 0 && $userShip['location'] == 1 && !IS_ADMIN) {
		$error_str = "The Admin has disabled all forms of attack in the Sol System (system #<b>1</b>).";
	} elseif(!isset($sure)) {
		get_var('Use $b_text Bomb','bombs.php',"Are you sure you want to detonate a $b_text Bomb?",'sure','');
	} else {
		dbn("update [game]_users set ${b_text} = ${b_text} - 1 where login_id = $user[login_id]");

		post_news("$user[login_name] unleashed a $b_text Bomb in system #$userShip[location]");

		get_star();
		if ($bomb_type == 1) { // gamma bomb
			$bomb_damage = 200;
		} elseif ($bomb_type == 2) { // delta bomb
			// clear all shields on all ships before we start.
			db("select s.ship_id from [game]_ships s, [game]_users u where s.location = '$userShip[location]' AND u.login_id != 1 AND s.ship_id > 1 AND s.login_id = u.login_id AND u.turns_run > $gameOpt[turns_safe]");

			while($target_ship = dbr(1)){
				dbn("update [game]_ships set shields = 0 where ship_id = '$target_ship[ship_id]'");
			}
			$target_ship = "";

			$bomb_damage = 5000;
		}

		$ship_counter = 0;
		$dam_victim = array();
		$destroyed_ships = 0;

		$lastresort = $db->query("select s.fighters,s.shields,s.ship_id,s.metal,s.fuel,s.location,s.login_id,s.ship_name,s.point_value,u.login_name from [game]_ships s,[game]_users u where s.location = '$userShip[location]' AND s.login_id >'1' AND s.login_id = u.login_id AND u.turns_run >= $gameOpt[turns_safe]");

		$elim = 0;

		#loop through players to damage.
		while ($target_ship = $db->fetchRow($lastresort, ROW_NUMERIC)) {
			#db("select login_name,login_id,ship_id from [game]_users where login_id = '$target_ship[login_id]'");
			#$target = dbr();


			$ship_counter++;

			#silicon armour taken into effect.
			$this_bomb = $bomb_damage;
			$temp121 = 0;
			$temp121 = damage_ship($this_bomb,0,0,$user,$target_ship,$target_ship);

			#Used to limit messages sent, so each player only gets 1 message.
			$dam_victim[$target_ship['login_id']] .= "\n<br /><b class=b1>$target_ship[ship_name]</b> ($target_ship[class_name])";
			if($temp121 > 0) {
				$dam_victim[$target_ship['login_id']] .= " - Destroyed";
				++$elim;
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
			msgSendSys($victim_id,"<b class=b1>$user[login_name]</b> unleashed a $b_text Bomb in Star System #<b>$userShip[location]</b>.<br />The bomb hit <b>$ships_hit</b> of your ships doing <b>$bomb_damage</b> damage to each.<br /><br />Of those hit, <b>$ships_killed</b> were destroyed by the blast.<br />Shown below is a compelte listing of all your ships hit by the bomb:<br />$ship_list");
		}

		if($elim == 0){
			$elim = "None";
		}

		$error_str .= "You have successfully released a $b_text Bomb in system #<b>$star[star_id]</b>, hitting <strong>$ship_counter ships</strong> in all, and doing <strong>" . ($ship_counter * $bomb_damage) . " damage</strong> in total, or <strong>$bomb_damage damage</strong> to each ship. Of those hit, <b>$elim</b> were destroyed.";
		$error_str .= "<br /><br />To put that into a more appropriate context:";
		$error_str .= "<p><strong>kaaaaBBBBBBOOOOOOMMMMMMMMM!</strong>";
	}

	checkPlayer($user['login_id']);
	checkShip($user['ship_id']);
} else {
	$error_str = "You cannot use a $b_text Bomb in a system with an Attack Planet in it.";

}

print_page("Use $b_text Bomb",$error_str);
?>
