<?php

// Removes all the generated map files
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

//add starports to the universe
function add_starports(&$ports)
{
	global $UNI;

	$amount = $UNI['num_ports'] - count($ports);

	// Stop infinite recursion if ports > stars
	if ($amount < $UNI['numsystems']) {
		$amount = $UNI['numsystems'];
	}

	while (--$amount >= 0) {
		// Ensure no more than one port per system
		while (in_array($new_port = mt_rand(1, $UNI['numsystems']), $ports));
		$ports[] = $new_port;
	}
}


//function that will pre-generate planets.
function planet_functionality()
{
	global $UNI, $db, $systems;
	#pre-generated planets
	$db->query("delete from [game]_planets");

	//sum total metal & fuel in the universe.
	$total = $db->query('SELECT SUM(metal), SUM(fuel) FROM [game]_stars');
	$mineral_sum = $db->fetchRow($total, ROW_NUMERIC);
	$metal_sum = round($mineral_sum[0] / ($UNI['numsystems'] - 1));
	$fuel_sum = round($mineral_sum[1] / ($UNI['numsystems'] - 1));

	for ($ct = 1; $ct <= $UNI['uv_planets']; ++$ct) {
		$db->query('INSERT INTO [game]_planets (planet_id, planet_name, ' .
		 'location, login_id, fighters, cash, metal, fuel, pass, planet_img) ' .
		 'VALUES (%u, \'%s\', %u, NULL, 0, 1, %u, %u, \'\', %u)',
		 array($ct + 1, $db->escape($systems[$planet_loc - 1]['name']),
		 mt_rand(2, $UNI['numsystems']), round((mt_rand(1, 50) / 100) *
		 $metal_sum), round((mt_rand(1, 50) / 100) * $fuel_sum),
		 mt_rand(1, 15)));
	}
}

//save the universe
function save_universe(&$systems, &$ports)
{
	global $UNI, $db;

	$db->query('TRUNCATE TABLE [game]_stars');
	$db->query('TRUNCATE TABLE [game]_ports');

	foreach ($systems as $system) {
		$link_arr = array();
		foreach ($system['links'] as $value) {
			$link_arr[] = $value + 1;
		}

		$link_arr = array_pad($link_arr, $UNI['maxlinks'], 0);

		$db->query('INSERT INTO [game]_stars (star_id, star_name, ' .
		 'x, y, link_1, link_2, link_3, link_4, link_5, ' .
		 'link_6, metal, fuel, planetary_slots, wormhole) VALUES ' .
		 '(%u, \'%s\', %u, %u, %u, %u, %u, %u, %u, %u, %u, %u, %u, %u)',
		 array($system['num'] + 1, $db->escape($system['name']), $system['x'],
		 $system['y'], $link_arr[0], $link_arr[1], $link_arr[2], $link_arr[3],
		 $link_arr[4], $link_arr[5], $system['metal'], $system['fuel'],
		 $system['planetary_slots'], $system['wormhole']));
	}

	$db->query('UPDATE se_games SET num_stars = %u WHERE ' .
	 'db_name = \'[game]\'', $UNI['numsystems']);

	$portId = 0;
	foreach ($ports as $location) {
		$db->query('INSERT INTO [game]_ports (port_id, location) VALUES ' .
		 '(%u, %u)', array(++$portId, $location + 1));
	}
}

//add minerals to the systems
function add_minerals(&$systems)
{
	global $UNI;

	foreach ($systems as $id => $system) {
		if($system['num'] == 0) {
			continue;
		}

		if (mt_rand(0, 100) < $UNI['fuelpercent']) {
			$systems[$id]['fuel'] = mt_rand($UNI['minfuel'], $UNI['maxfuel']);
		}
		if (mt_rand(0, 100) < $UNI['metalpercent']) {
			$systems[$id]['metal'] = mt_rand($UNI['minmetal'], $UNI['maxmetal']);
		}
	}
}

