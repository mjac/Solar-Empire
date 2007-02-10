<?php

require('inc/admin.inc.php');
require('inc/template.inc.php');

$withinSec = 300;
$currentTime = time();
$fromTime = $currentTime - $withinSec;

$tpl->assign('currentTime', $currentTime);
$tpl->assign('fromTime', $fromTime);

$playerInfo = $db->query('SELECT last_request, login_id, login_name, u.clan_id, c.symbol, c.sym_color FROM [game]_users AS u LEFT JOIN [game]_clans AS c ON u.clan_id = c.clan_id WHERE last_request > %[1] ORDER BY last_request DESC', $fromTime);

$players = array();
while ($player = $db->fetchRow($players, ROW_NUMERIC)) {
	$players[] = array(
	    'lastRequest' => (int)$player[0],
	    'id' => (int)$player[1],
	    'name' => $player[2],
	    'clan' => array(
			'id' => (int)$player[3],
			'symbol' => $player[4],
			'colour' => $player[5]
		)
	);
}

$tpl->assign('playersOnline', $players);

assignCommon($tpl);
$tpl->display('game/admin/activeusers.tpl.php');

?>
