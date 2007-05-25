<?php

require('inc/admin.inc.php');
require('inc/template.inc.php');

if (!class_exists('swGenerator')) {
	require('inc/generator.class.php');
}
if (!class_exists('benchmarkAuto')) {
	require('lib/benchmark/benchmark.class.php');
}

set_time_limit(60);

$uni = new swGenerator;

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

	    $positions = new autoBenchmark();
		$uni->positions();
		$uni->centreSol();
		$tpl->assign('genPeriodPos', $positions->finish());

		$links = new autoBenchmark();
		$uni->link();
		$uni->wormholes();
		$tpl->assign('genPeriodLink', $links->finish());

		$render = new autoBenchmark();
		$uni->renderMap();
		$tpl->assign('genPeriodRender', $render->finish());

		if ($action === 'makepreview') {
		    $uni->displayMap();
		    $uni->destroyMap();
		    exit;
		}

		$save = new autoBenchmark();
		$uni->saveData();

		$uni->saveMap($mapDir . '/screen.png');
		$uni->savePrintMap($mapDir . '/print.png');
		$uni->saveLocalMaps($mapDir . '/screen.png', $mapDir . '/local');
		$tpl->assign('genPeriodSave', $save->finish());

		$uni->destroyMap();

		$tpl->assign('genPeriod', $total->finish());
	case 'preview':
	    break;

	default:
	    $action = '';
}

$tpl->assign('action', $action);

assignCommon($tpl);
$tpl->display('game/admin/generator.tpl.php');

?>
