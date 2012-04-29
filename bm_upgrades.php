<?php

require_once('inc/user.inc.php');

$filename = "bm_upgrades.php";

sudden_death_check($user);


$rs = "";
if($from_0 == 1){
 	$rs = "<p><a href=black_market.php?bmrkt_id=$bmrkt_id>Return to Blackmarket</a>";
}
$rs .= "<p><a href=location.php>Close Contact</a>";


db("select * from ${db_name}_bmrkt where location = '$user[location]' && (bmrkt_type = 2 || bmrkt_type = 0) && bmrkt_id = '$bmrkt_id'");
$bmrkt = dbr();

if (!$bmrkt) {
	print_page("Blackmarket","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'");
} elseif($user[ship_id] ==1 && $user[login_id] !=1) {
	print_page("Error","The local Pirates who operate this service have refused you entry. How can you be a Captain with no ship!!!");
} elseif($flag_research !=1) {
	print_page("Error","Admin in his/her near infinite wisdom has disabled the Blackmarket");
}

db("select config from ${db_name}_ships where ship_id = $user[ship_id]");
$old_config = dbr();

// Tech and Credit costs of Advanced Upgrades

// Credit Cost
#turret costs - based on size of ship
$plasma_cannon_c = round(55000 * ($user_ship[size] / 100)) * 15;
$silicon_armour_c = round(65000 * ($user_ship[size] / 100)) * 15;

$electronic_warfare_c = 60000;
$genesis_c = 1000000;
$terra_i_c = 250000;

// Support Unit Cost
#turret costs based on size of ship
$plasma_cannon_t = round(700 * ($user_ship[size] / 100)) * 5;
$silicon_armour_t = round(800 * ($user_ship[size] / 100)) * 5;
$electronic_warfare_t = 300;
$genesis_t = 0;
$terra_i_t = 500;

#maximum amount of each weapon allowed on the ship.
$max_sa = 5;
$max_pc = 5;
$max_ew = 5;


$old_config = $user_ship[config];


db("select upgrades from ${db_name}_ships where ship_id = $user[ship_id]");
$upgrade_pods = dbr();

// value of $buy determines upgrade to be purchased
// new if statement to prevent multiple occurance of config abbrs
// new addition of num_ot,num_dt, etc to allow multiple upgrades of same type

