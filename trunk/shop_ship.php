<?php

require_once('inc/user.inc.php');

if (!deathCheck($user) && $userShip['location'] != 1) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}

$shipWorth = floor($userShip['max_hull'] + ($userShip['max_shields'] >> 1) + 
 ($userShip['fighters'] * $gameOpt['fighter_cost_earth'] / 10) +
 ($userShip['mining_rate'] * 10) + $userShip['max_fighters'] + 
 ($userShip['upgrades'] * 20));


if (isset($_REQUEST['action'])) {
	if ($_REQUEST['action'] === 'sell' && $userShip !== NULL) {
		$loc = get_star();
		$newId = closestShip($user['login_id'], $loc['x'], $loc['y']);

		if ($newId === false) {
			print_page('Sell ship', 'You have no other ships!');
		}

		$db->query('DELETE FROM [game]_ships WHERE ship_id = %u', 
		 array($user['ship_id']));
		$db->query('UPDATE [game]_users SET ship_id = %u WHERE login_id = %u', 
		 array($newId, $user['login_id']));

		giveMoneyPlayer($shipWorth);

		print_page('Sell ship', "<p>Your ship has been sold for " .
		 "<em>$shipWorth credits</em></p>\n");
	}
}

$out = <<<END
<h1>Spacecraft Emporium</h1>
<p>All the finest ships at bargain prices: 
<a href="help.php?ship_info=1&amp;shipno=-1">information about ships</a>.  
Return to <a href="earth.php">earth</a> after making your purchase.</p>
<p>We will <a href="shop_ship.php?action=sell" 
 onclick="return confirm('Are you sure?');">buy your ship</a> from you for 
<em>$shipWorth credits</em>.</p>

END;

$merc_text = $bat_text = $car_text = $other_text = $rd_text = '';

$ship_types = load_ship_types();
foreach ($ship_types as $type_id => $ship_stats) {
	$link = popup_help("help.php?popup=1&amp;ship_info=1&shipno=$type_id",300,600);
	if($ship_stats['type'] == "Freighter") {
		$merc_text .= make_row(array("<a href=\"ship_build.php?ship_type=$type_id\">$ship_stats[name]</a>", "$ship_stats[abbr]","<b>$ship_stats[cost]</b>", "<a href=\"ship_build.php?ship_type=$type_id\">Buy One</a>", "<a href=\"ship_build.php?mass=$type_id\">Buy Many</a>", $link));
	} elseif($ship_stats['type'] == "Battleship") {
		$bat_text .= make_row(array("<a href=\"ship_build.php?ship_type=$type_id\">$ship_stats[name]</a>", "$ship_stats[abbr]","<b>$ship_stats[cost]</b>", "<a href=\"ship_build.php?ship_type=$type_id\">Buy One</a>", $link));
	} elseif($ship_stats['type'] == "Raider") {
		$rd_text .= make_row(array("<a href=\"ship_build.php?ship_type=$type_id\">$ship_stats[name]</a>", "$ship_stats[abbr]", "<b>$ship_stats[cost]</b>", $link, "<b>$ship_stats[cost]</b>", "<a href=\"ship_build.php?ship_type=$type_id\">Buy One</a>", $link));
	} elseif (stristr($ship_stats['type'], 'Carrier') !== false) {
		$car_text .= make_row(array("<a href=\"ship_build.php?ship_type=$type_id\">$ship_stats[name]</a>", "$ship_stats[abbr]", "<b>$ship_stats[cost]</b>", "<a href=\"ship_build.php?ship_type=$type_id\">Buy One</a>", $link));
	} else {
		if($user['one_brob'] > 0 && shipHas($ship_stats, 'oo')) {
			$ship_stats['cost'] = $ship_stats['cost'] * $user['one_brob'];
		}
		$other_text .= make_row(array("<a href=\"ship_build.php?ship_type=$type_id\">$ship_stats[name]</a>", "$ship_stats[abbr]", "$ship_stats[type]", "<b>$ship_stats[cost]</b>", "<a href=\"ship_build.php?ship_type=$type_id\">Buy One</a>", $link));
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
