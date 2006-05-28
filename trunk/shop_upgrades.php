<?php

require_once('inc/user.inc.php');

if (deathCheck($user) || $userShip === NULL || $userShip['location'] != 1) {
	print_page("Error", "You are unable to buy upgrades here.");
}

//increases in capacity:
$fighter_inc = 300;
$shield_inc = 100;
$cargo_inc = 100;

// Basic costs
$basic_cost = 5000; // cost of the 3 basic upgrades.

// Cloak cost also based on size of ship
$cloak_cost = ceil(sqrt($userShip['hull'])) * 1000;

$scanner_cost = 20000;
$transwarp_cost = 20000;
$shield_charger = 20000;
$stabiliser_upgrade = 65000;

$error_str = '';

#ensure user has some upgrade pods free.
if ($userShip['upgrades'] < 1) {
	$out = <<<END
<h1>You have no upgrade pods</h1>
<p>This ship has no upgrade pods available.  Our upgrades require special 
pods.  As this ship has no such pods, we cannot do anything to it.</p>

END;
	print_page('Accessories and upgrades', $out);
}

#function for adding 'normal' upgrades to a ship.
function make_basic_upgrade($upgrade_str, $upgrade_sql, $inc_amount, $cost, 
 $amount = 1)
{
	global $user, $userShip, $db;

	$totalAmount = $inc_amount * $amount;
	$totalCost = $cost * $amount;

	if ($amount < 1) {
		return "<p>Invalid amount: please purchase one or more</p>\n";
	} elseif ($userShip['upgrades'] < $amount) {
		return "<p>You do not have enough upgrade pods</p>\n";
	} elseif (!giveMoneyPlayer(-$cost * $amount)) {
		return "<p>You can not afford that.</p>\n";
	} else {
		$db->query("UPDATE [game]_ships SET $upgrade_sql = $upgrade_sql + " .
		 "%d, upgrades = upgrades - %u WHERE ship_id = %u", 
		 array($inc_amount * $amount, $amount, $userShip['ship_id']));

		checkShip();

		return <<<END
<p>You have increased the <strong>$upgrade_str capacity</strong> on 
$userShip[ship_name] by <em>$totalAmount</em> for $totalCost credits.</p>

END;
	}
}


//a function to allow for easy addition of upgrades.
function make_standard_upgrade($upgrade_str, $config_addon, $cost,
 $developement_id)
{
	global $user, $userShip, $db;
	if (shipHas($userShip, $config_addon)){
		return "Your ship is already equipped with a <b class=b1>$upgrade_str</b>.<br />There is no point in having more than one on a ship.<p>";
	} elseif ($userShip['upgrades'] < 1){
		return '';
	} elseif (!giveMoneyPlayer(-$cost)) {
		return "You can not afford to buy a <b class=b1>$upgrade_str</b>.<p>";
	} else {
		$configArr = explode(':', $userShip['config']);
		if (empty($configArr[0])) {
			$configArr[0] = $config_addon;
		} else {
			$configArr[] = $config_addon;
		}
		
		$userShip['config'] = implode(':', $configArr);

		$db->query("update [game]_ships set config = '$userShip[config] ', upgrades = upgrades - 1 where ship_id = '$user[ship_id]'");

		--$userShip['upgrades'];

		return "<p>You have purchased and fitted a <strong>$upgrade_str</strong> to the $userShip[ship_name] for $cost credits.</p>";
	}
}


