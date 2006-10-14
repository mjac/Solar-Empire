<?php

require_once('inc/common.inc.php');
require_once('inc/db.inc.php');
require_once('inc/template.inc.php');

$gameInfo = selectGame(isset($_REQUEST['db_name']) ? $_REQUEST['db_name'] : '');

$tpl->assign('gameExists', $gameInfo ? true : false);
$tpl->assign('viewVars', $gameInfo ? $gameOpt['admin_var_show'] == 1 : false);

if (!($gameInfo && $gameOpt['admin_var_show'] == 1)) {
	$tpl->display('game_vars.tpl.php');
	exit;
}

$tpl->assign('gameName', $gameInfo['name']);

$gameVars = array();

$vars = $db->query('SELECT name, value, descript FROM [game]_db_vars ORDER BY name');
while ($var = $db->fetchRow($vars, ROW_ASSOC)) {
	$gameVars[] = $var;
}

$tpl->assign('gameVars', $gameVars);

$tpl->display('game_vars.tpl.php');
exit;

?>
