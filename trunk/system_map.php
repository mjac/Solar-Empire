<?php

require('inc/user.inc.php');
require('inc/template.inc.php');

$map = '';
if (isset($view) && $view === 'print') {
	$map = URL_BASE . '/img/maps/' . $gameInfo['db_name'] . '/print.png';
}

if (isset($find) && $gameOpt['allow_search_map'] != 0) {
	$find = (int)$find;

	$star = $db->query('SELECT COUNT(*) FROM [game]_stars WHERE star_id = %[1]',
	 $find);

	if (current($db->fetchRow($star)) > 0) {
		$map = URL_BASE . '/system_find.php?from=' . $userShip['location'] .
		 '&to=' . $find;
	}
}

if (empty($map)) {
	$map = URL_BASE . '/img/maps/' . $gameInfo['db_name'] . '/screen.png';
}

$tpl->assign('map', $map);
$tpl->assign('canSearch', $gameOpt['allow_search_map'] == 1);

assignCommon($tpl);
$tpl->display('game/system_map.tpl.php');

?>
