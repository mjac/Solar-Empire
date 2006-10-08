<?php

require_once('inc/admin.inc.php');
require_once('inc/template.inc.php');
require_once('inc/generator.inc.php');

set_time_limit(30);

$out = '';


$UNI = array();
$UNI['size'] = $gameOpt['uv_universe_size'];
$UNI['numsystems'] = $gameOpt['uv_num_stars'];
$UNI['map_border'] = 25;
$UNI['num_size'] = 1; // font number size

$UNI['bg_color'] = array(0x00, 0x00, 0x00);
$UNI['link_color'] = array(0x66, 0x66, 0x66);
//$UNI['num_color'] = array(0x99, 0xCC, 0xFF); // Most system numbers
$UNI['num_color2'] = array(0xFF, 0xFF, 0xFF); // Current system number
$UNI['num_color3'] = array(0xFF, 0x00, 0x00); // Sol color
$UNI['star_color'] = array(0xFF, 0xFF, 0xFF);
$UNI['label_color'] = array(0x00, 0xFF, 0x00);
$UNI['worm_one_way_color'] = array(0xE6, 0xE6, 0x40);
$UNI['worm_two_way_color'] = array(0x00, 0xE6, 0x00);

$UNI['print_bg_color'] = array(0xFF, 0xFF, 0xFF);
$UNI['print_link_color'] = array(0xCC, 0xCC, 0xCC);
$UNI['print_num_color'] = array(0x00, 0x00, 0x00);
$UNI['print_star_color'] = array(0x00, 0x00, 0x00);
$UNI['print_label_color'] = array(0x00, 0x00, 0x00);

$UNI['num_color'] = $UNI['graphics'] ? array(0x99, 0xCC, 0xFF) : 
 array(0x00, 0xFF, 0xFF);

$UNI['localmapwidth'] = 200; // Width of local map.
$UNI['localmapheight'] = 200; // Height of local map.

$UNI['mindist'] = $gameOpt['uv_min_star_dist'];
$UNI['minfuel'] = $gameOpt['uv_fuel_min'];
$UNI['maxfuel'] = $gameOpt['uv_fuel_max'];
$UNI['fuelpercent'] = $gameOpt['uv_fuel_percent'];
$UNI['minmetal'] = $gameOpt['uv_metal_min'];
$UNI['maxmetal'] = $gameOpt['uv_metal_max'];
$UNI['metalpercent'] = $gameOpt['uv_metal_percent'];
$UNI['map_layout'] = $gameOpt['uv_map_layout'];
$UNI['uv_planets'] = $gameOpt['uv_planets'];
$UNI['uv_planet_slots'] = $gameOpt['uv_planet_slots'];
$UNI['wormholes'] = $gameOpt['wormholes'];
$UNI['num_ports'] = $gameOpt['uv_num_ports'];
$UNI['num_bms'] = $gameOpt['uv_num_bmrkt'];
$UNI['graphics'] = (bool)$gameOpt['uv_map_graphics'];
$UNI['link_dist'] = $gameOpt['uv_max_link_dist']; // Maximum distance between linked systems

$UNI['minlinks'] = 2; // Miniumum number of links a system may have.
$UNI['maxlinks'] = 6; // Maximum number of links a system may have.

$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : '';

switch ($action) {
	case 'create':
	case 'preview':
		break;
	default:
		$tpl->display('game/admin/generate.tpl.php');
		return;
}

$tpl->assign('action', $action);

exit;

if (empty($sure) && isset($build_universe)) {
	$sure_str = "Are you sure you want to build a new universe?<p>This may take some time.";
	get_var('Build Uni', $self, $sure_str, 'sure', 'yes');
} elseif(isset($process)) {
	if (!isset($gen_new_maps)) {//don't make a new uni for map making
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

} else {
	pageStart('Build a new universe');
	echo <<<END
<h1>Build a new universe</h1>
<p><a href="$self?preview=1&process=1">Preview</a> a universe that 
uses your present variable settings. This <strong>will not affect</strong> the 
present game.</p>
<p>Generate a <a href="$self?build_universe=1&process=1">new universe</a> 
for this game.</p>

END;
	pageStop();
}

?>
