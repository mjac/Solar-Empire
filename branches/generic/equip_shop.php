<?php

require_once('inc/user.inc.php');

$filename = "equip_shop.php";


if($user[location] != '1') {
	print_page("Error","You are unable to buy equipment here.");
}

sudden_death_check($user);

$error_str = "";

if($alternate_play_1 == 1){
	$ship_stats_1 = load_ship_types();
	$ship_stats = $ship_stats_1[$user_ship[shipclass]];
	$mining_switch_cost = round($ship_stats[cost]/10);
}

#fighter cost is now based upon the admin var.
#$fighter_cost = 100;
$fighter_cost = $fighter_cost_earth;
if($fighter_cost <= 0){
	$fighter_cost = 1;
}

$shield_cost = 50;
$genesis_cost = $cost_genesis_device;
$bomb_cost = $cost_bomb;
$sn_cost = $bomb_cost * 5;

$rs = "<p><a href=equip_shop.php>Return to Equipment Shop</a>";

settype($amount, 'int');
$amount = round($amount);

//db ("select value from ${db_name}_db_vars where name = 'flag_bomb'");
//$varsforgame = dbr();

if($switch == 1){#someone is switching mining types.
	if($alternate_play_1 != 1){#check to see if alternate style of play is in effect.
		$error_str .= "That modification is not available with this style of play.<p>";
	} elseif($user[cash] < $mining_switch_cost){
		$error_str .= "You cannot afford to switch mining types. The cost is generally 10% of the original price of the ship.<p>";
	} elseif($user_ship[mine_rate_metal] < 1 && $user_ship[mine_rate_fuel] < 1) {
		$error_str .= "This ship has no mining capability.<br>This modification simply switches the mining rates around, it does not add mining capability.";
	}elseif($sure != 'yes') {
		get_var('Switch Mining',$filename,"Are you sure you want to switch mining rates on this ship? At present this ship has a metal mining rate of: <b>$user_ship[mine_rate_metal]</b> and a fuel mining rate of: <b>$user_ship[mine_rate_fuel]</b>.<br><br>For the cost of <b>$mining_switch_cost</b> credits, you can make the metal rate: <b>$user_ship[mine_rate_fuel]</b>, and the fuel rate: <b>$user_ship[mine_rate_metal]</b>.<br><br>This can be reversed at any time by purchasing another mining switch.<br>This will <b class=b1>NOT</b> use an upgrade slot.",'sure','');
	} else {
		take_cash($mining_switch_cost);
		dbn("update ${db_name}_ships set mine_rate_fuel = $user_ship[mine_rate_metal], mine_rate_metal = $user_ship[mine_rate_fuel] where ship_id = '$user_ship[ship_id]'");
		$temp4854 = $user_ship[mine_rate_metal];
		$user_ship[mine_rate_metal] = $user_ship[mine_rate_fuel];
		$user_ship[mine_rate_fuel] = $temp4854;
		$error_str .= "Mining Rates switched for <b>$mining_switch_cost</b> Credits.<p>";
	}
} elseif($mass_switch){ #switch the fleet
	db("select sum(s.cost / 10)as cost, count(ship_id) as num from ${db_name}_ship_types s, ${db_name}_ships us where s.type_id = us.shipclass && location='$user[location]' && us.login_id='$user[login_id]' && ship_id != 1 && (s.mine_rate_fuel > 1 || s.mine_rate_metal > 1) group by us.login_id");
	$total_cost = dbr();
	$total_cost[cost] = round($total_cost[cost]);
	if($alternate_play_1 != 1){
		$error_str .= "That modification is not available with this style of play.<p>";
	} elseif($user[cash] < $total_cost[cost]){
		$error_str .= "You do not have enough cash to switch the <b>$total_cost[num]</b> ships that can be switched in this system. <br>The cost would be <b>$total_cost[cost]</b><p>";
	} elseif($total_cost[num] < 1){
		$error_str .= "You do not have any ships in this system that can be switched. Switching simply switches the mining rates around, but none of your ships in this system have any mining capabilities.<p>";
	}elseif($sure != 'yes') {
		get_var('Switch Mining',$filename,"Are you sure you want to switch mining rates on <b>$total_cost[num]</b> ships in this system? The price will be <b>$total_cost[cost]</b> Credits<br><br>This can be reversed at any time by purchasing another mining switch.<br>This will <b class=b1>NOT</b> use any upgrade slots.",'sure','');
	} else {
		take_cash($total_cost[cost]);
		db("select ship_id,mine_rate_fuel,mine_rate_metal from ${db_name}_ships where location='$user[location]' && login_id='$user[login_id]' && ship_id != 1 && (mine_rate_fuel > 1 || mine_rate_metal > 1)");
		while($results=dbr()){
			dbn("update ${db_name}_ships set mine_rate_fuel = '$results[mine_rate_metal]', mine_rate_metal = '$results[mine_rate_fuel]' where ship_id = $results[ship_id]");
		}

#		dbn("update ${db_name}_ships set mine_rate_fuel = mine_rate_metal, mine_rate_metal = mine_rate_fuel where location='$user[location]' && login_id='$user[login_id]' && ship_id != 1 && (mine_rate_fuel > 1 || mine_rate_metal > 1)");

		$temp4854 = $user_ship[mine_rate_metal];
		$user_ship[mine_rate_metal] = $user_ship[mine_rate_fuel];
		$user_ship[mine_rate_fuel] = $temp4854;
		$error_str .= "Mining Rates for fleet switched, at a cost of <b>$total_cost[cost]</b> Credits. Total of <b>$total_cost[num]</b> ships affected.<p>";
	}

}