//create the star systems
function make_systems(&$systems)
{
	global $UNI, $tables, $db;
	$sNames = $db->query('SELECT name FROM se_star_names ORDER BY RAND()');

	$centre = round($UNI['size'] / 2); //centre of map
	$do_this = 0;

	if($UNI['map_layout'] == 1) { //grid layout
		$rows = round(sqrt($UNI['numsystems']));
		$row_dist = round($UNI['size'] / $rows);
		$per_col = round($UNI['numsystems'] / $rows);
		$col_dist = round($UNI['size'] / $per_col);
		$row_count = 0;
		$col_count = 0;
	} elseif($UNI['map_layout'] == 2){ //galactic core
		$one_quart = round($centre / 4);
	} elseif($UNI['map_layout'] == 3){ //clusters
		$num_clus = round(sqrt($UNI['numsystems'])) - 1; //number of clusters
		$stars_per_cluster = round($UNI['numsystems'] / $num_clus); //stars per cluster
		$cluster_size = round(($UNI['size'] / ($num_clus * 0.55)) / 2); //size of cluster in pixels
		$offset_cluster = $UNI['size'] - $cluster_size;
		$sec_count = 0;
		$basis_x = $centre;
		$basis_y = $centre;
	} elseif($UNI['map_layout'] == 5){ //ring layout
		$radius = round($UNI['size'] / 2) - 5;
		$degrees_between_stars = 360 / ($UNI['numsystems'] - 1); //number of degrees between each star
		$present_degrees = 0;
	} elseif($UNI['map_layout'] == 6){ //layered rings layout
		if($UNI['numsystems'] < 50){ //single ring
			$radius = round($UNI['size'] / 2) - 5;
			$degrees_between_stars = 360 / ($UNI['numsystems'] - 1);
			$present_degrees = 0;
			$do_this = 1;
		} elseif($UNI['numsystems'] < 200){ //2 rings
			$ring1_star_count = round(($UNI['numsystems'] / 100) * 30); //30% of the stars go into the first ring.
			$ring2_star_count = $UNI['numsystems'] - $ring1_star_count-1; //the rest of the stars (minus Sol).
			$ring3_star_count = 0; //(no third ring)
			$ring1_radius = $centre / 2;
			$ring2_radius = $centre;
			$ring1_degrees_between = 360 / $ring1_star_count;
			$ring2_degrees_between = 360 / $ring2_star_count;
			$ring1_preset = 0;
			$ring2_preset = 0;
		} else { // 3 rings
			$ring1_star_count = round(($UNI['numsystems'] / 100) * 25); //10% of the stars go into the first ring.
			$ring2_star_count = round((($UNI['numsystems'] - $ring1_star_count) / 100) * 34);
			$ring3_star_count = $UNI['numsystems'] - $ring1_star_count-$ring2_star_count-1;
			$ring1_radius = $centre / 1.5;
			$ring2_radius = $centre / 1.2;
			$ring3_radius = $centre;
			$ring1_degrees_between = 360 / $ring1_star_count;
			$ring2_degrees_between = 360 / $ring2_star_count;
			$ring3_degrees_between = 360 / $ring3_star_count;
			$ring1_preset = 0;
			$ring2_preset = 0;
			$ring3_preset = 0;
		}
	}

	while (($count = count($systems)) < $UNI['numsystems']) {
		list($newname) = $db->fetchRow($sNames, ROW_NUMERIC);

		//planetary slot counter
		$planet_slots = mt_rand(0, $UNI['uv_planet_slots']);

		$newsystem = array('num' => $count, 'x' => false, 'y' => false,
		 'links' => array(), 'name' => $newname, 'fuel' => 0, 'metal' => 0,
		 'wormhole' => 0, 'planetary_slots' => $planet_slots);


		if ($UNI['map_layout'] == 1) { //grid layout
			if($row_count > $rows){//create a new column
				$row_count = 0;
				++$col_count;
			}

			$newsystem['x'] = $col_dist * $col_count;
			$newsystem['y'] = $row_dist * $row_count;
			++$row_count;

			do {
				$newsystem['x'] = mt_rand(0, $UNI['size']);
				$newsystem['y'] = mt_rand(0, $UNI['size']);
			} while (system_too_close($newsystem, $systems, $UNI['mindist']));
		} elseif($UNI['map_layout'] == 2) { //galactic core
			do {
				$newsystem['x'] = mt_rand(0,$UNI['size']);
				$newsystem['y'] = mt_rand(0,$UNI['size']);
			} while ((get_sys_dist($systems[0], $newsystem) > $UNI['size'] / mt_rand(1, 4)) ||
			          system_too_close($newsystem, $systems, $UNI['mindist']));
		} elseif ($UNI['map_layout'] == 3) { //clusters
			if ($sec_count > $stars_per_cluster) { //create new cluster
				$basis_x = mt_rand($cluster_size, $offset_cluster);
				$basis_y = mt_rand($cluster_size, $offset_cluster);
				$sec_count = 0;
			}
			$newsystem['x'] = mt_rand(0, $cluster_size); //x_loc - within cluster
			if (mt_rand(0, 1)) { //decide offset from center of cluster.
				$newsystem['x'] += $basis_x;
			} else {
				$newsystem['x'] = $basis_x - $newsystem['x'];
			}
			$newsystem['y'] = mt_rand(0, $cluster_size); //y_loc - within cluster
			if (mt_rand(0, 1)) { //decide offset from center of cluster.
				$newsystem['y'] += $basis_y;
			} else {
				$newsystem['y'] = $basis_y - $newsystem['y'];
			}
			do {
				$newsystem['x'] = mt_rand(0, $UNI['size']);
				$newsystem['y'] = mt_rand(0, $UNI['size']);
			} while (system_too_close($newsystem,$systems,$UNI['mindist']));
			$sec_count++;

		} elseif ($UNI['map_layout'] == 4) { //circle layout
			do {
				$newsystem['x'] = mt_rand(0,$UNI['size']);
				$newsystem['y'] = mt_rand(0,$UNI['size']);
			} while((get_sys_dist($systems[0],$newsystem) > $UNI['size']/2) ||
			         system_too_close($newsystem,$systems,$UNI['mindist']));
		} elseif ($UNI['map_layout'] == 5 || $do_this == 1) { //ring layout
			$newsystem['x'] = $radius * cos(deg2rad($present_degrees));
			$newsystem['y'] = $radius * sin(deg2rad($present_degrees));
			$newsystem['x'] += $centre;
			$newsystem['y'] += $centre;
			$present_degrees += $degrees_between_stars;//prepare for next star

		} elseif ($UNI['map_layout'] == 6) { //layered rings layout
			if($count <= $ring1_star_count){//inner ring
				$newsystem['x'] = $ring1_radius * cos(deg2rad($ring1_preset));
				$newsystem['y'] = $ring1_radius * sin(deg2rad($ring1_preset));
				$newsystem['x'] += $centre;
				$newsystem['y'] += $centre;
				$ring1_preset += $ring1_degrees_between;
			} elseif($count <= ($ring2_star_count  + $ring1_star_count)|| $ring3_star_count == 0){//second ring
				$newsystem['x'] = $ring2_radius * cos(deg2rad($ring2_preset));
				$newsystem['y'] = $ring2_radius * sin(deg2rad($ring2_preset));
				$newsystem['x'] += $centre;
				$newsystem['y'] += $centre;
				$ring2_preset += $ring2_degrees_between;
			} else { //3rd ring if there is one.
				$newsystem['x'] = $ring3_radius * cos(deg2rad($ring3_preset));
				$newsystem['y'] = $ring3_radius * sin(deg2rad($ring3_preset));
				$newsystem['x'] += $centre;
				$newsystem['y'] += $centre;
				$ring3_preset += $ring3_degrees_between;
			}
		} else { // random layout
			do {
				$newsystem['x'] = mt_rand(0, $UNI['size']);
				$newsystem['y'] = mt_rand(0, $UNI['size']);
			} while (system_too_close($newsystem, $systems, $UNI['mindist']));
		}

		$systems[] = $newsystem;
	}
}


