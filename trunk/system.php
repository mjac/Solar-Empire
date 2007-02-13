<?php

function getStarLinks()
{
	global $links, $star;

	$num = array();
	for ($i = 1; $i <= 6; ++$i) {
		$to = $star['link_' . $i];
		if ($to != 0) {
			$num[] = $to;
		}
	}

	$info = $db->query('SELECT star_id, x, y FROM [game]_stars WHERE star_id = ' . 
	 implode(' OR star_id = ', $num));

	$links = array();
	while ($s = $db->fetchRow($info)) {
		$linkTo =& $links[];
		$linkTo['id'] = (int)$s['id'];
		$linkTo['x'] = (double)$s['x'] - (double)$star['x'] + (200 / 2);
		$linkTo['y'] = (double)$s['y'] - (double)$star['y'] + (200 / 2);
	}
}

function isLinked(&$star, $linkNo)
{
	switch ($linkNo) {
		case $star['link_1']:
		case $star['link_2']:
		case $star['link_3']:
		case $star['link_4']:
		case $star['link_5']:
		case $star['link_6']:
		case $star['wormhole']:
			return true;
	}

	return false;
}
function getAutowarp()
{
	global $autowarp, $star;

	if (empty($autowarp)) {
		return false;
	}

	$path = explode(',', $autowarp);
	foreach ($path as $id => $location) {
		$path[$id] = (int)$location;
	}

	if (!isLinked($star, $path[0])) {
		return false;
	}

	return $path;
}

function assignEquip(&$tpl)
{
	global $user;

	$tpl->assign('equip', array(
		'genesis' => $user['genesis'],
		'alpha' => $user['alpha'],
		'gamma' => $user['gamma'],
		'delta' => $user['delta']
	));
}

function assignStar(&$tpl)
{
	global $links, $star, $gameInfo;

	$tpl->assign('star', array(
		'id' => $star['id'],
		'map' => URL_BASE . '/img/' . $gameInfo['db_name'] . '_maps/sm' . 
		 $star['id'] . '.png',
		'links' => $links
	));
}

function systemInfo()
{
	global $user, $userOpt, $userShip, $star, $gameOpt, $metal_str,
	 $fuel_str, $links, $self;

	$bar = <<<END
<h1>$star[star_name] #$userShip[location]</h1>
<table class="simple">

END;
	if ($gameOpt['uv_planet_slots_use']) {
		$bar .= "\t<tr>\n\t\t<th>Planetary slots</th>\n\t\t<td>" .
		 $star['planetary_slots'] . "</td>\n\t</tr>\n";
	}
	$bar .= "\t<tr>\n\t\t<th>Warp Links</th>\n\t\t<td>";

	// ensure user is in a system that exists.
	if (empty($links)) {
		$bar .= 'No links';
	} else {
		$bar .= preg_replace('/(\\d+)/', 
		 "&lt;<a href=\"$self?toloc=\\1\">\\1</a>&gt;", implode(' ', $links));
	}
	$bar .= " - <a href=\"system_autowarp.php\">Autowarp</a></td>\n\t</tr>\n"; #end warp links

	// autowarp
	if (($path = getAutowarp()) !== false) {
		$todo = $path;
		$next = array_shift($todo);

		$path[0] = "<a href=\"$self?toloc=$next" .
		 (empty($todo) ? '' : '&amp;autowarp=' . implode(',', $todo)) .
		 "\">$next</a>";

		$bar .= "\t<tr>\n\t\t<th>Autowarp (" . count($path) . ")</th>\n" .
		 "\t\t<td>" . implode(' &raquo; ', $path) . "</td>\n\t</tr>\n";
	}

	if (!empty($star['wormhole'])) {
		$bar .= <<<END
	<tr>
		<th>Wormhole</th>
		<td><a href="$self?toloc=$star[wormhole]">$star[wormhole]</a></td>
	</tr>

END;
	}

	// metal and fuel
	if ($star['metal'] > 0) {
		$bar .= "<tr><th>Metal</th><td>" . number_format($star['metal']) .
		 "</td></tr>";
	}
	if ($star['fuel'] > 0) {
		$bar .= "<tr><th>Fuel</th><td>" . number_format($star['fuel']) .
		 "</td></tr>";
	}

	$bar .= "</table>\n";

	return $bar;
}




require('inc/user.inc.php');
require('inc/template.inc.php');
require('inc/attack.inc.php');

