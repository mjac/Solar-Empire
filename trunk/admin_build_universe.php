<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');
if (!class_exists('generator')) {
	require('inc/generator.class.php');
}

set_time_limit(60);

$uni = new generator;

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

		$uni->renderMap();

		if ($action === 'preview') {
		    $uni->displayMap();
		    $uni->destroyMap();
		    exit;
		}

		$uni->saveMap($mapDir . '/screen.png');
		$uni->savePrintMap($mapDir . '/print.png');
		$uni->renderLocal($mapDir . '/screen.png', $mapDir . '/local');

		$uni->destroyMap();
		break;

	default:
		assignCommon($tpl);
		$tpl->display('game/admin/generate.tpl.php');
		return;
}

?>