//aims to stop one way links from being created
//checks all systems that have already been linked to see what links have already been created to this location.
function pre_linked($systems, $present_system)
{
	$links_array = array();
	foreach ($systems as $new_sys) {
		if (!empty($new_sys['links']) &&
		     in_array($present_system, $new_sys['links'])) {
			$links_array[] = $systems[$new_sys['num']];
		}
	}

	return $links_array;
}


//link the systems
function link_systems(&$systems)
{
	global $UNI;

	foreach ($systems as $system) {
		$numlinks = mt_rand($UNI['minlinks'], $UNI['maxlinks']);

		//find the closest systems to the present system. when $numlinks closest found, link them
		$nearest = get_closest_systems($system, $systems, $numlinks);
		foreach ($nearest as $linksys) {
			make_link($systems[$system['num']], $systems[$linksys['num']]);
		}
	}

	//add wormholes if appropriate
	if ($UNI['wormholes'] > 0 && $UNI['numsystems'] > 15) {
		$num_worms = ceil($UNI['numsystems'] / 35);//num wormholes to make

		$worms_placed = array();

		for($a = 1; $a <= $num_worms; ++$a){//loop through

			$start_loc = mt_rand(2, $UNI['numsystems']);
			while(system_has_wormhole($worms_placed, $start_loc)) {
				$start_loc = mt_rand(2, $UNI['numsystems']);
			}
			$worms_placed[] = $start_loc;//push into wormhole checking array.


			$end_loc = mt_rand(1, $UNI['numsystems']);
			while (system_has_wormhole($worms_placed, $end_loc)) {
				$end_loc = mt_rand(1, $UNI['numsystems']);
			}
			$worms_placed[] = $end_loc;//push into wormhole checking array.

			//make them permanent
			$systems[$start_loc - 1]['wormhole'] = $end_loc;
			if (mt_rand(0, 1)) {//two way wormhole
				$systems[$end_loc - 1]['wormhole'] = $start_loc;
			}
		}
	}
}