if (deathCheck($user)) {
	deathInfo($user);
}

getStar();
if (!$star) {
	assignCommon($tpl);
	$tpl->display('game/system_missing.tpl.php');
	exit;
}
getStarLinks();





assignStar($tpl);
assignEquip($tpl);
assignCommon($tpl);

$tpl->display('game/system.tpl.php');

exit;

// command a different ship
if (isset($command)) {
	$tQuery = $db->query('SELECT * FROM [game]_ships WHERE ship_id = %u ' .
	 'AND login_id = %u', array($command, $user['login_id']));

	if ($db->numRows($tQuery) > 0) {
		$toShip = $db->fetchRow($tQuery);
		if ($toShip['location'] != $userShip['location']) {
			$dist = getStarDist($userShip['location'], $toShip['location']);
			if ($user['turns'] < 2) {
				print_page('Command Failed', "You are not able to take command of this ship remotely, as it would require <b>$dist</b> turns and you only have <b>$user[turns]</b><p>");
			} else {
				giveTurnsPlayer(-2);
				$header = "Remote Command";
				$out .= "<p>Command Transfered at a cost of two turns</p>\n";
			}
		} else {
			$out .= "<p>Command Transfered.</p>";
		}

		$db->query('UPDATE [game]_users SET ship_id = %u WHERE ' .
		 'login_id = %u', array($command, $user['login_id']));
		$user['ship_id'] = $command;

		checkShip();
	}
}

//Transwarp Burst
if (!empty($transburst)) {
	if (!shipHas($userShip, 'tw')) {
		print_page("Transwarp","Your ship is not equipped with a Transwarp Drive.");
	} elseif ($user['turns'] < 15) {
		print_page("Transwarp","You need <b>15</b> turns to use Transwarp Burst");
	} elseif(!isset($sure)) {
		get_var('Transwarp Burst', $self, "Are you sure you want to engage the <b class=b1>Transwarp Burst</b>? This will cost <b>15</b> turns and send you and any towed ships to a random location.",'sure','yes');
	}

	giveTurnsPlayer(-15);
	$new_loc = randomSystemNo($user['login_id'], $user['clan_id']); #make a random system number up.

	$db->query('UPDATE [game]_ships SET location = %u, task = \'none\' ' .
	 'WHERE ship_id = %u', array($new_loc, $user['ship_id']));

	$db->query('UPDATE [game]_ships SET location = %u, task = \'none\' ' .
	 'WHERE towed_by = %u AND location = %u', array($new_loc,
	 $userShip['ship_id'], $userShip['location']));

	$out .= "You and all towed ships have ended up in system <b class=b1>#$new_loc</b>";

	checkShip();
	getStar();
	getStarLinks();
}

// Transwarp
if (!empty($transwarp) && $userShip !== NULL) {
	$num_towed = towedByShip($userShip);

	$tw_distance = 100;
	$transwarp = (int)$transwarp;

	$turn = floor(getStarDist($userShip['location'], $transwarp) >> 4);
	if(!shipHas($userShip, 'tw')) {
		print_page("Transwarp", "Your ship is not equipped with a transwarp drive.");
	} elseif($transwarp == $userShip['location']) {
		$out .= "<p>You're already there!</p>";
	} elseif(!starExists($transwarp)) {
		print_page("Transwarp", "System does not exist.");
	} elseif(getStarDist($userShip['location'], $transwarp) > $tw_distance) {
		print_page("Transwarp", "Your Transwarp drive cannot warp that far. Maximum Transwarp distance of $tw_distance Light Years.");
	} elseif(!giveTurnsPlayer(-$turn)) {
		print_page("Transwarp", "You need <b>$turn</b> turns to warp that far.");
	} else {
		moveUserTo($transwarp);

		getStar();
		getStarLinks();
	}
}