//function that allows for quick and simple purchase of basic items.
function buy_basic ($item_sql, $item_max_sql, $item_str, $cost){
	global $amount, $user, $user_ship, $db_name;
	settype($amount, "int"); //security check

	$ret_str = "";

	if($user_ship[$item_sql] >= $user_ship[$item_max_sql]){
		$ret_str .= "Your ship is already full of <b class=b1>$item_str</b>.";

	} elseif($amount < 1) {
		$amount_can_buy = floor($user['cash'] / $cost);
		if($amount_can_buy > $user_ship[$item_max_sql] - $user_ship[$item_sql]) {
			$amount_can_buy = $user_ship[$item_max_sql] - $user_ship[$item_sql];
		}

		get_var("Buy $item_str",'equip_shop.php',"How many <b class=b1>$item_str</b> do you want to buy?",'amount',$amount_can_buy);

	} else {
		$total_cost = $amount * $cost;
		if($user['cash'] < $total_cost) {
			$ret_str .= "You can not afford that many <b class=b1>$item_str</b>.<p>";
		} elseif($user_ship[$item_sql] + $amount > $user_ship[$item_max_sql]) {
			$ret_str .= "Your ship can't hold that many more <b class=b1>$item_str</b>.<p>";
		} else {
			$ret_str .= "<b>$amount</b> <b class=b1>$item_str</b> purchased for <b>$total_cost</b> Credits.<p>";
			take_cash($total_cost);

			dbn("update ${db_name}_ships set $item_sql = $item_sql + '$amount' where ship_id = '$user_ship[ship_id]'");

			$user_ship[$item_sql] += $amount;
		}
	}
	return $ret_str;
}


