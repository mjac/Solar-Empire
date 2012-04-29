<?php

require_once('inc/user.inc.php');

$filename = "upgrade.php";

sudden_death_check($user);

if($user['location'] != '1') {
	print_page("Error","You are unable to buy Accessories & Upgrades here.");
} elseif($user['ship_id'] == NULL && $user['login_id'] !=1) {
	print_page("Error","You are unable to buy Accessories & Upgrades here, as you do not have a real ship.");
}/* elseif($flag_upgrades && $user[login_id] !=1) {
	print_page("Error","Admin has disabled upgrades for this game.");
}*/

//increases in capacity:
$fighter_inc = 300;
$shield_inc = 100;
$cargo_inc = 100;

//costs
$basic_cost = 5000;		//cost of the 3 basic upgrades.

if($user_ship['size'] < 1){
	$user_ship['size'] = 1;
}

#turret costs - based on size of ship
$pea_turret = round(40000 * ($user_ship['size'] / 100)) * 15;
$defensive_turret = round(45000 * ($user_ship['size'] / 100)) * 15;

#cloak cost also based on size of ship
$cloak_cost = round(40000 * ($user_ship['size'] / 100)) * 15;

$scanner_cost = 20000;
$transwarp_cost = 20000;
$ramjet_cost = 20000;
$shield_charger = 20000;
$stabiliser_upgrade = 65000;

#maximum number of each turret type:
$max_ot = 5;
$max_dt = 5;