//subspace jump
if(!empty($subspace)) {
	$towing = $db->query('SELECT COUNT(*) FROM [game]_ships WHERE ' .
	 'towed_by = %u AND location = %u AND login_id = %u',
	 array($userShip['ship_id'], $userShip['location'], $user['login_id']));
	$num_towed = (int)current($db->fetchRow($towing));

	$turn = floor(getStarDist($userShip['location'], $subspace) >> 4);
	if(!shipHas($userShip, 'sj')) {
		print_page("Sub-Space","This does not have a Sub-Space Jump Drive.");
	} elseif($subspace == $userShip['location']) {
		$out = "You're already there!";
	} elseif($user['turns'] < $turn) {
		print_page("Sub-Space","You need <b>$turn</b> turns to get that far");
	} elseif($num_towed > 10 && !shipHas($userShip, 'ws')) {
		print_page("Sub-Space","You can only tow <b>10 </b>ships through subspace, you are towing <b>$num_towed</b>.<br /><br />To have unlimited tow capability, purchase and install the <b class=b1>Wormhole Stabiliser</b> Upgrade.");
	} elseif(!starExists($subspace)) {
		print_page("Sub-Space","Where are you trying to go? That location doesn't exist. You can only go from systems <b>1</b> to <b>$num_ss[0] </b>using any form of transport.");
	} else {
		giveTurnsPlayer(-$turn);

		moveUserTo($subspace);

		getStar();
		getStarLinks();
	}
}

// Process page location command if given
if(isset($toloc)) {
	$toloc = (int)$toloc;

	// Determined by largest ship in fleet
	if ($gameOpt['ship_warp_cost'] < 0) {
		$db->query("SELECT move_turn_cost FROM [game]_ships WHERE (login_id = '$user[login_id]' AND location = '$userShip[location]' AND (towed_by = '$user[ship_id]') OR ship_id = '$user[ship_id]') order by move_turn_cost desc limit 1");
		$move_turn_cost_fleet = dbr();
		$warp_cost = $move_turn_cost_fleet['move_turn_cost']; #set it to warp_cost so can keep generic
	} else {#warp cost is set by admin
		$warp_cost = $gameOpt['ship_warp_cost']; #set to warp_cost so as to keep generic
	}

	if ($user['turns'] < $gameOpt['ship_warp_cost'] && $gameOpt['ship_warp_cost'] > 0 && !IS_ADMIN) {
		$out = "Sorry, you can't move because you have less than <b>$gameOpt[ship_warp_cost]</b> turn(s). <br />This is the present turn cost to move between systems, as set by the <b class=b1>Admin</b>.<p>";
	} elseif ($gameOpt['ship_warp_cost'] < 0 && $user['turns'] < $warp_cost && !IS_ADMIN) {
		$out = "Sorry, you can't more because you have less than <b>$warp_cost</b> turn(s).<br />This is the amount of turns required to move the largest ship in your present fleet.<br />Differernt ships use different amounts of turns to move between systems. See the help for more information.";
	} else {
		if ($toloc == $star['wormhole'] && attack_planet_check($user) > 0) {
			$out .= "It is not possible to get to the wormhole to jump to that system, because the hostile fighters in this system get in the way.";
		} elseif (!starExists($toloc)) {
			$out = "<p>That system does not exist.</p>";
		} elseif ($toloc == $userShip['location']) {
			$out = "<p>You are already there.</p>";
		} elseif (!(isLinked($star, $toloc) || IS_ADMIN)) {
			$out = "<p>This star system does not have a link to (#<b>$toloc</b>).</p>";
		} else {
			giveTurnsPlayer(-$warp_cost);

			moveUserTo($toloc);

			getStar();
			getStarLinks();
		}
	}
}


if(isset($jettison)) {
	if (!isset($sure)) {
		get_var('Jettison Cargo', $self, 'Are you sure you want to Jettison all Cargo in this ship?','sure','yes');
	} else {
		$db->query('UPDATE [game]_ships set metal = 0, fuel = 0, ' .
		 'elect = 0, organ = 0, colon = 0 WHERE ship_id = %u',
		 array($user['ship_id']));
		$out .= "<p>Cargo Jettisoned</p>";
		checkShip();
	}
}

