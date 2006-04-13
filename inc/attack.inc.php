<?php

define('SHIP_DEAD', 1);
define('SHIP_TRANSFERRED', 2);
define('SHIP_ESCAPED', 4);
define('SHIP_UNCHANGED', 8);

function canAttackShip($ship)
{
	global $user, $userShip, $gameInfo, $gameOpt;

	return IS_ADMIN || ($user['turns_run'] > $gameOpt['turns_safe'] &&
	 $ship['turns_run'] > $gameOpt['turns_safe'] &&
	 ($user['clan_id'] === NULL || $user['clan'] != $ship['clan_id']) &&
	 (!shipHas($ship, 'hs') || shipHas($userShip, 'sc')) &&
	 $user['turns_run'] > $gameOpt['turns_before_attack'] && 
	 $user['ship_id'] !== NULL &&
	 ($userShip['location'] != 1 || $gameOpt['flag_sol_attack'] == 1) &&
     !(shipHas($userShip, 'po') || shipHas($userShip, 'na')) &&
	 $gameOpt['flag_space_attack'] == 1);
}

function canAttackPlanet($planet)
{
	global $user, $userShip, $gameInfo, $gameOpt;

	return IS_ADMIN || ($user['turns_run'] > $gameOpt['turns_safe'] &&
	 $planet['turns_run'] > $gameOpt['turns_safe'] && 
	 ($user['clan_id'] === NULL || $user['clan'] != $planet['clan_id']) &&
	 $user['turns_run'] > $gameOpt['turns_before_attack'] && 
	 $user['ship_id'] !== NULL &&
	 ($userShip['location'] != 1 || $gameOpt['flag_sol_attack'] == 1) &&
	 $gameOpt['flag_planet_attack'] == 1);
}

function attackSway($hit)
{
	// anywhere between +- 1/2
	return floor(mt_rand($hit * 0.5, $hit * 1.5));
}

function deadShip($ship)
{
	global $db;

	$result = SHIP_DEAD;

	$db->query('DELETE FROM [game]_ships WHERE ship_id = %u',
	 array($ship['ship_id']));

	// update last attack by

	$location = $db->query('SELECT x, y FROM [game]_stars WHERE ' .
	 'star_id = %u', array($ship['location']));

	$pos = $db->fetchRow($location, ROW_NUMERIC);

	$newId = closestShip($ship['login_id'], $pos[0], $pos[1]);
	if ($newId === false && $ship['auxiliary_ship'] === NULL) {
		$db->query('UPDATE [game]_users SET ship_id = NULL WHERE ' .
		 'login_id = %u', array($ship['login_id']));
		return $result;
	}

	if ($newId === false) {	
		$result |= SHIP_ESCAPED;

		$type = load_ship_types();
		$new = $type[$ship['auxiliary_ship']];

		$new['location'] = random_system_num($ship['login_id']);
		$new['ship_name'] = 'Escape-ship';

		$newId = make_ship($new, $ship);
	} else {
		$result |= SHIP_TRANSFERRED;
	}

	$db->query('UPDATE [game]_users SET ship_id = %u WHERE ' .
	 'login_id = %u', array($newId, $ship['login_id']));

	return $result;
}

function updateArray(&$array, &$original, &$types, &$values)
{
	global $db;

	foreach ($array as $n => $v) {
	    if (!(isset($original[$n]) && $v != $original[$n])) {
	        continue;
	    }
		switch (gettype($v)) {
		    case 'integer':
		        $types[] = "$n = %d";
		        $values[] = $v;
		        break;

		    case 'double':
		        $types[] = "$n = %f";
		        $values[] = $v;
		        break;

			default:
		        $types[] = "$n = '%s'";
		        $values[] = $db->escape($v);
		}
	}

	return !empty($types);
}

function updateShip($ship, $original)
{
	global $db;

	if ($ship['hull'] == 0) {
	    return deadShip($ship);
	}

	$types = array();
	$values = array();

	if (!updateArray($ship, $original, $types, $values)) {
	    return SHIP_UNCHANGED;
	}

	$values[] = $ship['ship_id'];

    $db->query('UPDATE [game]_ships SET ' . implode(', ', $types) .
	 ' WHERE ship_id = %u', $values);
}

function updatePlanet($planet, $original)
{
	global $db;

	$types = array();
	$values = array();

	if (!updateArray($planet, $original, $types, $values)) {
	    return SHIP_UNCHANGED;
	}

	$values[] = $planet['planet_id'];

    $db->query('UPDATE [game]_planets SET ' . implode(', ', $types) .
	 ' WHERE planet_id = %u', $values);
}