if($buy ==3) { //Plasma Cannon
	if($user[cash] < $plasma_cannon_c) {
		$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
	} elseif($user[tech] < $plasma_cannon_t) {
		$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units. You must do more research!";
	} elseif($upgrade_pods[0] < 1) {
		$error_str .= "What sort of a Captain are you? Your blasted ship has no Upgrade Pods!<p>";
	} elseif ($user_ship[num_pc] >= $max_pc){
		$error_str .= "Your ship already has <b>$max_pc</b> <b class=b1>Plasma Cannons</b>. <br>Until my fellow Pirates learn more from raiding these cursed Aliens, I can fit no more.<p>";
	} elseif($sure != 'yes') {
		get_var('Purchase Plasma Cannon',$filename,"Are you sure you want to buy a <b class=b1>Plasma Cannon</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
	} else {
		$error_str .= "<b class=b1>Plasma Cannon</b>, purchased and installed on the <b class=b1>$user_ship[ship_name]</b> for <b>$plasma_cannon_c</b> Credits and <b>$plasma_cannon_t</b> Tech. Support Units.<p>";
		take_cash($plasma_cannon_c);
		take_tech($plasma_cannon_t);

		if (!eregi("pc",$old_config)){
			$user_ship[config] = $old_config.":pc";
		}

		dbn("update ${db_name}_ships set config = '$user_ship[config] ',upgrades = upgrades - 1,num_pc = num_pc + 1 where ship_id = '$user[ship_id]'");
		$upgrade_pods[0] = $upgrade_pods[0] - 1;
		$user_ship[num_pc]++;
	}
} elseif($buy ==4) { //Silicon Armour Module
	if($user[cash] < $silicon_armour_c) {
		$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
	} elseif($user[tech] < $silicon_armour_t) {
		$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
	} elseif($upgrade_pods[0] < 1) {
		$error_str .= "What sort of a Captain are you? Your blasted ship has no Upgrade Pods!<p>";
	} elseif ($user_ship[num_sa] >= $max_sa){
		$error_str .= "Your ship already has <b>$max_pc</b> <b class=b1>Silicon Armour</b> Modules. <br>Until my fellow Pirates learn more from raiding these cursed Aliens, I can fit no more.<p>";
	} elseif($sure != 'yes') {
		get_var('Purchase Silicon Armour Module',$filename,"Are you sure you want to buy a <b class=b1>Silicon Armour Module</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
	} else {
		$error_str .= "<b class=b1>Silicon Armour Module</b>, purchased and installed on the <b class=b1>$user_ship[ship_name]</b> for <b>$silicon_armour_c</b> Credits and <b>$silicon_armour_t</b> Tech. Support Units.<p>";

		take_cash($silicon_armour_c);
		take_tech($silicon_armour_t);

		if (!eregi("sa",$old_config)){
			$user_ship[config] = $old_config.":sa";
		}

		dbn("update ${db_name}_ships set config = '$user_ship[config] ',upgrades = upgrades - 1, num_sa = num_sa + 1 where ship_id = '$user[ship_id]'");
		$upgrade_pods[0] = $upgrade_pods[0] - 1;
		$user_ship[num_sa]++;
	}

} elseif($buy ==5) { //Electronic Warfare Pod
	if($user[cash] < $electronic_warfare_c) {
		$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
	} elseif($user[tech] < $electronic_warfare_t) {
		$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
	} elseif($upgrade_pods[0] < 1) {
		$error_str .= "What sort of a Captain are you? Your blasted ship has no Upgrade Pods!<p>";
	} elseif ($numew[0] >= 10){
		$error_str .= "Your ship already has a <b class=b1>Electronic Warfare Pod</b>. <br>Until my fellow Pirates learn more from raiding these cursed Aliens, I can fit no more.<p>";
	} elseif($sure != 'yes') {
		get_var('Purchase Electronic Warfare Pod',$filename,"Are you sure you want to buy a <b class=b1>Electronic Warfare Pod</b>, for the <b class=b1>$user_ship[ship_name]</b>?",'sure','');
	} else {
		$error_str .= "<b class=b1>Electronic Warfare Pod</b>, purchased and installed on the <b class=b1>$user_ship[ship_name]</b> for <b>$electronic_warfare_c</b> Credits and <b>$electronic_warfare_t</b> Tech. Support Units.<p>";

		take_cash($electronic_warfare_c);
		take_tech($electronic_warfare_t);

		if (eregi("ew",$old_config)){
			$user_ship[config] = $old_config.":ew";
		}
		dbn("update ${db_name}_ships set config = '$user_ship[config] ',upgrades = upgrades - 1, num_ew = num_ew + 1 where ship_id = '$user[ship_id]'");
		$upgrade_pods[0] = $upgrade_pods[0] - 1;
		$user_ship[num_ew]++;

	}

} elseif($buy ==7 && $uv_planets >= 0) { //Genesis Device
	if($user[cash] < $genesis_c) {
		$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
	} elseif($user[tech] < $genesis_t) {
		$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
	} elseif($sure != 'yes') {
		get_var('Purchase Genesis Device',$filename,"Are you sure you want to buy a <b cldass=b1>Genesis Device</b>?<br><br>",'sure','');
	} else{
		$error_str .= "<b class=b1>Gensis Device</b> purchased for <b>$genesis_c</b> Credits and <b>$genesis_t</b> Tech. Support Units.<p>";
		take_cash($genesis_c);
		take_tech($genesis_t);
		$user[genesis]++;
		dbn("update ${db_name}_users set genesis = genesis + 1 where login_id = '$user[login_id]'");
	}
} elseif($buy ==8) { //Terra Imploder
	if($user[cash] < $terra_i_c) {
		$error_str .= "Shiver me hull plates! You don't have enough Credits.<p>";
	} elseif($user[tech] < $terra_i_t) {
		$error_str .= "Ignorant planet-dweller! You don't have enough Tech. Support Units.";
	} elseif($sure != 'yes') {
		get_var('Purchase Terra Imploder',$filename,"Are you sure you want to buy a <b cldass=b1>Terra Imploder</b>?<br><br>",'sure','');
	} else {
		$error_str .= "<b class=b1>Terra Imploder</b> purchased for <b>$terra_i_c</b> Credits and <b>$terra_i_t</b> Tech. Support Units.<p>";
		take_cash($terra_i_c);
		take_tech($terra_i_t);
		$user[terra_imploder]++;
		dbn("update ${db_name}_users set terra_imploder = terra_imploder + 1 where login_id = '$user[login_id]'");
	}
}




$error_str .= "<br>This ship has <b>$upgrade_pods[0]</b> upgrade Pod(s) available. Each upgrade will use one pod.<br>";
$error_str .= "<br>Listen up, Captain! Once brought, an <b>Advanced Upgrade</b> cannot be sold on. <br>This is a Blackmarket. You do any arbitrage and I'll make you walk the plank! Got me a nice one hidden in a cargo bay on Sol Star Port.<br>";

$error_str .= "<br>Attack/Defence Articles";
$error_str .= make_table(array("Item Name","Notes","Credits Cost","Tech Cost"),"75%");
$error_str .= make_row(array("Plasma Cannon","Max of $max_pc per ship. Cost based on ship size.",$plasma_cannon_c,$plasma_cannon_t,"<a href=$filename?buy=3&bmrkt_id=$bmrkt_id>Buy</a>"));
$error_str .= make_row(array("Silicon Armour Module","Max of $max_pc per ship.  Cost based on ship size.",$silicon_armour_c,$silicon_armour_t,"<a href=$filename?buy=4&bmrkt_id=$bmrkt_id>Buy</a>"));
$error_str .= make_row(array("Electronic Warfare Pod","Max of $max_pc per ship.",$electronic_warfare_c,$electronic_warfare_t,"<a href=$filename?buy=5&bmrkt_id=$bmrkt_id>Buy</a>"));

$error_str .= "</table><br><br>Misc";
$error_str .= make_table(array("Item Name","Notes","Credits Cost","Tech Cost"),"75%");
if($uv_planets >= 0){
	$error_str .= make_row(array("Gensis Device","Creates planets.",$genesis_c,$genesis_t,"<a href=$filename?buy=7&bmrkt_id=$bmrkt_id>Buy</a>"));
	$error_str .= make_row(array("Terra Imploder","Allows the destruction of a planet.",$terra_i_c,$terra_i_t,"<a href=$filename?buy=8&bmrkt_id=$bmrkt_id>Buy</a>"));
}
$error_str .= "</table>";

$error_str .= "<p><a href=help.php?upgrades=1 target=_blank>Information about Accessories & Upgrades</a>";

print_page("Blackmarket Upgrades",$error_str);
?>