if (isset($action) && isset($ship) &&
     ((is_array($ship) && count($ship) > 0) || is_numeric($ship))) {
	if (is_numeric($ship)) {
		$ship = array($ship);
	}

	switch ($action = strtolower($action)) {
		case 'tow':
			$args = array($user['ship_id'], $user['login_id'], $user['ship_id']);
			foreach ($ship as $id) {
				$args[] = (int)$id;
			}

			$result = $db->query('UPDATE [game]_ships SET towed_by = %u ' .
			 'WHERE login_id = %u AND ship_id != %u AND (ship_id = %u' .
			 str_repeat(' OR ship_id = %u', count($ship) - 1) . ')', $args);

			if (($changed = $db->affectedRows($result)) > 0) {
				$out .= "<p>$changed ship(s) have been towed.</p>\n";
			}
			break;

		case 'release':
			$args = array($user['login_id']);
			foreach ($ship as $id) {
				$args[] = (int)$id;
			}

			$result = $db->query('UPDATE [game]_ships SET towed_by = NULL ' .
			 'WHERE login_id = %u AND (ship_id = %u' .
			 str_repeat(' OR ship_id = %u', count($ship) - 1) . ')', $args);

			if (($changed = $db->affectedRows($result)) > 0) {
				$out .= "<p>$changed ship(s) have been released.</p>\n";
			}
			break;

		case 'destroy':
			$args = array($user['login_id'], $user['ship_id']);
			foreach ($ship as $id) {
				$args[] = (int)$id;
			}

			$result = $db->query('DELETE FROM [game]_ships WHERE ' .
			 'login_id = %u AND ship_id != %u AND (ship_id = %u' .
			 str_repeat(' OR ship_id = %u', count($ship) - 1) . ')', $args);

			if (($changed = $db->affectedRows($result)) > 0) {
				$out .= "<p>$changed ship(s) have been destroyed.</p>\n";
				post_news("$user[login_name] self-destructed $changed ship(s)");
			}
			break;

		case 'assign':
			if (isset($task)) {
				switch ($task = strtolower($task)) {
					case 'mine-metal':
					case 'mine-fuel':
						$mode = $task === 'mine-metal' ? 'metal' : 'fuel';
						$out .= "<p>Ships are mining $mode.</p>\n";
						$args = array('mine', $mode, $user['login_id']);
						foreach ($ship as $id) {
							$args[] = (int)$id;
						}

						$db->query('UPDATE [game]_ships SET task = \'%s\', ' .
						 'mining_mode = \'%s\' WHERE login_id = %u AND ' .
						 '(ship_id = %u' . str_repeat(' OR ship_id = %u',
						 count($ship) - 1) . ')', $args);
						break;

					case 'none':
					case 'patrol':
					case 'defend':
					case 'defend-fleet':
					case 'defend-planet':
						$out .= "<p>Ships have been assigned.</p>\n";
						$args = array($db->escape($task), $user['login_id']);
						foreach ($ship as $id) {
							$args[] = (int)$id;
						}

						$db->query('UPDATE [game]_ships SET task = \'%s\' ' .
						 'WHERE login_id = %u AND (ship_id = %u' .
						 str_repeat(' OR ship_id = %u', count($ship) - 1) .
						 ')', $args);
						break;
				}
			}
	}
}


$out .= systemInfo();


if($userShip['location'] == 1){ //system 1. Only earth
	$out .= "<h2 class=\"earth\"><a href=\"earth.php\" title=\"Land on Earth\">Planet Earth</a></h2>\n";
}

// ports
$ports = $db->query('SELECT port_id, metal_bonus FROM [game]_ports ' .
 'WHERE location = %u', array($userShip['location']));
while ($port = $db->fetchRow($ports)) {
	$out .= "<h2 class=\"port\"><a href=\"shop_port.php?port_id=$port[port_id]\">Galactic port $port[port_id]</a></h2>\n";
}

/**********************
* Planet Listings
**********************/

$temp_str = "";
$temp2_str = "";

$planets = $db->query('SELECT p.planet_id, p.planet_name, ' .
 'p.fighters, u.login_id, u.login_name, u.turns_run, c.symbol AS ' .
 'clan_sym, c.sym_color AS clan_sym_color, c.clan_id, p.pass ' .
 'FROM [game]_planets AS p LEFT JOIN [game]_users AS u ON ' .
 'p.login_id = u.login_id LEFT JOIN [game]_clans AS c ON ' .
 'u.clan_id = c.clan_id WHERE p.location = %u ORDER BY ' .
 'planet_name ASC, fighters DESC', array($userShip['location']));