// checks
if(isset($buy)) {
	if($buy == 1) { //fighters
		$error_str .= buy_basic("fighters", "max_fighters", "Fighters", $fighter_cost);

	} elseif($buy == 2) { //shields
		$error_str .= buy_basic("shields", "max_shields", "Shields", $shield_cost);

	} elseif($buy == 5) { // genesis device
		if($uv_planets >= 0 && $user[login_id] != 1){
			$error_str .= "The admin has set it so as genesis devices are un-available.";
		} elseif($sure != 'yes') {
			get_var('Buy Genesis Device',$filename,'Are you sure you want to buy a genesis device?','sure','');
		} else {
			if($user[cash] < $genesis_cost) {
				$error_str .= "You can not afford a genesis device.<p>";
			} else {
				$error_str .= "Genesis device purchased for <b>$genesis_cost</b> Credits.<p>";
				take_cash($genesis_cost);
				dbn("update ${db_name}_users set genesis = genesis + 1 where login_id = $user[login_id]");
			}
		}
	} elseif($buy == 6) { // gamma bomb
		if($sure != 'yes') {
			get_var('Buy Gamma Bomb',$filename,'Are you sure you want to buy a gamma bomb?','sure','');
		} else {
			if($user[cash] < $bomb_cost) {
				$error_str .= "You can not afford a gamma bomb.<p>";
			} elseif ((!$flag_bomb) || ($user[login_id] ==1)) {
				$error_str .= "Gamma bomb purchased for <b>$bomb_cost</b> Credits.<p>";
		take_cash($bomb_cost);
	dbn("update ${db_name}_users set gamma = gamma + 1 where login_id = $user[login_id]");
		} else {
			$error_str .= "Admin has disabled the purchasing of Bombs. However if you have one you can still use it.<p>";
			}
		}
	} elseif($buy == 7) { // alpha bomb
		if($sure != 'yes') {
			get_var('Buy Alpha Bomb',$filename,'Are you sure you want to buy a Alpha bomb?','sure','');
		} else {
			if($user[cash] < $bomb_cost) {
				$error_str .= "You can not afford a Alpha bomb.<p>";
			} elseif ((!$flag_bomb) || ($user[login_id] ==1)) {
				$error_str .= "Alpha bomb purchased for <b>$bomb_cost</b> Credits.<p>";
		take_cash($bomb_cost);
	dbn("update ${db_name}_users set alpha = alpha + 1 where login_id = $user[login_id]");
		} else {
			$error_str .= "Admin has disabled the purchasing of Bombs. However if you have one you can still use it.<p>";
			}
		}
	} elseif($buy == 9) { // SuperNova Effector
	if($user[sn_effect] != 0) {
			$error_str .= "You may only own one SuperNova Effector at a time.<p>";
	} elseif($random_events < 3) {
	$error_str .= "It is not possible to purchase or use SuperNova Effectors when the Admin Variable for random events is set to less than <b>3</b>. <br>At present it is set to <b class=b1>$random_events</b>";
	} elseif($user[cash] < $sn_cost) {
			 $error_str .= "You can not afford a SuperNova Effector.<p>";
	} elseif (($flag_bomb) && ($user[login_id] !=1)) {
		 $error_str .= "Admin has disabled the purchasing of Bombs. However if you have one you can still use it.<p>";
	} elseif($sure != 'yes') {
			get_var('Buy SuperNova Effector',$filename,'Are you sure you want to buy a SuperNova Effector?','sure','');
	} else {
				$error_str .= "SuperNova Effector purchased for <b>$sn_cost</b> Credits.<p>";
		take_cash($sn_cost);
		dbn("update ${db_name}_users set sn_effect = 1 where login_id = '$user[login_id]'");
		}
	} elseif($buy == 10){
	$taken = 0; //Fighters taken from planet so far.
	$ship_counter = 0;
	db("select sum(max_fighters-fighters), count(ship_id) from ${db_name}_ships where location=1 && login_id='$user[login_id]' && max_fighters > 0 && fighters < max_fighters");
	$maths=dbr();
		if($user[cash] < $fighter_cost){
		print_page("Failed","You don't have enough money for one fighter, let alone a fleet of them.<br>Come back when you can afford it");
		} elseif(!$maths[0]) {
			print_page("Failed","This operation failed as there are no ships that have fighter bays empty in this system that belong to you.");
		} elseif($sure != "yes") {
		get_var('Load all ships','equip_shop.php',"There are <b>$maths[0]</b> empty fighter bays in <b>$maths[1]</b> ships in this system. <br>Do you want to fill as many as you can afford to fill?",'sure','yes');
		} else {
			db2("select ship_id,fighters,max_fighters,ship_name from ${db_name}_ships where login_id = '$user[login_id]' && location = '1' && max_fighters > 0 && fighters < max_fighters order by max_fighters desc");
			while($ships = dbr2()) {
				//player can load ship.
				$free = $ships[max_fighters] - $ships[fighters];
				if($user[cash] >= ($free * $fighter_cost)) {
					$ship_counter++;
					dbn("update ${db_name}_ships set fighters = max_fighters where ship_id = '$ships[ship_id]'");
					$out .= "<br><b class=b1>$ships[ship_name]</b> had its fighter cargo increased by <b>$free</b> to maximum capacity.";
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[fighters] = $user_ship[max_fighters];
					}
					$taken += $free;
					take_cash($free*$fighter_cost);
				//player will run out of cash.
				} else {
					$ship_counter++;
					$t868 = $ships[fighters] + floor($user[cash]/$fighter_cost);
					dbn("update ${db_name}_ships set fighters = '$t868' where ship_id = '$ships[ship_id]'");
					if($ships[ship_id] == $user_ship[ship_id]){
						$user_ship[fighters] = $t868;
					}
					$taken += $t868 - $ships[fighters];
					$q_m = ($t868 - $ships[fighters]) *$fighter_cost;
					$out .= "<br><b class=b1>$ships[ship_name]</b>s fighter count was increased to <b>$t868</b>.";
					take_cash($q_m);
					break;
				}
			}
			if($ship_counter > 0){
				$cost=$taken*$fighter_cost;
				print_page("Fighters Loaded","<b>$ship_counter</b> ships had their fighters augmented by new fighters from Sol.<br>Total New Fighters = <b>$taken</b>; Cost = <b>$cost</b><p>More Detailed Statistics :".$out);
			} else {
				print_page("No Ships","No ships where loaded as all ships in this system are already full of fighters.");
			}
		}
	}

} elseif(isset($fill_fleet)) { //fill fleet functionality

	if($fill_fleet == 1){ //fighters
		$error_str .= fill_fleet("fighters", "max_fighters", "Fighters", $fighter_cost, $filename);

	} else { //shields
		$error_str .= fill_fleet("shields", "max_shields", "Shields", $shield_cost, $filename);

	}
}