// checks
if (isset($buy)) {
	if ($buy == 1) { // Fighter capacity
		if($userShip['max_fighters'] + $fighter_inc > 5000 && !shipHas($userShip, 'bs')) {
			$error_str .= "It is against regulations to have more than 5000 fighter capacity on a ship unless the ship is registered as a battleship.<br />To do that you'll have to purchase a battleship upgrade from the auction house.<p>";
		} else {
			$error_str .= make_basic_upgrade("fighter", "max_fighters", $fighter_inc, $basic_cost);
		}
	} elseif ($buy == 2) { // Shield Capacity
		if (shipHas($userShip, 'sj')) {
			$error_str .= "Ships with Sub-Space Jump drieves are not allowed to have shields on due to technical problems involving the dynamics of warp-point generation.<p>";
		} else {
			$error_str .= make_basic_upgrade("shield", "max_shields", $shield_inc, $basic_cost);
		}
	} elseif ($buy == 3) { // Cargo capacity
		$error_str .= make_basic_upgrade("cargo bay", "cargo_bays", $cargo_inc, $basic_cost);
	} elseif ($buy == 4) { // shrouder
		if (shipHas($userShip, 'ls')) {
			$error_str .= "This ship already has low stealth. It is not possible to upgrade to high-stealth.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Scanner',$self,"This device will Highly stealth the ship. Enemy players will only see a distortion, and not be able to target it, unless they have a scanner on their ship.<br />If they do have a scanner they will still not be able to see who owns the ship.<p>Are you sure you want to buy a <b class=b1>Shrouding Unit</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("shrouding unit", "hs", $cloak_cost, 2003);
		}
	} elseif ($buy == 5) { // Shield Charger
		if ($userShip['max_shields'] < 1){
			$error_str .= "Why do you want a <b class=b1>Shield Charging Upgrade</b> on a ship that has no shield capacity? <br />I advise you re-think your strategy.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Shield Charging Upgrade',$self,"This upgrade will increase the shield charging rate for this ship.<p>Are you sure you want to buy a <b class=b1>Shield Charging Upgrade</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("shield charging upgrade", "sh", $shield_charger, 2005);
		}
	} elseif ($buy == 6) { // Transverser upgrade (wormhole stabiliser)
		if (!shipHas($userShip, 'sj')){
			$error_str .= "This ship does not have a <b class=b1>SubSpace Jump Drive</b> and so is not capable of using a <b class=b1>Wormhole Stabiliser</b>.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Wormhole Stabiliser',$self,"This upgrade will allow your ship to take more than 10 ships with it when sub-space jumping.<br />It will also allow you to auto-shift materials and colonists between planets.<p>Are you sure you want to buy a <b class=b1>Wormhole Stabiliser</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("wormhole stabiliser", "ws", $stabiliser_upgrade, 2006);
		}
	} elseif ($buy == 7) { // Scanner
		if(!isset($sure)) {
			get_var('Buy Scanner',$self,"Are you sure you want to buy a <b class=b1>Scanner</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("scanner", "sc", $scanner_cost, 2004);
		}
	} elseif ($buy == 8) { //transwarp drive
		if (shipHas($userShip, 'sj')){
			$error_str .= "Your ship has a <b class=b1>SubSpace Jump Drive</b> on, and so can't have a <b class=b1>Transwarp Drive</b>.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Transwarp Drive',$self,"This upgrade will allow your ship (and any ships following it) to jump a limited distance across the universe. Ideal for peninsula hopping, and getting to star-islands.<p>Are you sure you want to buy a <b class=b1>Transwarp Drive</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("transwarp drive", "tw", $transwarp_cost, 2002);
		}
	}
}

if (isset($b_buy) && isset($num_up) && is_numeric($num_up) && $num_up > 0) {
	if ($b_buy == 1 && ($userShip['max_fighters'] + $fighter_inc * $num_up) > 5000 && 
	     !shipHas($userShip, 'bs')) {
		$error_str .= <<<END
<p>It is against regulations to have more than 5000 fighter capacity on a ship 
unless the ship is registered as a battleship.  To do that you will have to 
purchase a battleship upgrade from the auction house.</p>

END;
	} elseif (shipHas($userShip, 'sj') && $b_buy == 2){
		$error_str .= <<<END
<p>It is not possible to fit shield capacity to a ship that has a <em>SubSpace 
Jump Drive</em> on it &#8212; the law's of physics are very uncompromising on 
this point.</p>

END;
	} else {
		switch ($b_buy) {
			case 1:
				$up_str = "fighters";
				$up_sql = "max_fighters";
				$inc_amount = $fighter_inc;
				break;
			case 2:
				$up_str = "shields";
				$up_sql = "max_shields";
				$inc_amount = $shield_inc;
				break;
			case 3:
			default:
				$up_str = "cargo bays";
				$up_sql = "cargo_bays";
				$inc_amount = $cargo_inc;
				break;
		}

		$error_str .= make_basic_upgrade($up_str, $up_sql, $inc_amount, 
		 $basic_cost, $num_up);
	}
}


$error_str = <<<END
<h1>Accessories and Upgrades</h1>$error_str
<p>This ship has <b>$userShip[upgrades]</b> upgrade pod(s) available.
Each upgrade will use one pod.</p>
<p><strong>Warning</strong>: once brought an upgrade cannot be sold!</p>

<h2>Ship Basics</h2>
<table class="simple">
	<tr>
		<th>Name</th>
		<th>Cost</th>
	</tr>
	<tr>
		<td><a href="$self?buy=1">$fighter_inc fighter capacity</a></td>
		<td>$basic_cost</td>
	</tr>
	<tr>
		<td><a href="$self?buy=2">$shield_inc shield capacity</a></td>
		<td>$basic_cost</td>
	</tr>
	<tr>
		<td><a href="$self?buy=3">$cargo_inc cargo capacity</a></td>
		<td>$basic_cost</td>
	</tr>
</table>

<h3>Mass upgrades</h3>
<form method="get" action="$self">
	<p><select name="b_buy">
		<option value="1">$fighter_inc fighter capacity</option>
		<option value="2">$shield_inc shield capacity</option>
		<option value="3">$cargo_inc cargo capacity</option>
	</select>
	<input type="text" size="3" name="num_up" class="text" value="1" /></p>
	<p><input type="submit" value="Upgrade ship" class="button" /></p>
</form>

<h2>Propulsion</h2>
<table class="simple">
	<tr>
		<th>Name</th>
		<th>Notes</th>
		<th>Cost</th>
	</tr>

END;

if (!shipHas($userShip, 'sj')) {
	$error_str .= <<<END
	<tr>
		<td><a href="$self?buy=8">Transwarp drive</a></td>
		<td>Cannot be fitted to a ship with a subspace jump drive</td>
		<td>$transwarp_cost</td>
	</tr>

END;
}
if (shipHas($userShip, 'sj')) {
	$error_str .= <<<END
	<tr>
		<td><a href="$self?buy=6">Wormhole stabiliser</a></td>
		<td>Can only be installed on ships with a subspace jump drive</td>
		<td>$stabiliser_upgrade</td>
	</tr>

END;
}

$error_str .= <<<END
</table>

<h2>Advanced</h2>
<table class="simple">
	<tr>
		<th>Name</th>
		<th>Notes</th>
		<th>Cost</th>
	</tr>
	<tr>
		<td><a href="$self?buy=4">Shrouding unit</a></td>
		<td>Provides high stealth.  Cost based on ship size.</td>
		<td>$cloak_cost</td>
	</tr>
	<tr>
		<td><a href="$self?buy=7">Scanner</a></td>
		<td>Allows detection of cloaked ships</td>
		<td>$scanner_cost</td>
	</tr>
	<tr>
		<td><a href="$self?buy=5">Shield charging upgrade</a></td>
		<td>Increases shield charge rate for the ship</td>
		<td>$shield_charger</td>
	</tr>
</table>

<p><a href="help.php?upgrades=1">Information about accessories and 
upgrades</a></p>

END;

print_page("Accessories and upgrades",$error_str);

?>
