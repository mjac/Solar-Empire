<?php

require_once('inc/user.inc.php');

$filename = "black_market.php";

sudden_death_check($user);

$error_str = "";

db("select * from ${db_name}_bmrkt where location = '$user[location]' order by bmrkt_type asc");
$bmrkt = dbr();

if (!isset($bmrkt)) {
	print_page("Port","You may not contact a blackmarket that is not in the same system as you are in. Stop playing with the URL's'","?research=1");
} elseif($flag_research == 0 && $user['login_id'] !=1) {
	print_page("Error","Admin has disabled the blackmarkets for the duration.","?research=1");
}


$error_str .= "You've reached <b class=b1>$bmrkt[bm_name]'s</b> Blackmarket";

$error_str .= ".<br>We have all the gadgets you could possibly want. From Starships, to surveillence technology - All strictly illegal, of course.<br>";

$error_str .= "<br><a href=bm_ships.php?from_0=1>Advanced Ships and Alien Starships</a>";
$error_str .= "<br><a href=bm_upgrades.php?from_0=1>Blackmarket Upgrades and Alien Devices</a>";

$rs = "<p><a href=location.php>Close Contact</a><br>";

print_page("Blackmarket",$error_str);


?>
