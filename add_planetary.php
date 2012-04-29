<?php
/*
A script that contains the code for some of the planetary functions.
Created:
By: Moriarty
Date: 14/6/02
*/

require_once('inc/user.inc.php');


db("select * from ${db_name}_planets where planet_id = '$planet_id'");
$planet = dbr();

$shield_gen_cost = 50000;
$research_fac_cost = 100000;
$out = "";
$header = "Error";

if($user['location'] != $planet['location']) {
	$out = "That planet is not in this system.";
} elseif($planet['login_id'] != $user['login_id']) {
	$out = "Only the planet owner may authorise that action.";
#build missile
} elseif($missile) {
	$header = "Omega Missile Construction";
	if($user[cash] < 100000 || $planet[elect] < 50 || $planet[metal] < 200 || $planet[fuel] < 100) { #134800
		$out .= "You can not afford a <b class=b1>Omega Missile</b>.";
	} elseif($enable_superweapons == 0){
		$out .= "Admin has disabled the ability to use these weapons, as such, building a missile is pointless";
	} elseif($user[turns] < 10){
		$out .= "You to do not have enough turns to build a <b class=b1>Omega Missile</b>.";
	} elseif($planet[missile] != 0) {
		$out .= "You already have a <b class=b1>Omega Missile</b> on this planet.";
	} elseif($sure != 'yes') {
		get_var('Build Omega Missile','add_planetary.php','Are you sure you want to build a <b class=b1>Omega Missile</b>?','sure','yes');
	} else {
		take_cash(100000);
		charge_turns(10);
		$out .= "Your new <b class=b1>Omega Missile</b> is ready, and your planet has been debited the materials needed to construct the Missile.";
		dbn("update ${db_name}_planets set missile = '-1', elect = elect - 50, metal=metal-200,fuel=fuel-100 where planet_id = $planet[planet_id]");
	}

#Fires missile
}elseif($launch_missile){
	$header = "Launch Omega Missile";

	if($user[turns] < 5){
		$out .= "You will require at least <b>5</b> turns to launch the missile, no matter the desination";
	} elseif($enable_superweapons == 0){
		$out .= "Admin has disabled the ability to use these weapons";
	} elseif($user[turns_run] < $turns_before_planet_attack && $user[login_id] != 1) {
		$out .= "You have not used enough turns to be able to attack planets.<br>You need to have used <b>$turns_before_planet_attack</b> or more turns.";
	} elseif($flag_planet_attack == 0) {
		$out .= "The admin presentlty has planet attacking disabled.";
	} elseif(!$planet[missile]){
		$out .= "You do not have a missile on this planet.";
	} elseif(!$destination){
		if($user[clan_id] >0){
			db("select planet_name,planet_id from ${db_name}_planets where clan_id != '$user[clan_id]' && login_id != 1 && planet_id != $planet_id && location != $planet[location] && login_id != $user[login_id]");
		} else {
			db("select planet_name,planet_id from ${db_name}_planets where login_id != 1 && planet_id != $planet_id && location != $planet[location] && login_id != $user[login_id]");
		}
		$enemy_planets=dbr();
		if(!$enemy_planets){
			$out .= "There are no planets in the game that you can fire the missile at.";
		} else {
			$out .= "Select a planet to fire the missile at:";
			$out .= "<form method=post action=add_planetary.php name=missile_form>";
			$out .= "<input type=hidden name=launch_missile value=-1>";
			$out .= "<input type=hidden name=planet_id value=$planet_id>";
			$out .= "<select name=destination>";
			while($enemy_planets){
				$out .= "<option value=$enemy_planets[planet_id]> $enemy_planets[planet_name] ";
				$enemy_planets=dbr();
			}
		$out .= "</select>";
		$out .= "<p><INPUT type=submit value=Launch></form><p>";
		}
	} else {
		db("select * from ${db_name}_planets where planet_id = '$destination'");
		$target_planet = dbr();
		$turns = get_star_dist($user[location],$target_planet[location]);
		if($turns < 12) {
			$turns = 5;
		} else {
			$turns = $turns -5;
		}
		$fuel = $turns * 20;

		if($target_planet[location] == $planet[location]){
			$out .= "Missiles may not be fired at planets in the same system as the launching planet due to fallout concerns.";
		} elseif(($target_planet[clan_id] == $user[clan_id] && $user[clan_id] >0) || $target_planet[login_id] == $user[login_id] || $target_planet[login_id] == 1) {
			$out .= "That planet is an invalid target.";
		} elseif($user[turns] < $turns){
			$out .= "You do not have enough turns to launch your missile at that planet.<br>You have <b>$user[turns]</b> and require <b>$turns</b>.";
		} elseif($planet[fuel] < $fuel){
			$out .= "You require more fuel on the planet to launch at that target.<br>You have <b>$planet[fuel]</b> and require <b>$fuel</b>.";
		} elseif($sure != 'yes') {
			get_var('Launch Confirmation','add_planetary.php',"Please <b>Confirm</b> that you want to fire your <b class=b1>Omega Missile</b> at the planet <b class=b1>$target_planet[planet_name].<p>This destination will require <b>$turns</b> Turns, and <b>$fuel</b> Fuel to prep for Launch?",'sure','');
		} else {
			charge_turns($turns);
			dbn("update ${db_name}_planets set missile=0, fuel=fuel-'$fuel' where planet_id = '$planet_id'");
			$out .= "Counting Down to Launch:<p>T Minus: 5... 4... 3... 2... 1...	<b class=b1>Liftoff</b>.";
			$out .= "<p>The <b class=b1>Omega Missile</b> has been successfully launched, with the target <b class=b1>$target_planet[planet_name]</b> as its destination.";
			$out .= "<br><br><br><br>Missile has struck the planet <b class=b1>$target_planet[planet_name]</b>. Damage report follows:";
			#fighters destroyed
			if($target_planet[fighters] >1000){
				$damage_done = round($target_planet[fighters] /100 * 4);

				$damage_done += round(rand(-$damage_done * .15,$damage_done * .15));
				$out .= "<p>The missile destroyed <b>$damage_done</b> fighters.";
			} elseif($target_planet[fighters] < 100) {
				$out .= "<p>The planet was obliterated by the missile due to lack of fighter defences.";
				$annihil = 1;
				$out .= "<p>Damage report ends;";
			} else {
				$damage_done = $target_planet[fighters];
				$out .= "<p>The missile took out all <b>$damage_done</b> fighters that were on the planet.";
			}

			if(!$annihil){
				#colonists killed
				if($target_planet['colon'] >3000){
					$colon_killed = round($target_planet['colon'] /100 * 4);
					$colon_killed += round(rand(-$colon_killed * .15,$colon_killed * .15));

					if($colon_killed > ($target_planet['colon'] - ($target_planet['alloc_fight'] + $target_planet['alloc_elect'] + $target_planet['alloc_organ']))) {

						$percent_fight = $target_planet['alloc_fight'] / (($target_planet['colon'] - ($target_planet['alloc_fight'] +$target_planet['alloc_elect']+ $target_planet['alloc_organ'])) + $target_planet['alloc_fight'] +$target_planet['alloc_elect'] + $target_planet['alloc_organ']);

						$percent_elect = $target_planet['alloc_elect'] / (($target_planet[colon] - ($target_planet['alloc_fight'] +$target_planet['alloc_elect']+ $target_planet['alloc_organ'])) + $target_planet['alloc_fight'] +$target_planet['alloc_elect'] + $target_planet['alloc_organ']);

						$percent_organ = $target_planet['alloc_organ'] / (($target_planet[colon] - ($target_planet['alloc_fight'] +$target_planet['alloc_elect'] + $target_planet['alloc_organ'])) + $target_planet['alloc_fight'] + $target_planet['alloc_elect'] + $target_planet['alloc_organ']);

						$fight_killed = round($percent_fight * $colon_killed);
						$elect_killed = round($percent_elect * $colon_killed);
						$organ_killed = round($percent_organ * $colon_killed);

						if($fight_killed < 0) {
							$fight_killed = 0;
						}
						if($elect_killed < 0) {
							$elect_killed = 0;
						}
						if($organ_killed < 0) {
							$organ_killed = 0;
						}
					} else {
						$fight_killed = 0;
						$elect_killed = 0;
						$organ_killed = 0;
					}

					$out .= "<br>The missile also killed $colon_killed colonists.";

				} elseif($target_planet[colon] >0) {
					$colon_killed = $target_planet[colon];
					$fight_killed = $target_planet['alloc_fight'];
					$elect_killed = $target_planet['alloc_elect'];
					$organ_killed = $target_planet['alloc_organ'];
					$out .= "<br>The missile also killed all $colon_killed colonists that were on the planet.";

				} else {
					$colon_killed = 0;
					$out .= "<br>The Missile failed to kill any Colonists as there were none on the planet.";
				}

				$out .= "<p>Damage report ends;";
				dbn("update ${db_name}_planets set fighters = fighters - '$damage_done', colon=colon-'$colon_killed', alloc_fight=alloc_fight-'$fight_killed', alloc_elect=alloc_elect-'$elect_killed', alloc_organ=alloc_organ-'$organ_killed' where planet_id = '$destination'");

				send_message($target_planet['login_id'],"<b class=b1>$user[login_name]</b> launched an Omega Missile at your planet <b class=b1>$target_planet[planet_name]</b> (system #<b>$target_planet[location]</b>) taking out <b>$damage_done</b> fighters, and <b>$colon_killed</b> colonists.");

			} else {
				dbn("delete from ${db_name}_planets where planet_id = '$destination'");
				dbn("update ${db_name}_stars set planetary_slots = planetary_slots + 1 where star_id = $target_planet[location]");
				send_message($target_planet['location'],"Your planet <b class=b1>$target_planet[planet_name]</b> (system #<b>$target_planet[location]</b>) was destroyed by a <b class=b1>Omega Missile</b> launched by <b class=b1>$user[login_name]</b>.");
			}

			post_news("<b class=b1>$user[login_name]</b> launched an <b class=b1>Omega Missile</b> at the planet <b class=b1>$target_planet[planet_name]</b>.");
			if($annihil == 1){
				post_news("<b class=b1>$target_planet[planet_name]</b> was annihilated by an <b class=b1>Omega Missile</b>.");
			}
		}
	}

#build a launch pad
} elseif($launch_pad) {
	$header = 'Launch Pad Construction';
	if ($user['cash'] < 100000 || $planet['elect'] < 200 ||
	     $planet['metal'] < 100 || $planet['fuel'] < 100) {
		$out .= <<<END
<p>You can not afford a Missile Launch Pad</p>
<p>Required: 100,000 cash, 100 metal, and 100 fuel.</p>

END;
	} elseif ($enable_superweapons == 0) {
		$out .= <<<END
<p>Admin has disabled the ability to use these weapons, as such, building a
missile pad is pointless.</p>

END;
	} elseif($planet[launch_pad] != 0) {
		$out .= "You already have a <b class=b1>Missile Launch Pad</b> on this planet.";
	} elseif($sure != 'yes') {
		get_var('Build Missile Launch Pad','add_planetary.php','Are you sure you want to build a <b class=b1>Missile Launch Pad</b>?','sure','yes');
	} else {
		take_cash(100000);
		$out .= "Construction of the <b class=b1>Missile Launch Pad</b> is under way.<br>It will be completed in <b>24hrs</b>.<br>Your planet has been debited the materials needed to construct the Pad.";
		dbn("update ${db_name}_planets set launch_pad = '25', elect = elect - 200, metal=metal-100,fuel=fuel-100 where planet_id = $planet[planet_id]");
	}

#build a research facility
}elseif ($flag_research == 1 && isset($research_fac)) {
	$header = "Research Facility";

	#check to see how many research centres the user has.
	db("select count(planet_id) from ${db_name}_planets where research_fac = 1 && login_id = $user[login_id]");
	$num_research = dbr();
	if($user['cash'] < $research_fac_cost) {
		$out .= "You can not afford a <b class=b1>Research Facility</b>.";
	} elseif($planet['research_fac'] != 0) {
		$out .= "You already have a <b class=b1>Research Facility</b> on this planet.";
	} elseif($num_research[0] > 1) {
		$out .= "You may only own two research centres at a time.";
	} elseif(!isset($sure)) {
		get_var('Buy Research Facility','add_planetary.php','Are you sure you want to purchase a <b class=b1>Research Facility</b>?','sure','yes');
	} else {
		take_cash($research_fac_cost);
		$out .= "You have purchased and installed a <b class=b1>Research Facility</b> on the planet <b class=b1>$planet[planet_name]</b> at the cost of <b>$research_fac_cost</b> Credits.<p>Note: You are only allowed two research centres at a time, with a maximum of 1 per planet.";
		dbn("update ${db_name}_planets set research_fac = '1' where planet_id = $planet[planet_id]");
	}

#build a shield generator
}elseif($shield_gen) {
	$header = "Shield Generator Construction";
	if($user['cash'] < $shield_gen_cost) {
		$out .= "You can not afford a <b class=b1>Shield Generator</b>.";
	} elseif($planet['shield_gen'] != 0) {
		$out .= "You already have a <b class=b1>Shield Generator</b> on this planet.";
	} elseif(!isset($sure)) {
		get_var('Buy Shield Generator','add_planetary.php','Are you sure you want to purchase a <b class=b1>Shield Generator</b>?','sure','yes');
	} else {
		take_cash($shield_gen_cost);
		$out .= "You have purchased and installed a <b class=b1>Shield Generator</b> on the planet <b class=b1>$planet[planet_name]</b> at the cost of <b>$shield_gen_cost</b> Credits.";
		dbn("update ${db_name}_planets set shield_gen = '3' where planet_id = $planet[planet_id]");
		$planet['shield_gen'] = 3;
	}

} else {
	$out = "You shouldn't be at this page without a reason";
}

print_page($header, $out . "<p><a href=\"planet.php?planet_id=$planet_id\">Back to The Planet</a></p>");

?>
