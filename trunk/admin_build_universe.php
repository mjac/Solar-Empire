<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');
if (!class_exists('generator')) {
	require('inc/generator.class.php');
}
if (!class_exists('autoBenchmark')) {
	require('lib/benchmark/benchmark.class.php');
}

set_time_limit(60);

$uni = new generator;

$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : '';
$tpl->assign('action', $action);

$mapDir = PATH_BASE . '/img/maps/' . $gameInfo['db_name'];

switch ($action) {
	case 'maps':
	    $total = new autoBenchmark();

		$uni->loadData();
		$uni->renderMap();

		$uni->saveMap($mapDir . '/screen.png');
		$uni->savePrintMap($mapDir . '/print.png');
		$uni->saveLocalMaps($mapDir . '/screen.png', $mapDir . '/local');

		$uni->destroyMap();

		$tpl->assign('mapGenPeriod', $total->finish());
		break;

	case 'makepreview':
	case 'create':
	    $total = new autoBenchmark();

		$uni->createStars();

		if ($action !== 'makepreview') {
			$uni->setNames();
		}

		$uni->positions();
		$uni->centreSol();

		$uni->link();
		$uni->wormholes();

		$uni->renderMap();

		if ($action === 'makepreview') {
		    $uni->displayMap();
		    $uni->destroyMap();
		    exit;
		}

		$uni->saveData();

		$uni->saveMap($mapDir . '/screen.png');
		$uni->savePrintMap($mapDir . '/print.png');
		$uni->saveLocalMaps($mapDir . '/screen.png', $mapDir . '/local');

		$uni->destroyMap();

		$tpl->assign('totalGenPeriod', $total->finish());
	case 'preview':
	    break;

	default:
	    $action = '';
}

$tpl->assign('action', $action);

assignCommon($tpl);
$tpl->display('game/admin/generator.tpl.php');

?>
