<?php

require_once('inc/admin.inc.php');
require_once('inc/generator.funcs.php');

set_time_limit(30);

$out = '';
if (empty($sure) && isset($build_universe)) {
	$sure_str = "Are you sure you want to build a new universe?<p>This may take some time.";
	get_var('Build Uni', $self, $sure_str, 'sure', 'yes');
} elseif(isset($process)) {
	$UNI = array();
	$UNI['size'] = $gameOpt['uv_universe_size'];
	$UNI['numsystems'] = $gameOpt['uv_num_stars'];
	$UNI['map_border'] = 25; //border on all sides around the image (stops numbers going off the edge) (pixels).
	$UNI['num_size'] = 1; //font size for system numbers (on map).
	$UNI['bg_color'] = array(0,0,0); //background colour of map
	$UNI['link_color'] = array(99, 99, 99); //colour of links between systems
	$UNI['num_color'] = array(0x99, 0xCC, 0xFF);//Most system numbers
	$UNI['num_color2'] = array(0xFF, 0xFF, 0xFF);//Current system number
	$UNI['num_color3'] = array(255,0,0);//Sol color
	$UNI['star_color'] = array(255,255,255);
	$UNI['worm_one_way_color'] = array(230,230,64); //yellow
	$UNI['worm_two_way_color'] = array(0,230,0); //green
	$UNI['label_color'] = array(0, 255, 0);
	$UNI['localmapwidth'] = 200; //width of 'local area' map.
	$UNI['localmapheight'] = 200; //height of 'local area' map.
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
	$UNI['link_dist'] = $gameOpt['uv_max_link_dist']; //maximum distance between linked star systems (pixels).
	$UNI['minlinks'] = 2; //miniumum number of links a system may have.
	$UNI['maxlinks'] = 6; //maximum number of links a system may have.
	$UNI['print_bg_color'] = array(255,255,255); //background colour of printable map.
	$UNI['print_link_color'] = array(200,200,200); //link colour for printable map
	$UNI['print_num_color'] = array(0,0,0);//Most system numbers for printably map
	$UNI['print_star_color'] = array(0,0,0); //star colour for printable map
	$UNI['print_label_color'] = array(0, 0, 0);
	$UNI['num_color'] = $UNI['graphics'] ? array(0x99, 0xCC, 0xFF) :  array(0, 0xFF, 0xFF);//Most system numbers

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
			set_time_limit(60); //another minute to make the images
			if (!isset($preview)) $out .= "<br />Deleting old images...<br />";
			if (!isset($preview)) clearImages('img/' . $db_name . '_maps');
			if (!isset($preview)) $out .= "Rendering global map...<br />";
			renderGlobal($db_name);
			if (!isset($preview)) $out .= "Rendering local maps...<br />";
			renderLocal($db_name);
		}
		if (!isset($preview)) {
			print_page('Universe generator', $out);
		}
	} elseif(isset($gen_new_maps)){ //generating some new maps for some reason
		if (!isset($preview)) $out .= "<br />Deleting old images...<br />";
		clearImages('img/' . $db_name . '_maps');

		if (!isset($preview)) $out .= "Rendering global map...<br />";
		renderGlobal($db_name);

		if (!isset($preview)) $out .= "Rendering local maps...<br />";
		renderLocal($db_name);

		print_footer();
	} else { //previewing universes
		renderGlobal($db_name);
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
