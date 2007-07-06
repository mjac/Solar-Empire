<?php

require_once('inc/user.inc.php');

$planet_id = 1;

$rs = "<p><a href=earth.php>Return to Earth</a>";

if (!deathCheck($user) && $userShip['location'] != 1) {
	print_page('Not in Sol', '<p>You are not in Star System #1</p>');
}


$out = '';

// Load fleet with colonists.
if(isset($all_colon) && $user['ship_id'] !== NULL){
	$out .= fill_fleet("colon", "(cargo_bays-metal-fuel-elect-organ-colon)", "Colonists", $gameOpt['cost_colonist'], $self, 1)."<p>";
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
	<dt><a href="shop_equipment.php?planet_id=$planet_id">Equipment 
	shop</a></dt>
	<dd>For that competitive advantage</dd>

	<dt><a href="shop_upgrades.php">Ship upgrade Store</a></dt>
	<dd>A variety of upgrades for ships including cargo and fighter 
	capacity</dd>

	<dt><a href="earth.php?colonist=1">Colonist recruitment centre</a> &#8212; 
	<a href=earth.php?all_colon=1>fill fleet</a></dt>
	<dd>These servants populate your planets and power your empire</dd>

END;
}

$out .= <<<END
	<dt><a href="shop_auction_house.php">Auction house</a></dt>
	<dd>Buy and sell a variety of goods unavailable elsewhere</dd>

	<dt><a href="bounty.php">&#8216;Charity shop&#8217;</a></dt>
	<dd>Place a bounty on a player&#8217;s head</dd>
</ul>
<h2><a href="system.php">Back into space</a></h2>
END;

print_page('Earth', $out);

?>
