<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');
require_once('inc/generator.class.php');

set_time_limit(60);

function clearImages($path)
{
	if (!file_exists($path)) {
		return;
	}

	$dir = opendir($path);
	while ($file = readdir($dir)) {
		if (substr($file, -4) === '.png') {
			unlink($path . '/' . $file);
		}
	}

	closedir($dir);
}

$uni = new genUniverse;

$uni->options['width'] = $uni->options['height'] = $gameOpt['uv_universe_size'];
$uni->options['starAmount'] = $gameOpt['uv_num_stars'];

if ($gameOpt['uv_map_graphics']) {
	$uni->appearance['earth'] = PATH_BASE . '/img/map/earth.png';
	$uni->appearance['star'] = PATH_BASE . '/img/map/star.png';
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

$UNI['minfuel'] = $gameOpt['uv_fuel_min'];
$UNI['maxfuel'] = $gameOpt['uv_fuel_max'];
$UNI['fuelpercent'] = $gameOpt['uv_fuel_percent'];

$UNI['minmetal'] = $gameOpt['uv_metal_min'];
$UNI['maxmetal'] = $gameOpt['uv_metal_max'];
$UNI['metalpercent'] = $gameOpt['uv_metal_percent'];

$UNI['uv_planets'] = $gameOpt['uv_planets'];
$UNI['uv_planet_slots'] = $gameOpt['uv_planet_slots'];

$UNI['num_ports'] = $gameOpt['uv_num_ports'];
$UNI['num_bms'] = $gameOpt['uv_num_bmrkt'];

$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : '';
$tpl->assign('action', $action);

switch ($action) {
	case 'maps':
	case 'create':
	case 'imagepreview':
		break;
	case 'preview':
	default:
		assignCommon($tpl);
		$tpl->display('game/admin/generate.tpl.php');
		return;
}

exit;





if (!isset($gen_new_maps)) { // Don't make a new uni for map making
	$systems = array(array(
		'num' => 0,
		'x' => $UNI['size'] / 2,
		'y' => $UNI['size'] / 2,
		'links' => '',
		'name' => 'Sol',
		'fuel' => 0,
		'metal' => 0,
		'wormhole' => 0,
		'planetary_slots' => 0
	));
	$ports = array(0);
	$bmarks = array();
}

if (isset($build_universe) || isset($preview) || isset($gen_new_maps)) {
	//only output text for html page. not map preview png
	if(isset($build_universe)){
		if (!isset($preview)) $out .= "Generating Systems...<br />";
	}
	if(!isset($gen_new_maps)){ //don't make a new uni for map making
		make_systems($systems);
	}

	 //only output text for html page. not map preview png
	if(isset($build_universe)) {
		if (!isset($preview)) $out .= "Linking Systems...<br />";

	}
	if(!isset($gen_new_maps)){//don't make a new uni for map making
		link_systems($systems);
	}

	if(isset($build_universe)){//generating a new universe
		if (!isset($preview)) $out .= "Adding Minerals...<br />";
		add_minerals($systems);

		if (!isset($preview)) $out .= "Adding Starports...<br />";
		add_starports($ports);

		if (!isset($preview)) $out .= "Saving Universe...<br />";
		save_universe($systems, $ports);

		if (!isset($preview)) $out .= "Creating pre-genned planets...<br />";
		planet_functionality();
	}

	if (!function_exists('imagecreate')) {
		echo "<h2>Image creation failed</h2>\n<p>Required GD image functions are missing.</p>\n";
	} else {
		set_time_limit(60); // Another minute to make the images
		if (!isset($preview)) {
			$out .= "<br />Deleting old images...<br />";
			clearImages('img/' . $gameInfo['db_name'] . '_maps');
			$out .= "Rendering global map...<br />";
		}
		renderGlobal($gameInfo['db_name']);
		if (!isset($preview)) $out .= "Rendering local maps...<br />";
		renderLocal($gameInfo['db_name']);
	}
	if (!isset($preview)) {
		print_page('Universe generator', $out);
	}
} elseif(isset($gen_new_maps)){ // Generating some new maps for some reason
	if (!isset($preview)) $out .= "<br />Deleting old images...<br />";
	clearImages('img/' . $gameInfo['db_name'] . '_maps');

	if (!isset($preview)) $out .= "Rendering global map...<br />";
	renderGlobal($gameInfo['db_name']);

	if (!isset($preview)) $out .= "Rendering local maps...<br />";
	renderLocal($gameInfo['db_name']);

	print_footer();
} else { // Previewing universes
	renderGlobal($gameInfo['db_name']);
}

?>
