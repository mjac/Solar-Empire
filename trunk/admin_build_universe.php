<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');
require_once('inc/generator.class.php');

set_time_limit(60);

/** Replace the most central star with Sol */ 

function centreSol(&$stars, $width, $height)
{
	$xCentre = floor($width / 2);
	$yCentre = floor($height / 2);

	$solIndex = 0;

	$lowIndex = 0;
	$lowQuad = -1;

	$amount = count($stars);
	foreach ($stars as $index => $star) {
		$currentQuad = ($star->x - $xCentre) * ($star->x - $xCentre) + 
		 ($star->y - $yCentre) * ($star->y - $yCentre);
		if ($lowQuad === -1 || $lowQuad > $currentQuad) {
			$lowQuad = $currentQuad;
			$lowIndex = $index;
		}

		if ($star->id === 1) {
			$solIndex = $index;
		}
	}

	$pivot = $stars[$solIndex]->id;
	$stars[$solIndex]->id = $stars[$lowIndex]->id;
	$stars[$lowIndex]->id = $pivot;
	$stars[$solIndex]->name = $stars[$lowIndex]->name;
	$stars[$lowIndex]->name = 'Sol';
}

$uni = new genUniverse;

$uni->options['width'] = $uni->options['height'] = $gameOpt['uv_universe_size'];
$uni->options['starAmount'] = $gameOpt['uv_num_stars'];

if ($gameOpt['uv_map_graphics']) {
	$uni->appearance['graphics']['earth'] = PATH_BASE . '/img/map/earth.png';
	$uni->appearance['graphics']['star'] = PATH_BASE . '/img/map/star.png';
}

$uni->options['localWidth'] = $uni->options['localHeight'] = 200;

$uni->options['starMinDist'] = $gameOpt['uv_min_star_dist'];
$uni->options['linkMaxDist'] = $gameOpt['uv_max_link_dist'];

if (!$gameOpt['wormholes']) {
	$uni->options['wormholeChance'] = 0;
}

$mapTypes = array_keys($uni->mapTypes);
switch ($gameOpt['uv_map_layout']) {
	case 0:
	case 1:
	case 2:
	case 3:
		$uni->options['mapType'] = $mapTypes[$gameOpt['uv_map_layout']];
		break;
	default:
		$uni->options['mapType'] = 0;
}

/*$UNI['minfuel'] = $gameOpt['uv_fuel_min'];
$UNI['maxfuel'] = $gameOpt['uv_fuel_max'];
$UNI['fuelpercent'] = $gameOpt['uv_fuel_percent'];

$UNI['minmetal'] = $gameOpt['uv_metal_min'];
$UNI['maxmetal'] = $gameOpt['uv_metal_max'];
$UNI['metalpercent'] = $gameOpt['uv_metal_percent'];

$UNI['uv_planets'] = $gameOpt['uv_planets'];
$UNI['uv_planet_slots'] = $gameOpt['uv_planet_slots'];

$UNI['num_ports'] = $gameOpt['uv_num_ports'];
$UNI['num_bms'] = $gameOpt['uv_num_bmrkt'];*/

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
		centreSol($uni->stars, $uni->options['width'], $uni->options['height']);
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
