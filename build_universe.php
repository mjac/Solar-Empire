<?php

require_once('inc/user.inc.php');
require_once('inc/generator.funcs.php');

set_time_limit(60);

if($user['login_id'] != ADMIN_ID && $user['login_id'] != OWNER_ID) { //(server) admin only.
	print_page('Error','Admin only');

} elseif(empty($sure) && isset($build_universe)) {
	$sure_str = "Are you sure you want to build a new universe?<p>This may take some time.";
	get_var('Build Uni','build_universe.php',$sure_str,'sure','yes');

} elseif(isset($process)) {
	if(!isset($preview)) { //only output text for html pages.
		print_header("Build Universe","../");
	}

	/*************************************************/
	/********************VARIABLES********************/
	/*************************************************/
	$UNI = array();
	$UNI['size'] = $uv_universe_size;
	$UNI['numsystems'] = $uv_num_stars;
	$UNI['map_border'] = 25; //border on all sides around the image (stops numbers going off the edge) (pixels).
	$UNI['num_size'] = 1; //font size for system numbers (on map).
	$UNI['bg_color'] = array(0,0,0); //background colour of map
	$UNI['link_color'] = array(90,90,90); //colour of links between systems
	$UNI['num_color'] = array(0,255,255);//Most system numbers
	$UNI['num_color2'] = array(255,255,255);//Current system number
	$UNI['num_color3'] = array(255,0,0);//Sol color
	$UNI['star_color'] = array(255,255,255);
	$UNI['worm_one_way_color'] = array(230,230,64); //yellow
	$UNI['worm_two_way_color'] = array(0,230,0); //green
	$UNI['label_color'] = array(0, 255, 0);
	$UNI['localmapwidth'] = 200; //width of 'local area' map.
	$UNI['localmapheight'] = 200; //height of 'local area' map.
	$UNI['mindist'] = $uv_min_star_dist;
	$UNI['minfuel'] = $uv_fuel_min;
	$UNI['maxfuel'] = $uv_fuel_max;
	$UNI['fuelpercent'] = $uv_fuel_percent;
	$UNI['minmetal'] = $uv_metal_min;
	$UNI['maxmetal'] = $uv_metal_max;
	$UNI['metalpercent'] = $uv_metal_percent;
	$UNI['map_layout'] = $uv_map_layout;
	$UNI['uv_planets'] = $uv_planets;
	$UNI['uv_planet_slots'] = $uv_planet_slots;
	$UNI['wormholes'] = $wormholes;
	$UNI['num_ports'] = $uv_num_ports;
	$UNI['num_bms'] = $uv_num_bmrkt;
	$UNI['flag_research'] = $flag_research;
	$UNI['random_events'] = $random_events;
	$UNI['link_dist'] = $uv_max_link_dist; //maximum distance between linked star systems (pixels).
	$UNI['minlinks'] = 2; //miniumum number of links a system may have.
	$UNI['maxlinks'] = 6; //maximum number of links a system may have.
	$UNI['print_bg_color'] = array(255,255,255); //background colour of printable map.
	$UNI['print_link_color'] = array(200,200,200); //link colour for printable map
	$UNI['print_num_color'] = array(0,0,0);//Most system numbers for printably map
	$UNI['print_star_color'] = array(0,0,0); //star colour for printable map
	$UNI['print_label_color'] = array(0, 0, 0);

	if (!isset($gen_new_maps)) {//don't make a new uni for map making
		$systems = array(array(
			'num' => 0,
			'x_loc' => $UNI['size'] / 2,
			'y_loc' => $UNI['size'] / 2,
			'links' => '',
			'name' => 'Sol',
			'fuel' => 0,
			'metal' => 0,
			'wormhole' => 0,
			'planetary_slots' => 0,
			'event_random' => 0
		));
		$ports = array( array('location' => 0) );
		$bmarks = array();
	}

	if(isset($extinfo)) { //print detailed information about the generation of the universe.
		$extinfo = true;
	} else {
		$extinfo = false;
	}

	if(isset($build_universe) || isset($preview) || isset($gen_new_maps)) {

		 //only output text for html page. not map preview png
		if(isset($build_universe)){
			if (!isset($preview)) print("Generating Systems...<br>");

		}
		if(!isset($gen_new_maps)){ //don't make a new uni for map making
			make_systems_1($systems);
		}

		 //only output text for html page. not map preview png
		if(isset($build_universe)) {
			if (!isset($preview)) print("Linking Systems...<br>");

		}
		if(!isset($gen_new_maps)){//don't make a new uni for map making
			link_systems_1($systems);
		}

		if(isset($build_universe)){//generating a new universe
			if (!isset($preview)) print("Adding Minerals...<br>");
			add_minerals($systems);
			if (!isset($preview)) print("Adding Starports...<br>");
			add_starports_se1($ports);
			if($UNI['flag_research'] == 1){
				print("Adding Blackmarkets...<br>");
				add_blackmarket_se1($bmarks);
				$bm_allowed = "${db_name}_bmrkt";
			} else {
				$bm_allowed = "";
			}
			if (!isset($preview)) print("Saving Universe...<br>");
			save_universe_se1($systems, $ports, $bmarks, "${db_name}_stars", "${db_name}_ports", $bm_allowed);
			random_event_placer();

			if (!isset($preview)) print("Creating pre-genned planets...<br>");
			planet_functionality();
		}

		if(!(extension_loaded("gd") || extension_loaded("gd2"))){
			print("<p><b class=b1>Warning</b>!<br>You do not have the <b class=b1>gd</b> module installed with this PHP installation, therefore the maps cannot be generated.<p>To fix this, find and install the GD library, or get the server operator to do it if it's a paid for server.");
		} else {
			set_time_limit(60); //another minute to make the images
			if (!isset($preview)) print("<br>Deleting old images...<br>");
			clearImages("img/${db_name}_maps");
			if (!isset($preview)) print("Rendering global map...<br>");
			render_global_se1($db_name);
			if (!isset($preview)) print("Rendering local maps...<br>");
			renderLocal($db_name);
			dbn("update se_games set paused = 1 where db_name = '$db_name'");
			if (!isset($preview)) print("Game Paused");
			if (!isset($preview)) print("<div id='done'>Finished.<script>document.all.done.scrollIntoView();</script></div>");
		}
		if (!isset($preview)) print_footer();

	} elseif(isset($gen_new_maps)){ //generating some new maps for some reason
		if (!isset($preview)) print("<br>Deleting old images...<br>");
		clearImages("img/${db_name}_maps");

		if (!isset($preview)) print("Rendering global map...<br>");
		render_global_se1($db_name);

		if(isset($all)){ //render locals as well as globals.
			if (!isset($preview)) print("Rendering local maps...<br>");
			renderLocal($db_name);
		}
		print_footer();
	} else { //previewing universes
		render_global_se1($db_name);
	}

} else {
	$out_str = "Choose something to do with the universe generator.<br>Only the bottom choice will re-generate the universe!";
	$out_str .= "<p><a href=$_SERVER[PHP_SELF]?preview=1&process=1>Preview</a> a universe that uses your present variable settings. This won't do anything to the present game!!!";
	$out_str .= "<p><br>Generate a <a href=$_SERVER[PHP_SELF]?build_universe=1&process=1>new universe</a>!<br>";

	print_page('Universe Generation',$out_str);
}

?>