while ($planet = $db->fetchRow($planets)) {
	if($planet['login_id'] === $user['login_id']){ # separate user planets from other planets
		$temp2_str .= "<p><strong>$planet[planet_name]</strong> ($planet[fighters] fighters) - <a href=planet.php?planet_id=$planet[planet_id]>Land</a></p>";
	} else { #other players planets
		$temp_str .= "<p>" . ($planet['login_id'] === NULL ? 'Deserted' :
		 print_name($planet)) . " <strong>$planet[planet_name]</strong> ($planet[fighters] fighters)";

		if (($planet['clan_id'] === $user['clan_id'] &&
		     $planet['clan_id'] !== NULL) || IS_ADMIN ||
		     ($planet['fighters'] == 0) || $user['ship_id'] === NULL) {
			$temp_str .= " - <a href=\"planet.php?planet_id=$planet[planet_id]\">Land</a>";
		} else {
			if ($gameOpt['flag_planet_attack'] != 0 && canAttackPlanet($planet)) {
				$temp_str .= " - <a href=attack_planet.php?target=$planet[planet_id]>Attack</a>";
				if (shipHas($userShip, 'sv')) { //quark disrupter
					$temp_str .= " - <a href=attack_planet.php?quark=1&target=$planet[planet_id]>Fire Quark Displacer</a>";
				}
				if (shipHas($userShip, 'sw') && $gameOpt['enable_superweapons'] == 1) { //terra maelstrom
					$temp_str .= " - <a href=attack_planet.php?terra=1&target=$planet[planet_id]>Fire Terra Maelstrom</a>";
				}
				if($planet['pass'] !== '') {
					$temp_str .= " - <a href=planet.php?planet_id=$planet[planet_id]>Have Pass</a>";
				}
			}
		}
		$temp_str .= "</p>\n";
	}
}

#determine if user has any planets in the system
if(!empty($temp2_str)){
	$out .= "<h2 class=\"planet\">Your planets</h2>" . $temp2_str;
}
if(!empty($temp_str)){
	$out .= "<h2 class=\"planet\">Other planets</h2>" . $temp_str;
}



/**********************
* Player Ship Listings
**********************/

$shipCount = $db->query('SELECT COUNT(*) FROM [game]_ships WHERE ' .
 'login_id = %u AND location = %u', array($user['login_id'],
 $userShip['location']));
$count = (int)current($db->fetchRow($shipCount));


/* HANDLE USER SHIPS */
if (isset($show_user_ships)) {
	if ($show_user_ships == 1) {
		$user['show_user_ships'] = 1;
		$db->query('UPDATE [game]_users SET show_user_ships = 1 WHERE ' .
		 'login_id = %u', array($user['login_id']));
	} elseif ($show_user_ships == 2) {
		$user['show_user_ships'] = 0;
		$db->query('UPDATE [game]_users SET show_user_ships = 0 WHERE ' .
		 'login_id = %u', array($user['login_id']));
	}
}


$out .= "<h2 class=\"ship\">" . ($user['show_user_ships'] == 1 ?
 "<a href=\"$self?show_user_ships=2\" title=\"Show summary\">Full listing</a>" :
 "<a href=\"$self?show_user_ships=1\" title=\"Show full listing\">Summary</a>") .
 " of your $count ship(s)</h2>\n";