$error_str .= "<p>Equipment Shop:";
$error_str .= "<p>Ship Stuff:";
$error_str .= "<p>Ship Equipment:";
$error_str .= "<br><a href=$filename?buy=1>Fighters</a> - Cost: <b>$fighter_cost</b> each - <a href=$filename?fill_fleet=1>Fill Fleet</a>";
$error_str .= "<br><a href=$filename?buy=2>Shields</a> - Cost: <b>$shield_cost</b> each - <a href=$filename?fill_fleet=2>Fill Fleet</a>";

if($alternate_play_1 == 1){
	$error_str .= "<br><a href=$filename?switch=1>Mining Switcher</a> $mining_switch_cost - <a href=$filename?mass_switch=1>Switch Fleet</a>";
}

if($uv_planets == -1 || $user[login_id] == 1){
	$error_str .= "<p>Planet Stuff:";
	$error_str .= "<br><a href=$filename?buy=5>Genesis Device</a>: $genesis_cost";
}

if ((!$flag_bomb) || ($user[login_id] ==1)) {
	$error_str .= "<p>Bombs:";
	$error_str .= "<br><a href=$filename?buy=7>Alpha Bomb</a>: $bomb_cost";
	$error_str .= "<br><a href=$filename?buy=6>Gamma Bomb</a>: $bomb_cost";
	if($random_events == 3) {
		$error_str .= "<br><a href=$filename?buy=9>SuperNova Effector</a>: $sn_cost";
	}
}
/*if (($transwarp_cost) || ($user[login_id] ==1)) {
	$error_str .= "<br><a href=$filename?buy=7>Transwarp Drive</a>: $transwarp_cost";
}*/

$error_str .= "<p><a href=help.php?equip=1 target=_blank>Information about equipment</a>";

$rs = "<p><a href=earth.php>Return to Earth</a>";


print_page("Equipment Shop",$error_str);
?>