//check to see if a star system has a wormhole in it already.
function system_has_wormhole(&$worms_placed, &$this_worm)
{
	foreach($worms_placed as $worm) {
		if($worm == $this_worm) {
			return true;
		}
	}

	return false;
}

//function that determines if it's ok to link to a system
function ok_to_link(&$sys1, &$sys2)
{
	global $UNI;

	// linking to itself.
	if ($sys1['num'] == $sys2['num']) {
		return false;
	}

	// return true if target still has empty links or is already linked.
	if ((count($sys2['links']) < $UNI['maxlinks']) || in_array($sys1['num'], $sys2['links'])) {
		return true;
	} else {
		return false;
	}
}

//find the closest systems to link to.
/*
$sys =  linking from
$systems = all systens
$howmany = number of closest systems to return
*/
function get_closest_systems($sys, $systems, $howmany)
{
	global $UNI;

	//check to see which systems have already linked to this one.
	$systems_to_link = pre_linked($systems, $sys['num']);
	$howmany -= count($systems_to_link);
	if ($howmany < 1) {
		return $systems_to_link;
	}

	//establish the distance of all stars in relation to this one
	$dists = array();
	foreach($systems as $system) {
		if (ok_to_link($sys, $system)) {
			$dists[$system['num']] = get_sys_dist($sys, $system);
		}
	}

	reset($dists);
	asort($dists, SORT_NUMERIC);

	// link to as many of the closest systems as can.
	$count = count($systems_to_link);
	while ($count < $howmany) {
		if (!$present_sys = each($dists)) {//get a system out of the dist array. RETURN if none.
			return $systems_to_link;
		}

		//too far away to be linked to (Sol System excepted).
		if ($present_sys['value'] > $UNI['link_dist'] &&
		     $UNI['link_dist'] > 0 && $sys['num'] != 0) {
			return $systems_to_link;
		}

		$systems_to_link[] = $systems[$present_sys['key']];
		++$count;
	}

	return $systems_to_link;
}

