<?php

require_once('inc/user.inc.php');

if (!deathCheck($user) && $userShip['location'] != 1) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}


$out = <<<END
<h1>Spacecraft Emporium</h1>
<p>All the finest ships at bargain prices: 
<a href="help.php?ship_info=1&amp;shipno=-1">information about ships</a>.  
Return to <a href="earth.php">earth</a> after making your purchase.</p>

END;

$merc_text = $bat_text = $car_text = $other_text = $rd_text = "";

$ship_types = load_ship_types();
foreach ($ship_types as $type_id => $ship_stats) {
	$link = popup_help("help.php?popup=1&ship_info=1&shipno=$type_id",300,600);
	if($ship_stats['type'] == "Freighter") {
		$merc_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[abbr]","<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "<a href=ship_build.php?mass=$type_id>Buy Many</a>", "$link<b></b></a>"));
	} elseif($ship_stats['type'] == "Battleship") {
		$bat_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[abbr]","<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
	} elseif($ship_stats['type'] == "Raider") {
		$rd_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[abbr]", "<b>$ship_stats[cost]</b>", "$link<b></b></a>","<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
	} elseif (stristr($ship_stats['type'], 'Carrier') !== false) {
		$car_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[abbr]", "<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
	} else {
		if($user['one_brob'] > 0 && shipHas($ship_stats, 'oo')) {
			$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
		}
		$other_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[abbr]", "$ship_stats[type]", "<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
	}
}

#
#Merchants
#
if (!empty($merc_text)) {
	$out .= "<h2>Freighters</h2>";
	$out .= make_table(array("Ship Name","Abbrv.","Cost"));
	$out .= $merc_text;
	$out .= "</table>";
}

#
#Battleships
#
if (!empty($bat_text)) {
	$out .= "<h2>Battleships</h2>";
	$out .= make_table(array("Ship Name","Abbrv.","Cost"));
	$out .= $bat_text;
	$out .= "</table>";
}

#
#Raiders
#
if (!empty($rd_text)) {
	$out .= "<h2>Raiders</h2>";
	$out .= make_table(array("Ship Name","Abbrv.","Cost"));
	$out .= $rd_text;
	$out .= "</table>";
}

#

// Carriers
if (!empty($car_text)) {
	$out .= "<h2>Carriers</h2>";
	$out .= make_table(array("Ship Name","Abbrv.","Cost"));
	$out .= $car_text;
	$out .= "</table>";
}

#
#Other Ships
#
$out .= "<h2>Other types</h2>";
if(!isset($other_text)){
	$out .= "<p>None</p>";
} else {
	$out .= make_table(array("Ship Name","Abbrv.","Type","Cost"));
	$out .= $other_text;
	$out .= "</table>";
}

print_page('Ship shop', $out);

?>
