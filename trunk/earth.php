<?php

require_once('inc/user.inc.php');

if (!deathCheck($user) && $userShip['location'] != 1) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}

$out = '';

// Load fleet with colonists.
if (isset($all_colon) && $user['ship_id'] !== NULL) {
	$out .= fill_fleet("colon", "(cargo_bays-metal-fuel-elect-organ-colon)", "Colonists", $gameOpt['cost_colonist'], $self, 1)."<p>";
} elseif (isset($colonist) && $user['ship_id'] !== NULL) { #individual ship load
	$max = floor($user['cash'] / $gameOpt['cost_colonist']);
	$fill = $userShip['empty_bays'] < $max ? $userShip['empty_bays'] : $max;

	$amount = isset($amount) ? (int)$amount : 0;
	if ($amount <= 0) {
		get_var('Take Colonists', $self, "<p><a href=\"earth.php?all_colon=1\">Fill Ship</a>  How many colonists do you want to take?  They cost <b>$gameOpt[cost_colonist]</b> credit(s) each.</p>\n", 'amount', $fill);
	} elseif($fill < 1) {
		$out .= "<p>You do not have the facilities (either money OR cargo space) to buy colonists. Try a different ship.</p>";
	}elseif($amount > $userShip['empty_bays']) {
		$out .= "<p>You can't carry that many colonists.</p>";
	} elseif(!giveMoneyPlayer(-$gameOpt['cost_colonist'] * $amount)) {
		$out .= "<p>You can't afford that many colonists.</p>";
	} else {
		$db->query('UPDATE [game]_ships SET colon = colon + %u WHERE ' .
		 'ship_id = %u', array($amount, $user['ship_id']));
		checkShip();
	}
}


if ($userOpt['show_pics']) {
	$out .= "<h1><img src=\"img/places/earth.jpg\" alt=\"Earth - centre of the universe\" /></h1>\n";
} else {
	$out .= "<h1>Earth - centre of the universe</h1>\n";
}

	$out .= <<<END
<h2>Places to visit</h2>
<dl>
	<dt><a href="shop_ship.php">Spacecraft emporium</a></dt>
	<dd>Buy and sell intergalactic vessels</dd>

END;


if ($user['ship_id'] !== NULL) {
	$out .= <<<END
	<dt><a href="shop_equipment.php?planet_id=1">Equipment 
	shop</a></dt>
	<dd>For that competitive advantage</dd>

	<dt><a href="shop_upgrades.php">Ship upgrades</a></dt>
	<dd>A variety of upgrades for ships including cargo and fighter 
	capacity</dd>

	<dt><a href="earth.php?colonist=1">Colonist recruitment centre</a> &#8212; 
	<a href="earth.php?all_colon=1">fill fleet</a></dt>
	<dd>These servants populate your planets and power your empire</dd>

END;
}

$out .= <<<END
	<dt><a href="shop_auction_house.php">Auction house</a></dt>
	<dd>Buy and sell a variety of goods unavailable elsewhere</dd>

	<dt><a href="bounty.php">&#8216;Charity shop&#8217;</a></dt>
	<dd>Place a bounty on a player&#8217;s head</dd>
</dl>
<h2><a href="system.php">Back into space</a></h2>
END;

print_page('Earth', $out);

?>