//work out if a system is too close to another system
function system_too_close(&$sys1, &$systems, $within)
{
	$quad = $within * $within;
	foreach ($systems as $sys2) {
		if ($sys1['num'] == $sys2['num']) {//same system
			continue;
		}
		if ((($sys1['x'] - $sys2['x']) * ($sys1['x'] - $sys2['x']) +
	         ($sys1['y'] - $sys2['y']) * ($sys1['y'] - $sys2['y'])) < $quad) {
			return true;
		}
	}

	return false;
}

//make a single link between two systems.
function make_link(&$sys1, &$sys2)
{
	if (empty($sys1['links']) || !in_array($sys2['num'], $sys1['links'])) {
		$sys1['links'][] = $sys2['num'];
	}
	if (empty($sys2['links']) || !in_array($sys1['num'], $sys2['links'])) {
		$sys2['links'][] = $sys1['num'];
	}
}

// work out the distance (in pixels) between two star systems
function get_sys_dist(&$sys1, &$sys2)
{
	return round(sqrt(($sys1['x'] - $sys2['x']) * ($sys1['x'] - $sys2['x']) +
	 ($sys1['y'] - $sys2['y']) * ($sys1['y'] - $sys2['y'])));
}


function renderGlobal($game_id)
{
	global $UNI, $games_dir, $systems, $preview, $directories,
	 $gen_new_maps, $gameOpt;

	$size = $UNI['size'] + ($UNI['map_border'] * 2);
	$central_star = 1;

	$im = imagecreatetruecolor($size, $size);

	if ($UNI['graphics']) {
		$earthIm = imagecreatefrompng('img/map/earth.png');
		$earthDim = array(imagesx($earthIm), imagesy($earthIm));
		$earthPos = array(-$earthDim[0] / 2, -$earthDim[1] / 2);

		$starIm = imagecreatefrompng('img/map/star.png');
		$starDim = array(imagesx($starIm), imagesy($starIm));
		$starPos = array(-$starDim[0] / 2, -$starDim[1] / 2);
	}

	//allocate the colours
	$color_bg = imagecolorallocate($im, $UNI['bg_color'][0],
	 $UNI['bg_color'][1], $UNI['bg_color'][2]);
	$color_st = imagecolorallocate($im, $UNI['num_color'][0],
	 $UNI['num_color'][1], $UNI['num_color'][2]);
	$color_sd = imagecolorallocate($im, $UNI['star_color'][0],
	 $UNI['star_color'][1], $UNI['star_color'][2] );
	$color_sl = imagecolorallocate($im, $UNI['link_color'][0],
	 $UNI['link_color'][1], $UNI['link_color'][2] );
	$color_sh = imagecolorallocate($im, $UNI['num_color3'][0],
	 $UNI['num_color3'][1], $UNI['num_color3'][2] );
	$color_l = imagecolorallocate($im, $UNI['label_color'][0],
	 $UNI['label_color'][1], $UNI['label_color'][2] );
	$worm_1way_color = imagecolorallocate($im,$UNI['worm_one_way_color'][0],
	 $UNI['worm_one_way_color'][1], $UNI['worm_one_way_color'][2] );
	$worm_2way_color = imagecolorallocate($im,$UNI['worm_two_way_color'][0],
	 $UNI['worm_two_way_color'][1], $UNI['worm_two_way_color'][2] );

	//get the star systems from the Db if using pre-genned map.
	if (isset($gen_new_maps)) {
		db("select (star_id -1) as num, x, y, wormhole, link_1, link_2, link_3, link_4, link_5, link_6 from ${game_id}_stars order by star_id asc");
		while ($system = dbr(1)) {
			$system['links'] = array();
			for ($i = 1; $i <= 6; ++$i) {
				$link = (int)$system['link_' . $i];
				if ($link !== 0) {
					$system['links'][] = $link - 1;
				}
			}
			$systems[] = $system;
		}
	}

	$central_star = 0;

	//process stars
	foreach ($systems as $star) {
		if (!empty($star['links'])) {//don't link all systems to 1 automatically.
			foreach ($star['links'] as $key => $value) {
				++$star['links'][$key];
			}
			$star_id = $star['num'] + 1;

			foreach ($star['links'] as $link) { // make star links
				if ($link < 1) {
					continue 1;
				}
				$other_star = $systems[$link - 1]; // set other_star to the link destination.
				imageline($im, $star['x'] + $UNI['map_border'],
				 $star['y'] + $UNI['map_border'],
				 $other_star['x'] + $UNI['map_border'],
				 $other_star['y'] + $UNI['map_border'], $color_sl);
			}
		}

		if(!empty($star['wormhole'])) { // Wormhole Manipulation
			$other_star = $systems[$star['wormhole'] - 1];
			if ($other_star['wormhole'] == $star_id) { //two way
				imageline($im, $star['x'] + $UNI['map_border'],
				 $star['y'] + $UNI['map_border'],
				 $other_star['x'] + $UNI['map_border'],
				 $other_star['y'] + $UNI['map_border'], $worm_2way_color);
			} else { //one way
				imageline($im, $star['x'] + $UNI['map_border'],
				 $star['y'] + $UNI['map_border'],
				 $other_star['x'] + $UNI['map_border'],
				 $other_star['y'] + $UNI['map_border'], $worm_1way_color);
			}
		}
	}

	if ($UNI['graphics']) {
		foreach ($systems as $star) {
			if ($star['num'] == $central_star) {
				imagecopy($im, $earthIm, $star['x'] + $UNI['map_border'] + $earthPos[0],
				 $star['y'] + $UNI['map_border'] + $earthPos[1], 0, 0,
				  $earthDim[0], $earthDim[1]);
			} else {
				imagecopy($im, $starIm, $star['x'] + $UNI['map_border'] + $starPos[0],
				 $star['y'] + $UNI['map_border'] + $starPos[1], 0, 0,
				  $starDim[0], $starDim[1]);
			}
		}
	} else {
		foreach ($systems as $star) {
			imagesetpixel($im, $star['x'] + $UNI['map_border'],
			 $star['y'] + $UNI['map_border'],
			 $star['num'] == $central_star ? $color_sh : $color_sd);
		}
	}

	if ($gameOpt['uv_show_warp_numbers'] == 1) {
		$off = array(6, -4);
		if (!$UNI['graphics']) {
		    $off = array(3, -4);
		}
		foreach ($systems as $star) {
		    if ($central_star === $star['num']) {
				imagestring($im, $UNI['num_size'] + 2,
				 $star['x'] + $UNI['map_border'] + $off[0],
				 $star['y'] + $UNI['map_border'] + $off[1],
				 $star['num'] + 1, $color_st);
		    } else {
				imagestring($im, $UNI['num_size'],
				 $star['x'] + $UNI['map_border'] + $off[0],
				 $star['y'] + $UNI['map_border'] + $off[1],
				 $star['num'] + 1, $color_st);
			}
		}
	}

	//for just a preview we can quite while we're ahead.
	if (isset($preview)) {
		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
		exit;
	}

	//Create buffer image
	imagecolorallocate($im, $UNI['bg_color'][0], $UNI['bg_color'][1],
	 $UNI['bg_color'][2]);

	//Create printable map
	$p_im = imagecreate($size, $size);
	imagecolorallocate($p_im, $UNI['print_bg_color'][0],
	 $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]);
	imagecopy($p_im, $im, 0, 0, 0, 0, $size, $size);

	//Replace colors
	$index = imagecolorexact($p_im, $UNI['bg_color'][0], $UNI['bg_color'][1],
	 $UNI['bg_color'][2]);
	imagecolorset($p_im, $index, $UNI['print_bg_color'][0],
	 $UNI['print_bg_color'][1], $UNI['print_bg_color'][2]);
	$index = imagecolorexact($p_im, $UNI['link_color'][0],
	 $UNI['link_color'][1], $UNI['link_color'][2]);
	imagecolorset($p_im, $index, $UNI['print_link_color'][0],
	 $UNI['print_link_color'][1], $UNI['print_link_color'][2]);
	$index = imagecolorexact($p_im, $UNI['num_color'][0],
	 $UNI['num_color'][1], $UNI['num_color'][2]);
	imagecolorset($p_im, $index, $UNI['print_num_color'][0],
	 $UNI['print_num_color'][1], $UNI['print_num_color'][2]);
	$index = imagecolorexact($p_im, $UNI['num_color3'][0],
	 $UNI['num_color3'][1], $UNI['num_color3'][2]);
	imagecolorset($p_im, $index, $UNI['print_num_color'][0],
	 $UNI['print_num_color'][1], $UNI['print_num_color'][2]);

	if (!$UNI['graphics']) {
		$index = imagecolorexact($p_im, $UNI['star_color'][0],
		 $UNI['star_color'][1], $UNI['star_color'][2]);
		imagecolorset($p_im, $index, $UNI['print_star_color'][0],
		 $UNI['print_star_color'][1], $UNI['print_star_color'][2]);
	}

	$dir = 'img/' . $game_id . '_maps';
	//Save map and finish
	if (!file_exists($dir)) {
		if (!@mkdir($dir, 0777)) {
		    exit('Unable to create make map directory!');
		}
	}
	imagepng($im, $dir . '/sm_full.png');
	imagepng($p_im, $dir . '/psm_full.png');

	imagedestroy($im);
	imagedestroy($p_im);
}

