<?php

require_once('inc/user.inc.php');

$filename = 'earth.php';

$planet_id = 1;

$rs = "<p><a href=earth.php>Return to Earth</a>";

// user can't access any functions as they are dead and sudden death has been set

sudden_death_check($user);

$amount = round($amount);

$error_str = "";

// ensure user is in system 1 b4 continuing
if ($user['location'] != 1) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}


// Load fleet with colonists.
if(isset($all_colon) && $user['ship_id'] != NULL){
	$error_str .= fill_fleet("colon", "(cargo_bays-metal-fuel-elect-organ-colon)", "Colonists", $cost_colonist, $filename, 1)."<p>";
} elseif(isset($colonist) && $user['ship_id'] != NULL) { #individual ship load
	if($user['cash'] < ($user_ship['empty_bays']*$cost_colonist)){
		$fill = floor($user['cash']/$cost_colonist);
	}

	settype($amount, "integer");
	if($user['turns'] < 1) {
		$error_str .= "You don't have enough turns to load colonists onto a ship.<p>";
	} elseif($amount <= 0) {
		get_var('Take Colonists',$filename,'<a href=earth.php?all_colon=1>Fill Ship</a><p>How many colonists do you want to take?<br>They cost <b>'.$cost_colonist.'</b> credit(s) each.<p>','amount',$fill);
	} elseif($fill < 1) {
		$error_str .= "You do not have the facilities (either money OR cargo space) to buy colonists. Try a different ship.<p>";
	}elseif($amount > $user_ship['empty_bays']) {
		$error_str .= "You can't carry that many colonists.<p>";
	} elseif($amount*$cost_colonist > $user['cash']) {
		$error_str .= "You can't afford that many colonists.<p>";
	} else {
		take_cash($cost_colonist*$amount);
		charge_turns(1);
		dbn("update ${db_name}_ships set colon = colon + $amount where ship_id = $user[ship_id]");
		$user_ship['colon'] += $amount;
		$user_ship['empty_bays'] -= $amount;
	}
}

pageStart();


echo $error_str;

if(isset($ship_shop)) {
	echo "Welcome to <b>Seatogu's Spacecraft Emporium.</b>";
	echo "<br>Where you'll find all the finest ships, at bargain prices.<br>";
	$merc_text = "";
	$bat_text = "";
	$car_text = "";
	$other_text = "";

	$ship_types = load_ship_types();
	foreach($ship_types as $type_id => $ship_stats){
		if ($type_id < 3 || $ship_stats['tcost'] != 0) { //skip the EP and SD, as well as BM ships.
			continue;
		}

		$link = popup_help("help.php?popup=1&ship_info=1&shipno=$type_id",300,600);
		$ship_stats['cost'] = number_format($ship_stats['cost']);
		if($ship_stats['type'] == "Freighter") {
			$merc_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[class_abbr]","<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "<a href=ship_build.php?mass=$type_id>Buy Many</a>", "$link<b></b></a>"));
		} elseif($ship_stats['type'] == "Battleship") {
			$bat_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[class_abbr]","<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
		} elseif($ship_stats['type'] == "Raider") {
			$rd_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[class_abbr]", "<b>$ship_stats[cost]</b>", "$link<b></b></a>","<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
		} elseif(eregi("Carrier",$ship_stats['type'])) {
			$car_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[class_abbr]", "<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
		} else {
			if($user['one_brob'] > 0 && eregi("oo",$ship_stats['config'])) {
				$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
			}
			$other_text .= make_row(array("<a href=ship_build.php?ship_type=$type_id>$ship_stats[name]</a>", "$ship_stats[class_abbr]", "$ship_stats[type]", "<b>$ship_stats[cost]</b>", "<a href=ship_build.php?ship_type=$type_id>Buy One</a>", "$link<b></b></a>"));
		}
	}

	#
	#Merchants
	#
	echo "<p>Freighters available:";
	if(!isset($merc_text)){
		echo "<br><b>None</b>";
	} else {
		echo make_table(array("Ship Name","Abbrv.","Cost"));
		echo stripslashes($merc_text);
		echo "</table>";
	}

	#
	#Battleships
	#
	echo "<p>Battleships available:";
	if(!isset($bat_text)){
		echo "<br><b>None</b>";
	} else {
		echo make_table(array("Ship Name","Abbrv.","Cost"));
		echo stripslashes($bat_text);
		echo "</table>";
	}

/*	#
	#Raiders
	#
	echo "<p>Raiders available:";
	if(!isset($rd_text)){
		echo "<br><b>None</b>";
	} else {
		echo make_table(array("Ship Name","Abbrv.","Cost"));
		echo stripslashes($rd_text);
		echo "</table>";
	}*/

	#

	// Carriers
	if (isset($car_text)) {
		echo "<p>Carriers available:";
		echo make_table(array("Ship Name","Abbrv.","Cost"));
		echo stripslashes($car_text);
		echo "</table>";
	}

	#
	#Other Ships
	#
	echo "<p>Ships of other types:";
	if(!isset($other_text)){
		echo "<br><b>None</b>";
	} else {
		echo make_table(array("Ship Name","Abbrv.","Type","Cost"));
		echo stripslashes($other_text);
		echo "</table>";
	}

	echo "<p><a href=help.php?ship_info=1&shipno=-1 target=_blank>List all information for all ships.</a>";
	echo $error_str;


//load earth page
} else {
	if ($user_options['show_pics']) {
		print "<h1><img src=\"img/earth.jpg\" alt=\"Earth - centre of the universe\" /></h1>\n";
	} else {
		print "<h1>Earth - centre of the universe</h1>\n";
	}

	print <<<END
<h2>Places to visit</h2>
<ul>
	<li><a href="earth.php?ship_shop=1">Spacecraft Emporium</a></li>

END;


	if ($user['ship_id'] != NULL) {
		print <<<END
	<li><a href="equip_shop.php?planet_id=$planet_id">Equipment Shop</a></li>
	<li><a href="upgrade.php">Accessories/Upgrades Store</a></li>
	<li><a href="earth.php?colonist=1">Colonist Recruitment Center</a> - <a href=earth.php?all_colon=1>Fill Fleet</a></li>

END;
	}

	print <<<END
	<li><a href="bilkos.php">Auction House</a></li>
	<li><a href="bounty.php">Charity Shop</a> (illegal)</li>
</ul>
<h2><a href="location.php">Back into space</a></h2>
END;
}

pageStop('Earth - the centre of the universe');

?>
