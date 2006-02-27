<?php

require_once('inc/user.inc.php');

$filename = 'earth.php';

$planet_id = 1;

$rs = "<p><a href=earth.php>Return to Earth</a>";

// user can't access any functions as they are dead and sudden death has been set

deathCheck($user);


$out = "";

// ensure user is in system 1 b4 continuing
if ($userShip['location'] != 1 && $user['ship_id'] !== NULL) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}


// Load fleet with colonists.
if(isset($all_colon) && $user['ship_id'] !== NULL){
	$out .= fill_fleet("colon", "(cargo_bays-metal-fuel-elect-organ-colon)", "Colonists", $gameOpt['cost_colonist'], $filename, 1)."<p>";
} elseif(isset($colonist) && $user['ship_id'] !== NULL) { #individual ship load
	$max = floor($user['cash'] / $gameOpt['cost_colonist']);
	$fill = $userShip['empty_bays'] < $max ? $userShip['empty_bays'] : $max;

	$amount = isset($amount) ? (int)$amount : 0;
	if ($amount <= 0) {
		get_var('Take Colonists', $self, '<a href=earth.php?all_colon=1>Fill Ship</a><p>How many colonists do you want to take?<br />They cost <b>' . $gameOpt['cost_colonist'] . '</b> credit(s) each.<p>','amount',$fill);
	} elseif($fill < 1) {
		$out .= "You do not have the facilities (either money OR cargo space) to buy colonists. Try a different ship.<p>";
	}elseif($amount > $userShip['empty_bays']) {
		$out .= "You can't carry that many colonists.<p>";
	} elseif($amount * $gameOpt['cost_colonist'] > $user['cash']) {
		$out .= "You can't afford that many colonists.<p>";
	} else {
		giveMoneyPlayer(-$gameOpt['cost_colonist'] * $amount);
		$db->query("update [game]_ships set colon = colon + $amount where ship_id = $user[ship_id]");
		$userShip['colon'] += $amount;
		$userShip['empty_bays'] -= $amount;
	}
}

if(isset($ship_shop)) {

	$out .= <<<END
<h1>Spacecraft Emporium</h1>
<p><q>Where you will find all the finest ships, at bargain prices.</q></p>
<p><a href="help.php?ship_info=1&amp;shipno=-1">Information about ships</a></p>

END;

	$merc_text = $bat_text = $car_text = $other_text = $rd_text = "";

	$ship_types = load_ship_types();
	foreach($ship_types as $type_id => $ship_stats){
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
//load earth page
} else {
	if ($userOpt['show_pics']) {
		$out .= "<h1><img src=\"img/places/earth.jpg\" alt=\"Earth - centre of the universe\" /></h1>\n";
	} else {
		$out .= "<h1>Earth - centre of the universe</h1>\n";
	}

	$out .= <<<END
<h2>Places to visit</h2>
<ul>
	<li><a href="earth.php?ship_shop=1">Spacecraft Emporium</a></li>

END;


	if ($user['ship_id'] !== NULL) {
		$out .= <<<END
	<li><a href="shop_equipment.php?planet_id=$planet_id">Equipment Shop</a></li>
	<li><a href="shop_upgrades.php">Accessories/Upgrades Store</a></li>
	<li><a href="earth.php?colonist=1">Colonist Recruitment Center</a> - <a href=earth.php?all_colon=1>Fill Fleet</a></li>

END;
	}

	$out .= <<<END
	<li><a href="auction_house.php">Auction House</a></li>
	<li><a href="bounty.php">Charity Shop</a> (illegal)</li>
</ul>
<h2><a href="system.php">Back into space</a></h2>
END;
}

print_page('Earth', $out);

?>
