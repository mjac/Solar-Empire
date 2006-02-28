<?php

require_once('inc/user.inc.php');

if (deathCheck($user) || $userShip === NULL || $userShip['location'] != 1) {
	print_page("Error", "You are unable to buy upgrades here.");
}

//increases in capacity:
$fighter_inc = 300;
$shield_inc = 100;
$cargo_inc = 100;

//costs
$basic_cost = 5000;		//cost of the 3 basic upgrades.

#cloak cost also based on size of ship
$cloak_cost = ceil(sqrt($userShip['hull'])) * 1000;

$scanner_cost = 20000;
$transwarp_cost = 20000;
$shield_charger = 20000;
$stabiliser_upgrade = 65000;

$error_str = '';


// checks
if(isset($buy)) {
	if ($buy == 1) { //Fighter capacity
		if($userShip['max_fighters'] + $fighter_inc >= 5000 && !shipHas($userShip, 'bs')) {
			$error_str .= "It is against regulations to have more than 4,999 fighter capacity on a ship unless the ship is registered as a battleship.<br />To do that you'll have to purchase a battleship upgrade from the auction house.<p>";
		} else {
			$error_str .= make_basic_upgrade("Fighter","max_fighters",$fighter_inc,$basic_cost);
		}

	} elseif ($buy == 2) { //Shield Capacity
		if (shipHas($userShip, 'sj')) {
			$error_str .= "Ships with Sub-Space Jump drieves are not allowed to have shields on due to technical problems involving the dynamics of warp-point generation.<p>";
		} else {
			$error_str .= make_basic_upgrade("Shield","max_shields",$shield_inc,$basic_cost);
		}

	} elseif ($buy == 3) { //Cargo capacity
		$error_str .= make_basic_upgrade("Cargo Bay","cargo_bays",$cargo_inc,$basic_cost);

	} elseif ($buy == 4) { //shrouder
		if (shipHas($userShip, 'ls')) {
			$error_str .= "This ship already has low stealth. It is not possible to upgrade to high-stealth.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Scanner',$self,"This device will Highly stealth the ship. Enemy players will only see a distortion, and not be able to target it, unless they have a scanner on their ship.<br />If they do have a scanner they will still not be able to see who owns the ship.<p>Are you sure you want to buy a <b class=b1>Shrouding Unit</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Shrouding Unit", "hs", $cloak_cost, 2003);
		}

	} elseif ($buy == 5) { //Shield Charger
		if ($userShip['max_shields'] < 1){
			$error_str .= "Why do you want a <b class=b1>Shield Charging Upgrade</b> on a ship that has no shield capacity? <br />I advise you re-think your strategy.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Shield Charging Upgrade',$self,"This upgrade will increase the shield charging rate for this ship.<p>Are you sure you want to buy a <b class=b1>Shield Charging Upgrade</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Shield Charging Upgrade", "sh", $shield_charger, 2005);
		}

	} elseif ($buy == 6) { //Transverser upgrade (wormhole stabiliser)
		if (!shipHas($userShip, 'sj')){
			$error_str .= "This ship does not have a <b class=b1>SubSpace Jump Drive</b> and so is not capable of using a <b class=b1>Wormhole Stabiliser</b>.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Wormhole Stabiliser',$self,"This upgrade will allow your ship to take more than 10 ships with it when sub-space jumping.<br />It will also allow you to auto-shift materials and colonists between planets.<p>Are you sure you want to buy a <b class=b1>Wormhole Stabiliser</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Wormhole Stabiliser", "ws", $stabiliser_upgrade, 2006);
		}

	} elseif($buy==7) { //Scanner
		if(!isset($sure)) {
			get_var('Buy Scanner',$self,"Are you sure you want to buy a <b class=b1>Scanner</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("Scanner", "sc", $scanner_cost, 2004);
		}

	} elseif ($buy == 8) { //transwarp drive
		if (shipHas($userShip, 'sj')){
			$error_str .= "Your ship has a <b class=b1>SubSpace Jump Drive</b> on, and so can't have a <b class=b1>Transwarp Drive</b>.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Transwarp Drive',$self,"This upgrade will allow your ship (and any ships following it) to jump a limited distance across the universe. Ideal for peninsula hopping, and getting to star-islands.<p>Are you sure you want to buy a <b class=b1>Transwarp Drive</b>, for the <b class=b1>$userShip[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade("Transwarp Drive", "tw", $transwarp_cost, 2002);
		}
	}
}

if(isset($b_buy)) {
	#ensure users don't enter equations in place of numbers.
	settype($num_up, "integer");

	if($num_up < 1) {
		$error_str .= "Please select a number of upgrades to purchase and fit to the <b>$userShip[ship_name]</b>.<p>";
	} elseif($num_up > $userShip['upgrades']) {
		$error_str .= "You do not have that many upgrade pods.<p>";
	} elseif(($num_up * $basic_cost) > $user['cash']) {
		$error_str .= "You do not have enough money for that many upgrade pods.<p>";
	} elseif(($userShip['max_fighters'] + ($fighter_inc * $num_up) >= 5000) && !shipHas($userShip, 'bs') && $b_buy == 1) {
		$error_str .= "It is against regulations to have more than 4,999 fighter capacity on a ship unless the ship is registered as a battleship.<br />To do that you'll have to purchase a battleship upgrade from the auction house.<p>";
	} elseif (shipHas($userShip, 'sj') && $b_buy == 2){
		$error_str .= "It is not possible to fit shield capacity to a ship that has a <b class=b1>SubSpace Jump Drive</b> on.<p>The law's of physics are very uncompromising on this point.";
	} else {

		if($b_buy == 1){
			$up_str = "Fighters";
			$up_sql = "max_fighters";
			$inc_amount = $fighter_inc;

		} elseif($b_buy == 2){
			$up_str = "Shields";
			$up_sql = "max_shields";
			$inc_amount = $shield_inc;

		} else{
			$up_str = "Cargo Bays";
			$up_sql = "cargo_bays";
			$inc_amount = $cargo_inc;
		}
		$cost = $num_up * $basic_cost;
		$inc_amount *= $num_up;


		$error_str .= "You have increased the <b class=b1>$userShip[ship_name]'s</b> $up_str capacity by <b>$inc_amount</b> for <b>$cost</b> Credits. <p>";
		giveMoneyPlayer(-$cost);
		dbn("update [game]_ships set $up_sql = $up_sql + '$inc_amount', upgrades = upgrades - '$num_up' where ship_id = '$userShip[ship_id]'");
		$userShip['upgrades'] -= $num_up;
		$userShip[$up_sql] += $inc_amount;
		if($up_sql == "cargo_bays"){
			$userShip['empty_bays'] += $inc_amount;
		}
	}
}

#ensure user has some upgrade pods free.
if($userShip['upgrades'] < 1){
	print_page("Accessories & Upgrades","This Ship has no Upgrade pods available.<p>Our upgrades require special 'pods'. As this ship as no such pods, we cannot do anything to it.");

} else {


	$error_str = <<<END
<h1>Accessories and Upgrades</h1>$error_str
<p>This ship has <b>$userShip[upgrades]</b> upgrade Pod(s) available.
Each upgrade will use one pod.</p>
<p>Warning! Once brought, an upgrade cannot be sold!</p>

END;

	$error_str .= "<h2>Ship Basics</h2>";
	if($userShip['upgrades'] > 1) {
		$error_str .= "<table><tr><td>";
	}

	$error_str .= make_table(array("Item Name","Item Cost"));
	$error_str .= make_row(array("$fighter_inc Fighter Capacity",$basic_cost,"<a href=$self?buy=1>Buy</a>"));
	$error_str .= make_row(array("$shield_inc Shield Capacity",$basic_cost,"<a href=$self?buy=2>Buy</a>"));
	$error_str .= make_row(array("$cargo_inc Cargo Capacity",$basic_cost,"<a href=$self?buy=3>Buy</a>"));
	$error_str .= "</table>";


	if($userShip['upgrades'] > 1) {
		$error_str .= "</td><td align=right>";
		$error_str .= "<p>Mass upgrades:";
		$error_str .= "<FORM method=get action=shop_upgrades.php>";
		$error_str .= "&nbsp;&nbsp;&nbsp;&nbsp;<select name=b_buy>";
		$error_str .= "<option value=1> + $fighter_inc Fighter Capacity";
		$error_str .= "<option value=2> + $shield_inc Shield Capacity";
		$error_str .= "<option value=3> + $cargo_inc Cargo Capacity";
		$error_str .= "</select>";
		$error_str .= " - <input type='text' size='3' name='num_up'>";
		$error_str .= "<p><INPUT type=submit value=Submit></form><p>";
		$error_str .= "</td></tr></table>";
	}

	$error_str .= "</table><h2>Propulsion</h2>";
	$error_str .= make_table(array("Item Name","Notes","Item Cost"),"75%");
	if (!shipHas($userShip, 'sj')) {
		$error_str .=  make_row(array("Transwarp Drive","Cannot be fitted to a ship with a Subspace Jump Drive",$transwarp_cost,"<a href=$self?buy=8>Buy</a>"));
	}
	if (shipHas($userShip, 'sj')) {
		$error_str .=  make_row(array("WormHole Stabiliser","Can only be installed on ships with a Subspace Jump Drive.",$stabiliser_upgrade,"<a href=$self?buy=6>Buy</a>"));
	}

	$error_str .= "</table><h2>Advanced</h2>";
	$error_str .= make_table(array("Item Name","Notes","Item Cost"),"75%");
	$error_str .= make_row(array("Shrouding Unit","Provides High Stealth. Cost based on ship size.",$cloak_cost,"<a href=$self?buy=4>Buy</a>"));
	$error_str .= make_row(array("Scanner","Allows Detection of Cloaked Ships",$scanner_cost,"<a href=$self?buy=7>Buy</a>"));
	$error_str .= make_row(array("Shield Charging Upgrade","Increases Shield Charge Rate for the ship.",$shield_charger,"<a href=$self?buy=5>Buy</a>"));

	$error_str .= "</table>";
	$error_str .= "<p><a href=help.php?upgrades=1 target=_blank>Information about Accessories & Upgrades</a>";

	print_page("Accessories & Upgrades",$error_str);

}

#function for adding 'normal' upgrades to a ship.
function make_basic_upgrade($upgrade_str, $upgrade_sql, $inc_amount, $cost)
{
	global $user, $userShip;
	if($userShip['upgrades'] < 1) {
		return '<p>You do not have enough upgrade pods</p>';
	} elseif (!giveMoneyPlayer(-$cost)) {
		return "<p>You can not afford any of the Basic Upgrades.</p>";
	} else {
		dbn("update [game]_ships set $upgrade_sql = $upgrade_sql + '$inc_amount', upgrades = upgrades - 1 where ship_id = '$userShip[ship_id]'");
		--$userShip['upgrades'];
		$userShip[$upgrade_sql] += $inc_amount;

		checkShip();

		return "You have increased the <b class=b1>$userShip[ship_name]s</b> $upgrade_str capacity by <b>$inc_amount</b> for <b>$cost</b> Credits. <p>";
	}
}

?>