/* SHOW FULL LIST OF USER SHIPS */
if ($user['show_user_ships'] == 1) {
	$out .= <<<END
<form method="get" action="$self" id="ship_towing">
	<p><input type="button" onclick="tickInvert('ship_towing'); return false;" value="Invert" class="button" /> -
	<input type="submit" name="action" value="Tow" class="button" />
	<input type="submit" name="action" value="Release" class="button" /> -
	<select name="task">
		<option value="none">No task</option>
		<optgroup label="Attack">
			<option value="patrol">Patrol</option>
		</optgroup>
		<optgroup label="Defend">
			<option value="defend">Self</option>
			<option value="defend-fleet">Fleet</option>
			<option value="defend-planet">Planets</option>
		</optgroup>
		<optgroup label="Mine">
			<option value="mine-metal">Metal</option>
			<option value="mine-fuel">Fuel</option>
		</optgroup>
	</select>
	<input type="submit" name="action" value="Assign" class="button" /> -
	<input type="submit" name="action" value="Destroy" class="button"
	 onclick="return confirm('Are you sure?');" /></p>

	<table class="shipListing">
		<tr>
			<th>Name</th>
			<th>Class</th>
			<th>Hull</th>
			<th>Shields</th>
			<th>Fighters</th>
			<th>Specials</th>
			<th>Task</th>
			<th>Towed-by</th>
		</tr>

END;

	$ships = $db->query('SELECT s.ship_id, s.ship_name, s.config, s.hull, s.shields, s.fighters, s.towed_by, s.config, s.task, s.mining_mode, t.name AS class_name, t.abbr AS class_name_abbr, b.ship_name AS tower FROM [game]_ships AS s LEFT JOIN [game]_ship_types AS t ON s.type_id = t.type_id LEFT JOIN [game]_ships AS b ON s.towed_by = b.ship_id WHERE s.login_id = %u AND s.location = %u ORDER BY ship_name', array($user['login_id'], $userShip['location']));

	#Loop through all of a players ships in the system.
	while ($ship = $db->fetchRow($ships)) {
		// Ship is cloaked.
		$styleExtra = shipHas($ship, 'ls') || shipHas($ship, 'hs') ?
		 ' class="cloaked"' : '';
		$link = $ship['ship_id'] == $user['ship_id'] ? '' :
		 " href=\"$self?command=$ship[ship_id]\" title=\"Command\"";

		// Abbreviate or not
		$class = $userOpt['show_abbr_ship_class'] ? $ship['class_name_abbr'] :
		 $ship['class_name'];

		$tower = $ship['tower'] === NULL ? '-' :
		 "<a href=\"#ship_$ship[towed_by]\">$ship[tower]</a>";

		$task = $ship['task'] === 'mine' ? "mine $ship[mining_mode]" :
		 $ship['task'];

		$out .= <<<END
		<tr$styleExtra>
			<td><input type="checkbox" name="ship[]" value="$ship[ship_id]" />
			<a id="ship_$ship[ship_id]"$link>$ship[ship_name]</a></td>
			<td>$class</td>
			<td>$ship[hull]</td>
			<td>$ship[shields]</td>
			<td>$ship[fighters]</td>
			<td>$ship[config]</td>
			<td>$task</td>
			<td>$tower</td>
		</tr>

END;
	}

		$out .= <<<END
	</table>
</form>

END;
} else { // SHOW SUMMARY OF USER SHIPS
	$ships = $db->query('SELECT t.name, COUNT(s.ship_id), SUM(s.hull), SUM(s.shields), SUM(s.fighters) FROM [game]_ships AS s LEFT JOIN [game]_ship_types AS t ON s.type_id = t.type_id WHERE s.location = %u AND s.login_id = %u GROUP BY t.type_id ORDER BY t.name', array($userShip['location'], $user['login_id']));

	$out .= <<<END
<table class="shipListing">
	<tr>
		<th>Class</th>
		<th>Count</th>
		<th>Hull</th>
		<th>Shields</th>
		<th>Fighters</th>
	</tr>

END;

	while ($ship = $db->fetchRow($ships, ROW_NUMERIC)) {
		$out .= <<<END
	<tr>
		<td>$ship[0]</td>
		<td>$ship[1]</td>
		<td>$ship[2]</td>
		<td>$ship[3]</td>
		<td>$ship[4]</td>
	</tr>

END;
	}
	$out .= "</table>\n";
}

$shipCount = $db->query('SELECT COUNT(*) FROM [game]_ships WHERE ' .
 'login_id != %u AND location = %u', array($user['login_id'],
 $userShip['location']));
$count = (int)current($db->fetchRow($shipCount));


/* HANDLE ENEMY SHIPS */
if (isset($show_enemy_ships)) {
	if ($show_enemy_ships == 1) {
		$user['show_enemy_ships'] = 1;
		$db->query('UPDATE [game]_users SET show_enemy_ships = 1 WHERE ' .
		 'login_id = %u', array($user['login_id']));
	} elseif ($show_enemy_ships == 2) {
		$user['show_enemy_ships'] = 0;
		$db->query('UPDATE [game]_users SET show_enemy_ships = 0 WHERE ' .
		 'login_id = %u', array($user['login_id']));
	}
}


