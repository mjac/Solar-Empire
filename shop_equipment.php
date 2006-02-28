<?php

require_once('inc/user.inc.php');

if (deathCheck($user) || $userShip === NULL || $userShip['location'] != 1) {
	print_page("Error", "You are unable to buy equipment here.");
}


$error_str = "";

#fighter cost is now based upon the admin var.
#$fighter_cost = 100;
$fighter_cost = $gameOpt['fighter_cost_earth'];
if($fighter_cost <= 0){
	$fighter_cost = 1;
}

$shield_cost = 50;
$sn_cost = $gameOpt['bomb_cost'] * 5;
$amount = isset($amount) ? round($amount) : 0;

//function that allows for quick and simple purchase of basic items.
function buyShipItem($item_sql, $item_max_sql, $item_str, $cost)
{
	global $amount, $user, $userShip, $db_name, $self;

	$amount = round($amount); //security check

	$ret_str = "";

	if($userShip[$item_sql] >= $userShip[$item_max_sql]){
		$ret_str .= "Your ship is already full of <b class=b1>$item_str</b>.";

	} elseif($amount < 1) {
		$amount_can_buy = floor($user['cash'] / $cost);
		if($amount_can_buy > $userShip[$item_max_sql] - $userShip[$item_sql]) {
			$amount_can_buy = $userShip[$item_max_sql] - $userShip[$item_sql];
		}

		get_var("Buy $item_str", $self, "How many <b class=b1>$item_str</b> do you want to buy?",'amount',$amount_can_buy);

	} else {
		$total_cost = $amount * $cost;
		if($userShip[$item_sql] + $amount > $userShip[$item_max_sql]) {
			$ret_str .= "Your ship can't hold that many more <b class=b1>$item_str</b>.<p>";
		} elseif (!giveMoneyPlayer(-$total_cost)) {
			$ret_str .= "You cannot afford that many <b class=b1>$item_str</b>.<p>";
		} else {
			$ret_str .= "<b>$amount</b> <b class=b1>$item_str</b> purchased for <b>$total_cost</b> Credits.<p>";
			giveMoneyPlayer(-$total_cost);

			dbn("update [game]_ships set $item_sql = $item_sql + '$amount' where ship_id = '$userShip[ship_id]'");

			$userShip[$item_sql] += $amount;
		}
	}
	return $ret_str;
}

function buyEquipment($field, $name, $cost, $amount)
{
	global $db, $user;

	$amount = round($amount);
	$total = ceil($cost * $amount);

	if ($amount < 1) {
		return "<p>You may not sell here.</p>\n";
	}

	if (!giveMoneyPlayer(-$total)) {
	    return "<p>You can not afford <strong>$amount $name(s)</strong>.</p>\n";
	}

	$db->query('UPDATE [game]_users SET %s = %s + %u',
	 array($db->escape($field), $db->escape($field), $amount));

	return "<p><strong>$amount $name(s)</strong> purchased for <em>$total " .
	 "credits</em></p>\n";
}




// checks
if(isset($buy)) {
	if ($buy == 1) { //fighters
		$error_str .= buyShipItem("fighters", "max_fighters", "Fighters", $fighter_cost, 1);
	} elseif ($buy == 2) { //shields
		$error_str .= buyShipItem("shields", "max_shields", "Shields", $shield_cost, 1);
	} elseif ($buy == 5) { // genesis device
		if ($uv_planets < 1 || IS_ADMIN) {
			$error_str .= buyEquipment("genesis", "Genesis Device", $cost_genesis_device, 1);
		} else {
		    $error_str .= "<p>Genesis devices are unavailable - planets " .
			 "cannot be created or destroyed artificially.</p>\n";
		}
	} elseif ($buy == 'gamma') { // gamma bomb
	    if ($bomb_level_shop >= 1 || IS_ADMIN) {
	        $error_str .= buyEquipment('gamma', 'Gamma Bomb', $bomb_cost, 1);
	    } else {
			$error_str .= "<p>Gamma bombs are outlawed.</p>\n";
		}
	} elseif ($buy === 'alpha') { // alpha bomb
	    if ($bomb_level_shop >= 1 || IS_ADMIN) {
	        $error_str .= buyEquipment('alpha', 'Alpha Bomb', $bomb_cost, 1);
	    } else {
			$error_str .= "<p>Alpha bombs are outlawed.</p>\n";
		}
	} elseif ($buy === 'delta') { // alpha bomb
	    if ($bomb_level_shop >= 2 || IS_ADMIN) {
	        $error_str .= buyEquipment('delta', 'Delta Bomb', $bomb_cost * 50, 1);
	    } else {
			$error_str .= "<p>Delta bombs are outlawed.</p>\n";
		}
	} elseif($buy == 10){
		$taken = 0; //Fighters taken from planet so far.
		$ship_counter = 0;
		db("select sum(max_fighters-fighters), count(ship_id) from [game]_ships where location = 1 AND login_id='$user[login_id]' AND max_fighters > 0 AND fighters < max_fighters");
		$maths=dbr();
		if($user['cash'] < $fighter_cost){
			print_page("Failed","You don't have enough money for one fighter, let alone a fleet of them.<br />Come back when you can afford it");
		} elseif(!$maths[0]) {
			print_page("Failed","This operation failed as there are no ships that have fighter bays empty in this system that belong to you.");
		} elseif($sure != "yes") {
			get_var('Load all ships', $self,"There are <b>$maths[0]</b> empty fighter bays in <b>$maths[1]</b> ships in this system. <br />Do you want to fill as many as you can afford to fill?",'sure','yes');
		} else {
			db2("select ship_id,fighters,max_fighters,ship_name from [game]_ships where login_id = '$user[login_id]' AND location = 1 AND max_fighters > 0 AND fighters < max_fighters order by max_fighters desc");
			while($ships = dbr2()) {
				//player can load ship.
				$free = $ships['max_fighters'] - $ships['fighters'];
				if($user['cash'] >= ($free * $fighter_cost)) {
					$ship_counter++;
					dbn("update [game]_ships set fighters = max_fighters where ship_id = '$ships[ship_id]'");
					$out .= "<br /><b class=b1>$ships[ship_name]</b> had its fighter cargo increased by <b>$free</b> to maximum capacity.";
					if($ships['ship_id'] == $userShip['ship_id']){
						$userShip['fighters'] = $userShip['max_fighters'];
					}
					$taken += $free;
					giveMoneyPlayer(-$free * $fighter_cost);
				//player will run out of cash.
				} else {
					$ship_counter++;
					$t868 = $ships['fighters'] + floor($user[cash]/$fighter_cost);
					dbn("update [game]_ships set fighters = '$t868' where ship_id = '$ships[ship_id]'");
					if ($ships['ship_id'] == $userShip['ship_id']) {
						$userShip['fighters'] = $t868;
					}
					$taken += $t868 - $ships['fighters'];
					$q_m = ($t868 - $ships['fighters']) * $fighter_cost;
					$out .= "<br /><b class=b1>$ships[ship_name]</b>s fighter count was increased to <b>$t868</b>.";
					giveMoneyPlayer(-$q_m);
					break;
				}
			}
			if($ship_counter > 0){
				$cost=$taken*$fighter_cost;
				print_page("Fighters Loaded","<b>$ship_counter</b> ships had their fighters augmented by new fighters from Sol.<br />Total New Fighters = <b>$taken</b>; Cost = <b>$cost</b><p>More Detailed Statistics :".$out);
			} else {
				print_page("No Ships","No ships where loaded as all ships in this system are already full of fighters.");
			}
		}
	}
}

