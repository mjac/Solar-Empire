<?php

require_once(PATH_INC . '/admin.inc.php');
require_once(PATH_INC . '/template.inc.php');
if (!class_exists('generator')) {
	require(PATH_INC . '/generator.class.php');
}

set_time_limit(60);

$uni = new genUniverse;

$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : '';
$tpl->assign('action', $action);

$mapDir = PATH_BASE . '/img/maps/' . $gameInfo['db_name'];

switch ($action) {
	case 'maps':
		// load id, position, link, wormhole from database and create
		break;

	case 'preview':
	case 'create':
		$uni->createStars();
		$uni->positions();
		$uni->centreSol();
		$uni->link();
		$uni->wormholes();
		$uni->renderGlobal($mapDir . '/screen.png', $mapDir . '/print.png');
		$uni->renderLocal($mapDir . '/screen.png', $mapDir . '/local');
		break;

	default:
		assignCommon($tpl);
		$tpl->display('game/admin/generate.tpl.php');
		return;
}

?>