$enemyShips = '';
if ($user['show_enemy_ships'] == 1) { // SHOW FULL LIST OF ENEMY SHIPS
	$ships = $db->query('SELECT s.ship_id, s.ship_name, s.login_id, s.hull, s.shields, s.fighters, s.config, t.name AS class_name, t.abbr AS class_name_abbr, u.login_name, u.clan_id, c.symbol AS clan_sym, c.sym_color AS clan_sym_color, u.turns_run FROM [game]_ships AS s LEFT JOIN [game]_users AS u ON s.login_id = u.login_id LEFT JOIN [game]_clans AS c ON u.clan_id = c.clan_id LEFT JOIN [game]_ship_types AS t ON t.type_id = s.type_id WHERE s.location = %u AND s.login_id != %u ORDER BY c.symbol, u.login_name, s.fighters DESC', array($userShip['location'], $user['login_id']));

	if ($db->numRows($ships) > 0) {
		$enemyShips .= <<<END
<table class="shipListing">
	<tr>
		<th>Owner</th>
		<th>Name</th>
		<th>Class</th>
		<th>Fighters</th>
	</tr>

END;
		$anonymous = '';
		while ($ship = $db->fetchRow($ships)) {
			$class = $userOpt['show_abbr_ship_class'] ?
			 $ship['class_name_abbr'] : $ship['class_name'];

			// Ship is cloaked.
			$styleExtra = shipHas($ship, 'ls') || shipHas($ship, 'hs') ?
			 ' class="cloaked"' : '';

			$shipName = canAttackShip($ship) ?
			 "<a href=\"attack_ship.php?target=$ship[ship_id]\" " .
			 "title=\"Attack\">$ship[ship_name]</a>" : $ship['ship_name'];

			if (!(shipHas($ship, 'ls') || shipHas($ship, 'hs')) ||
				 shipHas($userShip, 'sc') || ($ship['clan_id'] == $user['clan_id'] &&
				 $user['clan_id'] !== NULL) || IS_ADMIN) {
				$shipName = canAttackShip($ship) ?
				 "<a href=\"attack_ship.php?target=$ship[ship_id]\" " .
				 "title=\"Attack\">$ship[ship_name]</a>" : $ship['ship_name'];
				$owner = print_name($ship);
				$enemyShips .= <<<END
	<tr$styleExtra>
		<td>$owner</td>
		<td>$shipName</td>
		<td>$class</td>
		<td>$ship[fighters]</td>
	</tr>

END;
			} elseif (shipHas($ship, 'ls')) {
				$class = canAttackShip($ship) ?
				 "<a href=\"attack_ship.php?target=$ship[ship_id]\" " .
				 "title=\"Attack\">$class</a>" : $class;
				$anonymous .= <<<END
	<tr$styleExtra>
		<td colspan="2"><em>Unknown</em></td>
		<td>$class</td>
		<td>$ship[fighters]</td>
	</tr>

END;
			} else {
				$size = discern_size($ship['hull']);
				$anonymous .= <<<END
	<tr$styleExtra>
		<td colspan="4">$size disturbance detected</td>
	</tr>

END;
			}
		}

		$enemyShips .= $anonymous . "</table>\n";
	}
} else { // SHOW SUMMARY OF ENEMY SHIPS
	$getCloaked = shipHas($userShip, 'sc') || IS_ADMIN ? '' :
	 ' AND config NOT LIKE \'%%ls%%\' AND config NOT LIKE \'%%hs%%\'';

	$ships = $db->query('SELECT COUNT(*) AS total, SUM(s.fighters) ' .
	 'AS fighters, s.login_id, u.login_name, u.clan_id, ' .
	 'c.symbol AS clan_sym, c.sym_color AS clan_sym_color, ' .
	 'u.turns_run FROM [game]_ships s  LEFT JOIN  [game]_users AS ' .
	 'u ON u.login_id = s.login_id LEFT JOIN [game]_clans AS c ' .
	 'ON u.clan_id = c.clan_id WHERE s.location = %u AND ' .
	 's.login_id != %u' . $getCloaked . ' GROUP BY login_id ORDER BY ' .
	 'c.symbol, u.login_name', array($userShip['location'],
	 $user['login_id']));

	if ($db->numRows($ships) > 0) {
		$enemyShips .= <<<END
<table class="shipListing">
	<tr>
		<th>Owner</th>
		<th>Count</th>
		<th>Fighters</th>
	</tr>

END;
		while ($ship = $db->fetchRow($ships)) {
			$name = print_name($ship);
			$enemyShips .= <<<END
	<tr>
		<td>$name</td>
		<td>$ship[total]</td>
		<td>$ship[fighters]</td>
	</tr>

END;
		}

		$enemyShips .= "</table>\n";
	}
}

if (!empty($enemyShips)) {
	$out .= "<h2 class=\"ship\">" . ($user['show_enemy_ships'] == 1 ?
	 "<a href=\"$self?show_enemy_ships=2\" title=\"Show summary\">Full listing</a>" :
	 "<a href=\"$self?show_enemy_ships=1\" title=\"Show full listing\">Summary</a>") .
	 " of the $count other ship(s)</h2>\n$enemyShips";
}

$out = locationBar() . "<div id=\"locInfo\">$out</div>";

print_page($header, $out);

?>
