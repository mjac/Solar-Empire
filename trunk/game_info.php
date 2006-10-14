<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');
require_once('inc/template.inc.php');

$gameInfo = selectGame(isset($_REQUEST['db_name']) ? $_REQUEST['db_name'] : '');

$tpl->assign('gameExists', $gameInfo ? true : false);

if (!$gameInfo) {
	$tpl->display('game_info.tpl.php');
	exit;
}

$tpl->assign('name', $gameInfo['name']);
$tpl->assign('description', $gameInfo['description']);
$tpl->assign('viewVars', $gameOpt['admin_var_show'] == 1);
$tpl->assign('canRegister', $gameOpt['new_logins'] == 1 && 
 $gameOpt['sudden_death'] == 0);
$tpl->assign('status', $gameInfo['status']);
$tpl->assign('maxPlayers', $gameOpt['max_players']);
$tpl->assign('difficulty', $gameInfo['difficulty']);
$tpl->assign('started', $gameInfo['started']);
$tpl->assign('finishes', $gameInfo['finishes']);
$tpl->assign('gameSelected', $gameInfo['db_name']);

$gAdmin = $db->query('SELECT login_name FROM user_accounts WHERE login_id = %u',
 array($gameInfo['admin']));
$tpl->assign('admin', current($db->fetchRow($gAdmin)));


$gInfo = $db->query('SELECT COUNT(*), SUM(cash), SUM(turns_run), ' .
 'SUM(ships_killed), SUM(fighters_lost), SUM(fighters_killed) FROM ' .
 '[game]_users WHERE login_id != %u', array($gameInfo['admin']));
$playerStats = $db->fetchRow($gInfo, ROW_NUMERIC);

$tpl->assign('playerAmount', (int)$playerStats[0]);
$tpl->assign('playerCredits', (int)$playerStats[1]);
$tpl->assign('playerTurns', (int)$playerStats[2]);
$tpl->assign('shipsKilled', (int)$playerStats[3]);
$tpl->assign('fightersLost', (int)$playerStats[4]);
$tpl->assign('fightersKilled', (int)$playerStats[5]);

$aQuery = $db->query('SELECT COUNT(*) FROM [game]_users WHERE ' .
 'ship_id IS NOT NULL AND login_id != %u', array($gameInfo['admin']));
$tpl->assign('alivePlayers', (int)current($db->fetchRow($aQuery)));


$shipInfo = $db->query('SELECT COUNT(*), SUM(fighters) FROM [game]_ships ' .
 'WHERE login_id != %u', array($gameInfo['admin']));
$shipStats = $db->fetchRow($shipInfo, ROW_NUMERIC);

$tpl->assign('shipAmount', (int)$shipStats[0]);
$tpl->assign('shipFighters', (int)$shipStats[1]);


$planetInfo = $db->query('SELECT COUNT(*), SUM(fighters), SUM(cash) ' .
 'FROM [game]_planets WHERE login_id != %u',
 array($gameInfo['admin']));
$planetStats = $db->fetchRow($planetInfo, ROW_NUMERIC);

$tpl->assign('planetAmount', (int)$planetStats[0]);
$tpl->assign('planetFighters', (int)$planetStats[1]);
$tpl->assign('planetCredits', (int)$planetStats[2]);


$topPlayers = $db->query('SELECT login_id, login_name, u.clan_id, ' .
 'c.symbol AS clan_sym, c.sym_color AS clan_sym_color, ' .
 'u.score FROM [game]_users AS u LEFT JOIN [game]_clans AS c ON ' .
 'u.clan_id = c.clan_id WHERE login_id != %u ORDER BY score DESC, ' .
 'login_name LIMIT 10', array($gameInfo['admin']));
$topArr = array();

if ($db->numRows($topPlayers) > 0) {
	while ($eachPlayer = $db->fetchRow($topPlayers, ROW_ASSOC)) {
		$topArr[] = $eachPlayer;
	}
}

$tpl->assign('topPlayers', $topArr);

$tpl->display('game_info.tpl.php');
exit;

?>