function damageShip(&$ship, &$attacker, $modifier)
{
	$amount = $modifier * $attacker['fighters'];

	$damage = attackSway($amount / 2);
	if ($ship['fighters'] < $damage) {
		$amount = ($damage - $ship['fighters']) * 2;
		$ship['fighters'] = 0;
	} else {
	    $ship['fighters'] -= $damage;
		return false;
	}

	$damage = attackSway($amount);
	if ($ship['shields'] < $damage) {
		$amount = $damage - $ship['shields'];
		$ship['shields'] = 0;
	} else {
	    $ship['shields'] -= $damage;
		return false;
	}

	$damage = attackSway($amount);
	if ($ship['hull'] < $damage) {
		$amount = $damage - $ship['hull'];
		$ship['hull'] = 0;
	} else {
	    $ship['hull'] -= $damage;
		return false;
	}

	return true; // ship destroyed
}

function findDefender(&$ship, $type)
{
	global $db;

	$d = $db->query('SELECT ship_id FROM [game]_ships WHERE ' .
	 'login_id = %u AND ship_id != %u AND fighters > %u AND ' .
	 'task = \'defend-%s\' AND RAND() > 0.5 AND location = %u ' .
	 'ORDER BY fighters DESC LIMIT 1', array($ship['login_id'],
	 $ship['ship_id'], $ship['fighters'], $db->escape($type),
	 $ship['location']));

	if ($db->numRows($d) > 0) {
	    return getShip(current($db->fetchRow($d)));
	}

	return false;
}

function fleetDefender(&$ship)
{
	return findDefender($ship, 'fleet');
}

function planetDefender(&$ship)
{
	return findDefender($ship, 'planet');
}

function shipVship(&$aShip, &$dShip)
{
	// amount the ship defends with
	$modifier = strstr($dShip['task'], 'defend') !== false &&
	 (shipHas($dShip, 'sc') || !(shipHas($aShip, 'hs') ||
	 shipHas($aShip, 'ls'))) ? 1 : 1.125;

	if (damageShip($dShip, $aShip, $modifier)) {
		return true;
	}
	damageShip($aShip, $dShip, 1);

	if (strstr($dShip['task'], 'defend') === false) {
    	$dShip['task'] = 'defend';
    }

	return false;
}

function shipVplanet(&$aShip, &$dPlanet)
{
	$dPlanet['colon'] -= mt_rand(0, 2 * $aShip['fighters']);
	if ($dPlanet['colon'] < 0) {
		$dPlanet['colon'] = 0;
	}

	$dPlanet['fighters'] -= mt_rand($aShip['fighters'] * 0.75, $aShip['fighters']);
	if ($dPlanet['fighters'] <= 0) {
		$dPlanet['fighters'] = 0;
		return true;
	}

	damageShip($aShip, $dPlanet, mt_rand(6, 10) / 10);

	return false;
}

function atkShipResult(&$attacker, &$from, &$to)
{
	$effect = array();

	if ($from['fighters'] != $to['fighters']) {
	    $effect[] = '<em>' . ($from['fighters'] - $to['fighters']) .
		 ' fighters</em>';
	}

	if ($from['shields'] != $to['shields']) {
	    $effect[] = '<em>' . ($from['shields'] - $to['shields']) .
		 ' shields</em>';
	}

	if ($from['hull'] != $to['hull']) {
	    $effect[] = '<em>' . ($from['hull'] - $to['hull']) .
		 ' hull integrity</em>';
	}

	return "<p>$attacker[ship_name] " . (empty($effect) ?
	 " did not do any damage" : ("destroyed <em>" .
	 implode('</em>, <em>', $effect))) . "</em></p>\n";
}


function atkPlanetResult(&$attacker, &$from, &$to)
{
	$effect = array();

	if ($from['fighters'] != $to['fighters']) {
	    $effect[] = '<em>' . ($from['fighters'] - $to['fighters']) .
		 ' fighters</em>';
	}

	if ($from['colon'] != $to['colon']) {
	    $effect[] = '<em>' . ($from['colon'] - $to['colon']) .
		 ' colonists</em>';
	}

	return "<p><strong>$attacker[ship_name] " . (empty($effect) ?
	 "</strong> did not do any damage" : ("destroyed</strong> <em>" .
	 implode('</em>, <em>', $effect))) . "</em></strong></p>\n";
}

function atkShipOverview(&$rAttack, &$rDefend, &$tShip)
{
	if ($rAttack & SHIP_DEAD) {
		return "<p>Your ship was destroyed.</p>\n";
	} elseif ($rDefend & SHIP_DEAD) {
		if ($rDefend & SHIP_ESCAPED) {
			return "<p>You destroyed the enemy ship; " . 
			 esc($tShip['login_name']) . " managed to flee in an " .
			 "escape-craft.</p>\n";
		} else if ($rDefend & SHIP_TRANSFERRED) {
			return "<p>You destroyed the enemy ship.</p>\n";
		} else {
			return "<p>You destroyed the enemy ship, killing " .
			 esc($tShip['login_name']) . " in the process.</p>\n";
		}
	}

	return "<p>Neither ship was destroyed.</p>\n";
}

?>