$rs = "<p><a href=upgrade.php>Return to Accessories & Upgrades Store</a>";
// checks
if(isset($buy)) {
	if($buy ==1) { //Fighter capacity
		if($user_ship['max_fighters'] + $fighter_inc >= 5000 && !eregi("bs",$user_ship['config'])) {
			$error_str .= "It is against regulations to have more than 4,999 fighter capacity on a ship unless the ship is registered as a battleship.<br>To do that you'll have to purchase a battleship upgrade from Bilkos.<p>";
		} else {
			$error_str .= make_basic_upgrade("Fighter","max_fighters",$fighter_inc,$basic_cost);
		}

	} elseif($buy ==2) { //Shield Capacity
		if (eregi("sj",$user_ship['config'])){
			$error_str .= "Ships with Sub-Space Jump drieves are not allowed to have shields on due to technical problems involving the dynamics of warp-point generation.<p>";
		} else {
			$error_str .= make_basic_upgrade("Shield","max_shields",$shield_inc,$basic_cost);
		}

	} elseif($buy ==3) { //Cargo capacity
		$error_str .= make_basic_upgrade("Cargo Bay","cargo_bays",$cargo_inc,$basic_cost);

	} elseif($buy==4) { //shrouder
		if (eregi("ls",$user_ship['config'])){
			$error_str .= "This ship already has low stealth. It is not possible to upgrade to high-stealth.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Scanner',$filename,"This device will Highly stealth the ship. Enemy players will only see a distortion, and not be able to target it, unless they have a scanner on their ship.<br>If they do have a scanner they will still not be able to see who owns the ship.<p>Are you sure you want to buy a <b class=b1>Shrouding Unit</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Shrouding Unit", "hs", $cloak_cost, 2003);
		}

	} elseif($buy==5) { //Shield Charger
		if ($user_ship['max_shields'] < 1){
			$error_str .= "Why do you want a <b class=b1>Shield Charging Upgrade</b> on a ship that has no shield capacity? <br>I advise you re-think your strategy.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Shield Charging Upgrade',$filename,"This upgrade will increase the shield charging rate for this ship.<p>Are you sure you want to buy a <b class=b1>Shield Charging Upgrade</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Shield Charging Upgrade", "sh", $shield_charger, 2005);
		}

	} elseif($buy==6) { //Transverser upgrade (wormhole stabiliser)
		if (!eregi("sj",$user_ship['config'])){
			$error_str .= "This ship does not have a <b class=b1>SubSpace Jump Drive</b> and so is not capable of using a <b class=b1>Wormhole Stabiliser</b>.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Wormhole Stabiliser',$filename,"This upgrade will allow your ship to take more than 10 ships with it when sub-space jumping.<br>It will also allow you to auto-shift materials and colonists between planets.<p>Are you sure you want to buy a <b class=b1>Wormhole Stabiliser</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Wormhole Stabiliser", "ws", $stabiliser_upgrade, 2006);
		}

	} elseif($buy==7) { //Scanner
		if(!isset($sure)) {
			get_var('Buy Scanner',$filename,"Are you sure you want to buy a <b class=b1>Scanner</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Scanner", "sc", $scanner_cost, 2004);
		}

	} elseif($buy ==8) { //transwarp drive
		if (eregi("sj",$user_ship['config'])){
			$error_str .= "Your ship has a <b class=b1>SubSpace Jump Drive</b> on, and so can't have a <b class=b1>Transwarp Drive</b>.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Transwarp Drive',$filename,"This upgrade will allow your ship (and any ships following it) to jump a limited distance across the universe. Ideal for peninsula hopping, and getting to star-islands.<p>Are you sure you want to buy a <b class=b1>Transwarp Drive</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= make_standard_upgrade ("Transwarp Drive", "tw", $transwarp_cost, 2002);
		}

	} elseif($buy==10) { //Pea Turret
		if($user['cash'] < $pea_turret) {
			$error_str .= "You can not afford to buy a <b class=b1>Pea Shooter</b>.<p>";
		}elseif(!avail_check(2000)){
			$error_str .= "This item has not been developed yet.<p>";
		} elseif ($user_ship['num_ot'] >= $max_ot){
			$error_str .= "Your ship is already equipped with <b>$max_ot</b> <b class=b1>Pea Shooters</b>. <br>The power relays on your ship are unable to cope with any more.<p>";
		} elseif ($user_ship['upgrades'] < 1){
			$error_str .= "This ship does not have any upgrade pods available.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Pea Shooter',$filename,"This upgrade will complement your ships fighters in battle, allowing you to do more damage to the enemy ship(s).<p>Are you sure you want to buy a <b class=b1>Pea Shooter</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= "<b class=b1>Pea Shooter</b>, purchased and installed on the <b class=b1>$user_ship[ship_name]</b> for <b>$pea_turret</b> Credits.<p>";

			take_cash($pea_turret);

			dbn("update ${db_name}_ships set upgrades = upgrades - 1 ,num_ot = num_ot + 1 where ship_id = '$user[ship_id]'");
			$user_ship['upgrades'] --;
			$user_ship['num_ot'] ++;
		}

	} elseif($buy==11) { //Defensive Turret
		if($user['cash'] < $defensive_turret) {
			$error_str .= "You can not afford to buy a <b class=b1>Defensive Turret</b>.<p>";
		}elseif(!avail_check(2001)){
			$error_str .= "This item has not been developed yet.<p>";
		} elseif ($user_ship['num_dt'] >= $max_dt){
			$error_str .= "Your ship is already equipped with <b>$max_dt</b> <b class=b1>Defensive Turrets</b>. <br>The power relays on your ship are unable to cope with any more.<p>";
		} elseif ($user_ship['upgrades'] < 1){
			$error_str .= "This ship does not have any upgrade pods available.<p>";
		} elseif(!isset($sure)) {
			get_var('Buy Defensive Turret',$filename,"This turret will destroy enemy fighters <b class=b1>Before</b> they have a chance to hurt your ship.<p>Are you sure you want to buy a <b class=b1>Defensive Turret</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
		} else {
			$error_str .= "<b class=b1>Defensive Turret</b>, purchased and installed on the <b class=b1>$user_ship[ship_name]</b> for <b>$defensive_turret</b> Credits.<p>";

			take_cash($defensive_turret);

			dbn("update ${db_name}_ships set upgrades = upgrades - 1,num_dt = num_dt + 1 where ship_id = '$user[ship_id]'");
			$user_ship['upgrades'] --;
			$user_ship['num_dt'] ++;
		}
	}
}

if(isset($b_buy)) {
	#ensure users don't enter equations in place of numbers.
	settype($num_up, "integer");

	#user should type something in.
	if($num_up < 1) {
		$error_str .= "Please select a number of upgrades to purchase and fit to the <b>$user_ship[ship_name]</b>.<p>";

	#have some free pods?
	} elseif($num_up > $user_ship['upgrades']) {
		$error_str .= "You do not have that many upgrade pods.<p>";

	#enough money?
	} elseif(($num_up * $basic_cost) > $user['cash']) {
		$error_str .= "You do not have enough money for that many upgrade pods.<p>";

	#user not allowed more than 5k figs unless the ship is a battleship.
	} elseif(($user_ship['max_fighters'] + ($fighter_inc * $num_up) >= 5000) && !ereg("bs",$user_ship['config']) && $b_buy == 1) {
		$error_str .= "It is against regulations to have more than 4,999 fighter capacity on a ship unless the ship is registered as a battleship.<br>To do that you'll have to purchase a battleship upgrade from Bilkos.<p>";

	#not allowed shields on a SJ ship.
	} elseif (ereg("sj",$user_ship['config']) && $b_buy == 2){
		$error_str .= "It is not possible to fit shield capacity to a ship that has a <b class=b1>SubSpace Jump Drive</b> on.<p>The law's of physics are very uncompromising on this point.";

	#confirmation
	#} elseif(!isset($sure)) {
	#	get_var('Buy Multiple Upgrades',$filename,'Are you sure you want to do a Mass Upgrade?','sure','');

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


		$error_str .= "You have increased the <b class=b1>$user_ship[ship_name]'s</b> $up_str capacity by <b>$inc_amount</b> for <b>$cost</b> Credits. <p>";
		take_cash($cost);
		dbn("update ${db_name}_ships set $up_sql = $up_sql + '$inc_amount', upgrades = upgrades - '$num_up' where ship_id = '$user_ship[ship_id]'");
		$user_ship['upgrades'] -= $num_up;
		$user_ship[$up_sql] += $inc_amount;
		if($up_sql == "cargo_bays"){
			$user_ship['empty_bays'] += $inc_amount;
		}
	}
}

#ensure user has some upgrade pods free.
if($user_ship['upgrades'] < 1){
	print_page("Accessories & Upgrades","This Ship has no Upgrade pods available.<p>Our upgrades require special 'pods'. As this ship as no such pods, we cannot do anything to it.");

} else {


	$error_str .= "<br>This ship has <b>$user_ship[upgrades]</b> upgrade Pod(s) available. Each upgrade will use one pod.<br>";
	$error_str .= "<br>Warning! Once brought, an upgrade cannot be sold!<p>";

	if($user_ship['upgrades'] > 1) {
		$error_str .= "<table><tr><td>";
	}

	$error_str .= "Basic Upgrades";
	$error_str .= make_table(array("Item Name","Item Cost"));
	$error_str .= make_row(array("$fighter_inc Fighter Capacity",$basic_cost,"<a href=$filename?buy=1>Buy</a>"));
	$error_str .= make_row(array("$shield_inc Shield Capacity",$basic_cost,"<a href=$filename?buy=2>Buy</a>"));
	$error_str .= make_row(array("$cargo_inc Cargo Capacity",$basic_cost,"<a href=$filename?buy=3>Buy</a>"));
	$error_str .= "</table>";


	if($user_ship['upgrades'] > 1) {
		$error_str .= "</td><td align=right>";
		$error_str .= "<p>Mass upgrades:";
		$error_str .= "<FORM method=get action=upgrade.php>";
		$error_str .= "&nbsp;&nbsp;&nbsp;&nbsp;<select name=b_buy>";
		$error_str .= "<option value=1> + $fighter_inc Fighter Capacity";
		$error_str .= "<option value=2> + $shield_inc Shield Capacity";
		$error_str .= "<option value=3> + $cargo_inc Cargo Capacity";
		$error_str .= "</select>";
		$error_str .= " - <input type='text' size='3' name='num_up'>";
		$error_str .= "<p><INPUT type=submit value=Submit></form><p>";
		$error_str .= "</td></tr></table>";
	}

	$error_str .= "<br>This ship has <b>$upgrade_pods[0]</b> upgrade Pod(s) available. Each upgrade will use one pod.<br>";
	$error_str .= "<br>Warning! Once brought, an upgrade cannot be sold!";

	$error_str .= "Basic Upgrades";
	$error_str .= make_table(array("Item Name","Item Cost"),"75%");
	$error_str .= make_row(array("$fighter_inc Fighter Capacity",$basic_cost,"<a href=$filename?buy=1>Buy</a>"));
	$error_str .=  make_row(array("$shield_inc Shield Capacity",$basic_cost,"<a href=$filename?buy=2>Buy</a>"));
	$error_str .=  make_row(array("$cargo_inc Cargo Capacity",$basic_cost,"<a href=$filename?buy=3>Buy</a>"));

	$error_str .= "</table><br><br>Turrets";
	$error_str .= make_table(array("Item Name","Notes","Item Cost"),"75%");
	$error_str .=  make_row(array("Pea Shooter","Max of $max_ot per ship. Cost based on ship size.",$pea_turret,"<a href=$filename?buy=10>Buy</a>"));
	$error_str .=  make_row(array("Defensive Turret","Max of $max_dt per ship. Cost based on ship size.",$defensive_turret,"<a href=$filename?buy=11>Buy</a>"));

	$error_str .= "</table><br><br>Propulsion Upgrades";
	$error_str .= make_table(array("Item Name","Notes","Item Cost"),"75%");
	$error_str .=  make_row(array("Transwarp Drive","Cannot be fitted to a ship with a Subspace Jump Drive",$transwarp_cost,"<a href=$filename?buy=8>Buy</a>"));
	if (eregi("sj",$user_ship[config])){
		$error_str .=  make_row(array("WormHole Stabiliser","Can only be installed on ships with a Subspace Jump Drive.",$stabiliser_upgrade,"<a href=$filename?buy=6>Buy</a>"));
	}

	$error_str .= "</table><br><br>Misc";
	$error_str .= make_table(array("Item Name","Notes","Item Cost"),"75%");
	$error_str .=  make_row(array("Shrouding Unit","Provides High Stealth. Cost based on ship size.",$cloak_cost,"<a href=$filename?buy=4>Buy</a>"));
	$error_str .=  make_row(array("Scanner","Allows Detection of Cloaked Ships",$scanner_cost,"<a href=$filename?buy=7>Buy</a>"));
	$error_str .=  make_row(array("Shield Charging Upgrade","Increases Shield Charge Rate for the ship.",$shield_charger,"<a href=$filename?buy=5>Buy</a>"));

	$error_str .= "</table>";
	$error_str .= "<p><a href=help.php?upgrades=1 target=_blank>Information about Accessories & Upgrades</a>";

	$rs = "<p><a href=earth.php>Return to Earth</a>";

	print_page("Accessories & Upgrades",$error_str);

}

#function for adding 'normal' upgrades to a ship.
function make_basic_upgrade ($upgrade_str, $upgrade_sql, $inc_amount, $cost){
	global $user, $user_ship, $db_name;
	if($user['cash'] < $cost) {
		return "You can not afford any of the Basic Upgrades.<p>";
	} elseif($user_ship['upgrades'] < 1) {
		return "";
	} else {
		take_cash($cost);
		dbn("update ${db_name}_ships set $upgrade_sql = $upgrade_sql + '$inc_amount', upgrades = upgrades - 1 where ship_id = '$user_ship[ship_id]'");
		$user_ship['upgrades'] --;
		$user_ship[$upgrade_sql] += $inc_amount;

		if($upgrade_sql == "cargo_bays"){
			$user_ship['empty_bays'] += $cargo_inc;
		}

		return "You have increased the <b class=b1>$user_ship[ship_name]s</b> $upgrade_str capacity by <b>$inc_amount</b> for <b>$cost</b> Credits. <p>";
	}
}

?>