//draw the local maps.
function renderLocal($game_id)
{
	global $UNI, $db, $gameOpt;

	if (!file_exists('img/' . $game_id . '_maps')) {
		trigger_error('Map image is missing - dir does not exist', E_USER_ERROR);
	}

	$full_map = imagecreatefrompng("img/${game_id}_maps/sm_full.png");

	$stars = $db->query('SELECT star_id, x, y FROM [game]_stars');
	while($star = $db->fetchRow($stars)) {
		$im = imagecreatetruecolor($UNI['localmapwidth'], $UNI['localmapheight']);

		imagecopy($im, $full_map, 0, 0,
		 $star['x'] - $UNI['localmapwidth'] / 2 + $UNI['map_border'],
		 $star['y'] - $UNI['localmapheight'] / 2 + $UNI['map_border'],
		 $UNI['localmapwidth'], $UNI['localmapheight']);

		$color_hd = imagecolorallocate($im, $UNI['num_color2'][0],
		 $UNI['num_color2'][1], $UNI['num_color2'][2]);

		if ($gameOpt['uv_show_warp_numbers'] == 1) {
			$off = array(6, -4);
			if (!$UNI['graphics']) {
				$off = array(3, -4);
			}

			if ($star['star_id'] == 1) {
				imagestring($im, $UNI['num_size'] + 2,
				 $UNI['localmapwidth'] / 2 + $off[0],
				 $UNI['localmapwidth'] / 2 + $off[1],
				 $star['star_id'], $color_hd);
			} else {
				imagestring($im, $UNI['num_size'],
				 $UNI['localmapwidth'] / 2 + $off[0],
				 $UNI['localmapwidth'] / 2 + $off[1],
				 $star['star_id'], $color_hd);
			}
		}

		imagepng($im, 'img/' . $game_id . '_maps/sm' . $star['star_id'] . '.png');

		imagedestroy($im);
	}

	imagedestroy($full_map);
}

?>
