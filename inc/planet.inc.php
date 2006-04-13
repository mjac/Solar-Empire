<?php

function checkPlanet($id)
{
	global $userShip;

	$planet = getPlanet($id);
	return ($userShip['login_id'] == $planet['login_id'] ||
	 $planet['fighters'] <= 0) && $userShip['location'] == $planet['location'] ?
	 $planet : false;
}

function getPlanet($id)
{
	global $db;

	$qPlanet = $db->query('SELECT p.*, u.login_id, u.login_name, ' .
	 'u.turns_run, c.symbol AS clan_sym, c.sym_color AS clan_sym_color, ' .
	 'c.clan_id FROM [game]_planets AS p LEFT JOIN [game]_users AS ' .
	 'u ON p.login_id = u.login_id LEFT JOIN [game]_clans AS c ' .
	 'ON u.clan_id = c.clan_id WHERE planet_id = %u LIMIT 1', 
	 array($id));

	return $db->numRows($qPlanet) > 0 ? $db->fetchRow($qPlanet, ROW_ASSOC) : 
	 false;
}

function idle_colonists()
{
	global $planet;
	return $planet['colon'] - $planet['alloc_fight'] -
	 $planet['alloc_elect'] - $planet['alloc_organ'];
}

// Ensure a user can transfer stuff.
function conditions($user, $planet)
{
	global $gameOpt;
	if ($user['joined_game'] > (time() - ($gameOpt['min_before_transfer'] * 86400)) &&
	     ($user['login_id'] !== $planet['login_id'] && $planet['fighters'] != 0) &&
	     !IS_ADMIN) {
		return true;
	} else {
		return false;
	}
}

?>
