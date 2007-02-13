<?php

require_once('inc/user.inc.php');
require_once('inc/planet.inc.php');

$pQuery = $db->query('SELECT * FROM [game]_planets WHERE planet_id = ' .
 '%u AND location = %u', array($planet_id, $userShip['location']));
$planet = $db->fetchRow($pQuery);

if (!($planet = checkPlanet($planet_id))) {
	header('Location: system.php');
	exit;
}

$shield_gen_cost = 50000;
$out = "";
$header = "Error";

if ($planet['login_id'] !== $user['login_id']) {
	$out = "Only the planet owner may authorise that action.";
#build missile
} elseif (isset($missile) && $planet['launch_pad'] <= time()) {
	$header = "Omega Missile Construction";
	if ($planet['elect'] < 50 || $planet['metal'] < 200 || $planet['fuel'] < 100) { #134800
		$out .= "You can not afford a <b class=b1>Omega Missile</b>.";
	} elseif ($gameOpt['enable_superweapons'] == 0) {
		$out .= "Admin has disabled the ability to use these weapons, as such, building a missile is pointless";
	} elseif (!giveMoneyPlayer(-100000)) {
		$out .= "<p>You do not have 100000 credits.</p>";
	} else {
		$out .= "Your new <b class=b1>Omega Missile</b> is ready, and your planet has been debited the materials needed to construct the Missile.";
		dbn("UPDATE [game]_planets SET missile = missile + 1, elect = elect - 50, metal = metal - 200, fuel = fuel - 100 WHERE planet_id = $planet[planet_id]");
	}
#Fires missile
} elseif (isset($launch_missile) && $planet['launch_pad'] <= time()) {
	$header = "Launch Omega Missile";

	if ($user['turns'] < 5) {
		$out .= "You will require at least <b>5</b> turns to launch the missile, no matter the desination";
	} elseif ($gameOpt['enable_superweapons'] == 0) {
		$out .= "Admin has disabled the ability to use these weapons";
	} elseif ($user['turns_run'] < $gameOpt['turns_before_planet_attack'] && !IS_ADMIN) {
		$out .= "You have not used enough turns to be able to attack planets.<br />You need to have used <b>$gameOpt[turns_before_planet_attack]</b> or more turns.";
	} elseif ($gameOpt['flag_planet_attack'] == 0) {
		$out .= "The admin presentlty has planet attacking disabled.";
	} elseif ($planet['missile'] < 1) {
		$out .= "You do not have a missile on this planet.";
	} elseif(!isset($destination)){
		if ($user['clan_id'] !== NULL) {
			$planets = $db->query('SELECT planet_name, planet_id FROM ' .
			 '[game]_planets WHERE clan_id != %u AND login_id != %u AND ' .
			 'planet_id != %u AND location != %u AND login_id != %u',
			 array($user['clan_id'], $gameInfo['admin'], $planet_id,
			 $planet['location'], $user['login_id']));
		} else {
			$planets = $db->query('SELECT planet_name, planet_id FROM ' .
			 '[game]_planets WHERE login_id != %u AND planet_id != %u AND ' .
			 'location != %u AND login_id != %u', array($gameInfo['admin'],
			 $planet_id, $planet['location'], $user['login_id']));
		}

		if ($db->numRows($planets) > 0) {
			$out .= "<h1>Target for the missile</h1>";
			$out .= "<form method=post action=add_planetary.php name=missile_form>";
			$out .= "<input type=hidden name=launch_missile value=-1>";
			$out .= "<input type=hidden name=planet_id value=$planet_id>";
			$out .= "<p><select name=destination>";
			while ($planet = $db->fetchRow($planets)) {
				$out .= "<option value=\"$planet[planet_id]\">$planet[planet_name]</option>";
			}
			$out .= "</select></p>";
			$out .= "<p><input type=\"submit\" value=\"Launch\" class=\"button\" /></p></form>";
		} else {
			$out .= "<p>There are no planets in the game that you can fire the missile at.</p>";
		}
	} else {
		$target_planet = getPlanet($destination);
		if (!$target_planet) {
			print_page('Invalid planet', 'Invalid planet');
			exit;
		}

		$turns = ceil(getStarDist($userShip['location'],
		 $target_planet['location'])) + 1;
		$fuel = $turns * 20;

		if ($target_planet['location'] == $planet['location']) {
			$out .= "Missiles may not be fired at planets in the same system as the launching planet due to fallout concerns.";
		} elseif (($target_planet['clan_id'] == $user['clan_id'] && $user['clan_id'] !== NULL) || $target_planet['login_id'] == $user['login_id'] || $target_planet['login_id'] == $gameInfo['admin']) {
			$out .= "That planet is an invalid target.";
		} elseif ($user['turns'] < $turns) {
			$out .= "You do not have enough turns to launch your missile at that planet.<br />You have <b>$user[turns]</b> and require <b>$turns</b>.";
		} elseif ($planet['fuel'] < $fuel) {
			$out .= "You require more fuel on the planet to launch at that target.<br />You have <b>$planet[fuel]</b> and require <b>$fuel</b>.";
		} elseif (!(isset($sure) && $sure == 'yes')) {
			get_var('Launch Confirmation','add_planetary.php',"Please <b>Confirm</b> that you want to fire your <b class=b1>Omega Missile</b> at the planet <b class=b1>$target_planet[planet_name].<p>This destination will require <b>$turns</b> Turns, and <b>$fuel</b> Fuel to prep for Launch?",'sure','');
		} else {
			giveTurnsPlayer(-$turns);
			$db->query('UPDATE [game]_planets SET missile = 0, fuel = ' .
			 'fuel - %u WHERE planet_id = %u', array($fuel, $planet_id));
			$out .= "Counting Down to Launch:<p>T Minus: 5... 4... 3... 2... 1...	<b class=b1>Liftoff</b>.";
			$out .= "<p>The <b class=b1>Omega Missile</b> has been successfully launched, with the target <b class=b1>$target_planet[planet_name]</b> as its destination.";
			$out .= "<br /><br /><br /><br />Missile has struck the planet <b class=b1>$target_planet[planet_name]</b>. Damage report follows:";
			#fighters destroyed
			if ($target_planet['fighters'] > 1000) {
				$damage_done = round($target_planet['fighters'] / 25);

				$damage_done += round(mt_rand(-$damage_done, $damage_done) / 8);
				$out .= "<p>The missile destroyed <b>$damage_done</b> fighters.";
			} elseif($target_planet['fighters'] < 100) {
				$out .= "<p>The planet was obliterated by the missile due to lack of fighter defences.";
				$annihil = 1;
				$out .= "<p>Damage report ends;";
			} else {
				$damage_done = $target_planet['fighters'];
				$out .= "<p>The missile took out all <b>$damage_done</b> fighters that were on the planet.";
			}

			if (!$annihil) {
				#colonists killed
				if ($target_planet['colon'] > 3000) {
					$colon_killed = round($target_planet['colon'] /100 * 4);
					$colon_killed += round(mt_rand(-$colon_killed, $colon_killed) / 8);

					if ($colon_killed > ($target_planet['colon'] - ($target_planet['alloc_fight'] + $target_planet['alloc_elect'] + $target_planet['alloc_organ']))) {

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

					$out .= "<br />The missile also killed $colon_killed colonists.";

				} elseif($target_planet[colon] >0) {
					$colon_killed = $target_planet[colon];
					$fight_killed = $target_planet['alloc_fight'];
					$elect_killed = $target_planet['alloc_elect'];
					$organ_killed = $target_planet['alloc_organ'];
					$out .= "<br />The missile also killed all $colon_killed colonists that were on the planet.";

				} else {
					$colon_killed = 0;
					$out .= "<br />The Missile failed to kill any Colonists as there were none on the planet.";
				}

				$out .= "<p>Damage report ends;";
				dbn("update [game]_planets set fighters = fighters - '$damage_done', colon=colon-'$colon_killed', alloc_fight=alloc_fight-'$fight_killed', alloc_elect=alloc_elect-'$elect_killed', alloc_organ=alloc_organ-'$organ_killed' where planet_id = '$destination'");

				msgSendSys($target_planet['login_id'],"<b class=b1>$user[login_name]</b> launched an Omega Missile at your planet <b class=b1>$target_planet[planet_name]</b> (system #<b>$target_planet[location]</b>) taking out <b>$damage_done</b> fighters, and <b>$colon_killed</b> colonists.");

			} else {
				$db->query('DELETE FROM [game]_planets WHERE planet_id = %u', array($destination));
				dbn("update [game]_stars set planetary_slots = planetary_slots + 1 where star_id = $target_planet[location]");
				msgSendSys($target_planet['login_id'], "Your planet <b class=b1>$target_planet[planet_name]</b> (system #<b>$target_planet[location]</b>) was destroyed by a <b class=b1>Omega Missile</b> launched by <b class=b1>$user[login_name]</b>.");
			}

			post_news("<b class=b1>$user[login_name]</b> launched an <b class=b1>Omega Missile</b> at the planet <b class=b1>$target_planet[planet_name]</b>.");
			if($annihil == 1){
				post_news("<b class=b1>$target_planet[planet_name]</b> was annihilated by an <b class=b1>Omega Missile</b>.");
			}
		}
	}

#build a launch pad
} elseif(isset($launch_pad)) {
	$header = 'Launch Pad Construction';
	if ($gameOpt['enable_superweapons'] == 0) {
		$out .= <<<END
<p>Admin has disabled the ability to use these weapons, as such, building a
missile pad is pointless.</p>

END;
	} elseif ($planet['launch_pad'] != 0) {
		$out .= "You already have a <b class=b1>Missile Launch Pad</b> on this planet.";
	} elseif ($planet['metal'] < 200 || $planet['fuel'] < 100 || !giveMoneyPlayer(-100000)) {
		$out .= <<<END
<h2>You can not afford a Missile Launch Pad</h2>
<p>200 metal, 100 fuel and 100000 credits are required.</p>

END;
	} else {
		$out .= "Construction of the <b class=b1>Missile Launch Pad</b> is under way.<br />It will be completed in <b>24hrs</b>.<br />Your planet has been debited the materials needed to construct the Pad.";

		$db->query('UPDATE [game]_planets SET launch_pad = %u, metal = ' .
		 'metal - 200, fuel = fuel - 100 WHERE planet_id = %u',
		 array(time() + 60 * 60 * 24, $planet['planet_id']));
	}
// build a shield generator
} elseif (isset($shield_gen)) {
	$header = "Shield Generator Construction";
	if($planet['shield_gen'] != 0) {
		$out .= "You already have a <em>shield generator</em> on this planet.";
	} elseif(!isset($sure)) {
		get_var('Buy Shield Generator', 'add_planetary.php', 'Are you sure you want to purchase a <em>shield generator</em>?', 'sure', 'yes');
	} elseif (!giveMoneyPlayer(-$shield_gen_cost)) {
		$out .= "You can not afford a <em>shield generator</em>.";
	} else {
		$out .= "You have purchased and installed a <em>shield generator</em> on the planet <em>$planet[planet_name]</em> at the cost of $shield_gen_cost credits.";
		dbn("UPDATE [game]_planets SET shield_gen = 3 WHERE planet_id = $planet[planet_id]");
		$planet['shield_gen'] = 3;
	}
} else {
	$out = "You shouldn't be at this page without a reason";
}

print_page($header, $out . "<p><a href=\"planet.php?planet_id=$planet_id\">Return to planet</a></p>");

?>