if(isset($fill_fleet)) { //fill fleet functionality
	if($fill_fleet == 1){ //fighters
		$error_str .= fill_fleet("fighters", "max_fighters", "Fighters", $fighter_cost, $self);
	} else { //shields
		$error_str .= fill_fleet("shields", "max_shields", "Shields", $shield_cost, $self);

	}
}


$error_str .= <<<END
<h1>Equipment Shop</h1>
<h2>Ship additions</h2>
<dl>
	<dt>Fighters</dt>
	<dd><img src="img/equipment/fighter.jpg" alt="Fighter" /></dd>
	<dd>Fighters are the units that participate in a battle situation.
	They deal damage to enemy ships and counter-damage when attacked.</dd>
	<dd>Purchase for your <a href="$self?buy=1">ship</a> or
	<a href="$self?fill_fleet=1">fleet</a> at <em>$fighter_cost credits per
	fighter</em></dd>

	<dt>Shields</dt>
	<dd><img src="img/equipment/shield.jpg" alt="Shielded ship" /></dd>
	<dd>A ship that has only shields will only be able to take damage.
	Shields absorb the first impacts of enemy fighters on your ship.
	Once they have run-out the fighters get to work in defending you ship.
	Shields automatically replenish over each hour until they reach the maximum
	a ship can hold.</dd>
	<dd>Purchase for your <a href="$self?buy=2">ship</a> or
	<a href="$self?fill_fleet=2">fleet</a> at <em>$shield_cost credits per
	shield</em>.</dd>
</dl>

END;

if ($uv_planets < 1 || IS_ADMIN) {
	$error_str .= <<<END
<h2>Planet devices</h2>
<dl>
	<dt>Genesis Device</dt>
	<dd><img src="img/equipment/genesis_device.jpg" alt="Genesis device" /></dd>
	<dd>These are used to create a planet.  One thousand of your
	followers accompany it and inhabit the planet after its creation.</dd>
	<dd><a href="$self?buy=5">Purchase one</a> for
	<em>$cost_genesis_device credits</em>.</dd>
</dl>

END;
}


if ($bomb_level_shop >= 1 || IS_ADMIN) {
	$error_str .= <<<END
<h2>System-wide bombs</h2>
<dl>
	<dt>Alpha bomb</dt>
	<dd><img src="img/equipment/bomb_alpha.jpg" alt="Alpha bomb" /></dd>
	<dd>The singularity created by this bomb <strong>removes all
	shields</strong> in this system before disappearing from the universe
	altogether.</dd>
	<dd><a href="$self?buy=alpha">Purchase one</a> for
	<em>$bomb_cost credits</em>.</dd>

	<dt>Gamma bomb</dt>
	<dd><img src="img/equipment/bomb_gamma.jpg" alt="Gamma bomb" /></dd>
	<dd>Causes <strong>blast damage of 200</strong> to every local ship.</dd>
	<dd><a href="$self?buy=gamma">Purchase one</a> for
	<em>$bomb_cost credits</em>.</dd>

END;

    if ($bomb_level_shop >= 2 || IS_ADMIN) {
        $cost = $bomb_cost * 50;
		$error_str .= <<<END

	<dt>Delta bomb</dt>
	<dd><img src="img/equipment/bomb_delta.jpg" alt="Delta bomb" /></dd>
	<dd>Creates a massive ripple in space-time causing <strong>tremendous
	damage</strong> to all local ships.</dd>
	<dd><a href="$self?buy=delta">Purchase one</a> for
	<em>$cost credits</em>.</dd>

END;
    }

	$error_str .= "</dl>\n";
}

/*if (($transwarp_cost) || ($user[login_id] ==1)) {
	$error_str .= "<br /><a href=$self?buy=7>Transwarp Drive</a>: $transwarp_cost";
}*/


print_page("Equipment Shop",$error_str);
?>
