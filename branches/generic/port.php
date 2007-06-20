<?php

require_once('inc/user.inc.php');

$filename = "port.php";

sudden_death_check($user);

db("select * from ${db_name}_ports where location = '$user[location]'");
$port = dbr();

if (!$port) {
	print_page("Port","You may not sell at a port that is not in the same system as you are in. Stop playing with the URL's'");
}


$metal_buy = $buy_metal + $port['metal_bonus'];
$metal_sell = $buy_metal - round(($buy_metal/100)*20) + $port['metal_bonus'];
$fuel_buy = $buy_fuel + $port['fuel_bonus'];
$fuel_sell = $buy_fuel - round(($buy_fuel/100)*20) + $port['fuel_bonus'];
$elect_buy = $buy_elect + $port['elect_bonus'];
$elect_sell = $buy_elect - round(($buy_elect/100)*20) + $port['elect_bonus'];
$organ_buy = $buy_organ + $port['organ_bonus'];
$organ_sell = $buy_organ - round(($buy_organ/100)*20) + $port['organ_bonus'];

$rs = "<p><a href=port.php>Return to Starport</a>";


settype($amount, "integer");
$amount = round($amount);
$error_str = "";


#Resource Trading
if(isset($deal)) {
	if($deal == 1){
		$resource_deal = "metal";
		$resource_str = "Metal";
		$buy_cost = $metal_buy;
		$sell_cost = $metal_sell;
	} elseif($deal == 2){
		$resource_deal = "fuel";
		$resource_str = "Fuel";
		$buy_cost = $fuel_buy;
		$sell_cost = $fuel_sell;
	} elseif($deal == 3){
		$resource_deal = "elect";
		$resource_str = "Electronics";
		$buy_cost = $elect_buy;
		$sell_cost = $elect_sell;
	} elseif($deal == 4){
		$resource_deal = "organ";
		$resource_str = "Organics";
		$buy_cost = $organ_buy;
		$sell_cost = $organ_sell;
	} else {
		echo "There is no such material.";
	}

	#fleet fill with a mineral
	if($buy_sell == 3) {

		$max = "(cargo_bays-metal-fuel-elect-organ-colon)";

		if($alternate_play_1 == 1 && ($deal == 1 || $deal == 2)) {
			print_page("Error","Alternate style of play in action. You may not buy metal or fuel. You must mine all the metal or fuel you plan on using.");
		} else {
			$error_str .= fill_fleet($resource_deal, $max, $resource_str, $buy_cost, $filename, 1)."<p>";
		}


	#find out how much of the material the user wants to deal in.
	} elseif($amount < 1) {
		if($buy_sell == 0) { #buy
			#figure out max capacity.
			$def = floor($user['cash'] / $metal_buy);
			if($def > $user_ship['empty_bays']) {
				$def = $user_ship['empty_bays'];
			}

			#not allowed to buy.
			if($alternate_play_1 == 1 && ($deal == 1 || $deal == 2)) {
				print_page("Error","Alternate style of play in action. You may not buy metal or fuel. You must mine all the metal or fuel you plan on using.");
			} elseif ($user_ship['empty_bays'] > 0) {
				get_var("Buy $resource_str",$filename,"How much $resource_str do you want to buy?",'amount',$def);
			} else { #no cargo cap
				$error_str .= "You have no cargo capacity left on this ship. As such you cannot buy anything.<p>";
			}

		} elseif ($user_ship[$resource_deal]) {#sell commodity
			get_var("Sell $resource_str",$filename,"How much $resource_str do you want to sell?",'amount',$user_ship[$resource_deal]);
		} else { #no commodity to sell
			$error_str .= "You have no $resource_str to sell.<p>";
		}

	} else { #user has entered amount of resource to play with

		if($buy_sell == 0) { # buy continued
			if(($amount * $buy_cost > $user['cash']) && $user['login_id'] != ADMIN_ID) {
				$error_str .= "You cannot afford that much $resource_str.<p>";
			} elseif($alternate_play_1 == 1 && ($deal == 1 || $deal == 2)) {
				print_page("Error","Alternate style of play in action. You may not buy metal or fuel. You must mine all the metal or fuel you plan on using.");
			} elseif($amount > $user_ship['empty_bays']) {
				$error_str .= "Your ship can not hold that much $resource_str.<p>";
			} else {
				take_cash($amount * $buy_cost);
				dbn("update ${db_name}_ships set $resource_deal = $resource_deal + $amount where ship_id = $user[ship_id]");
				$user_ship[$resource_deal] += $amount;
				$error_str .= "You have purchased <b>$amount</b> units of $resource_str, for the sum of <b>".$amount*$buy_cost."</b> Credits.<p>";
			}

		} elseif($buy_sell == 1) { #sell metal
			if($amount > $user_ship[$resource_deal]) {
				$error_str .= "You do not have that much $resource_str.<p>";
			} else {
				give_cash($amount * $sell_cost);
				dbn("update ${db_name}_ships set $resource_deal = $resource_deal - $amount where ship_id = $user[ship_id]");
				$user_ship[$resource_deal] -= $amount;
				$error_str .= "You sold <b>$amount</b> units of $resource_str, for the sum of <b>".$amount*$sell_cost."</b> Credits.<p>";
			}
		}
	}
}


#user wants to sell all
if(isset($sell_all)) {
	$elect_sold = 0;
	$fuel_sold = 0;
	$metal_sold = 0;
	$organ_sold = 0;

	if(isset($all_ships)) {#all being sold from all ships
		$sold_worth = 0;
		$ship_count = 0;
		db("select elect,fuel,metal,organ,ship_id from ${db_name}_ships where location = $user[location] and login_id = $user[login_id]");
		while ($current_ship = dbr()) {
			$sold_worth += (($current_ship['elect'] * $elect_sell) + ($current_ship['fuel'] * $fuel_sell) + ($current_ship['metal'] * $metal_sell) + ($current_ship['organ'] * $organ_sell));
			$elect_sold = $elect_sold + $current_ship['elect'];
			$fuel_sold = $fuel_sold + $current_ship['fuel'];
			$metal_sold = $metal_sold + $current_ship['metal'];
			$organ_sold = $organ_sold + $current_ship['organ'];
			if($current_ship['elect'] || $current_ship['metal'] || $current_ship['organ'] || $current_ship['fuel']) {
				$ship_count++;
			}
		}
			if ($sold_worth < 1) {
			print_page("Port","You do not have any cargo in any ships in this star system.");
		} elseif($ship_count == 1 && $user['turns'] > 0){
			header('Location: port.php?sell_all=1&changed=1');
			exit();
		} elseif ($user['turns'] < 5) {
			print_page("Port","You do not have enough turns to trade using this method. You will have to sell everything manually.");
		} elseif(!isset($sure)) {
			get_var('Sell all cargo',$filename,"Are you sure you want to sell all cargo from all your ships currently in this star system with cargo (<b>$ship_count</b> of them)?<p>This will cost you <b>5</b> turns and will generate revenues of about <b>$sold_worth</b> Credits.",'sure','yes');
		} else {
			dbn("update ${db_name}_ships set elect = 0, metal = 0, fuel = 0, organ = 0 where location = '$user[location]' && login_id = '$user[login_id]' && cargo_bays > 0");
			charge_turns(5);
			$error_str .= "All cargo from all ships in this star system sold.";
			$error_str .= "<p>Metal Sold: <b>$metal_sold</b><br>Fuel Sold: <b>$fuel_sold</b><br>Electronics Sold: <b>$elect_sold</b><br>Organics Sold: <b>$organ_sold</b>";
			$error_str .= "<p>Total goods sold: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold + $organ_sold;
			$error_str .= "$total_goods</b>";
			$error_str .= "<br>Total income: <b>$sold_worth</b>.";
			$error_str .= "<br>From <b>$ship_count</b> ship(s).<p>";
		}

	} else { #all being sold from just the present ship.
		$sold_worth = (($user_ship['elect'] * $elect_sell) + ($user_ship['fuel'] * $fuel_sell) + ($user_ship['metal'] * $metal_sell) + ($user_ship['organ'] * $organ_sell));
		if ($user['turns'] < 1) {
			print_page("Port","You do not have enough turns to trade using this method. You will have to sell everything manually.");
			} elseif ($sold_worth < 1) {
			print_page("Port","This ship has no cargo that can be sold. Try a selling from a different ship.");
		} elseif(!isset($sure)) {
			if(isset($changed)){
				get_var('Sell all cargo',$filename,"As you only have one ship in this system with cargo to sell you have been re-directed to the <b class=b1>sell from one ship</b> facility.<br>This will save you <b>4</b> turns. <p>Are you sure you want to sell all cargo from this ship?<p>This will cost you <b>1</b> turn, and add <b>$sold_worth</b> Credits to your funds.",'sure','yes');
			} else {
				get_var('Sell all cargo',$filename,"Are you sure you want to sell all cargo from this ship?<p>This will cost you <b>1</b> turn, and add <b>$sold_worth</b> Credits to your funds.",'sure','yes');
			}
		} else {
			dbn("update ${db_name}_ships set elect = 0, metal = 0, fuel = 0, organ = 0 where ship_id = '$user[ship_id]'");
			$elect_sold = $elect_sold + $user_ship['elect'];
			$fuel_sold = $fuel_sold + $user_ship['fuel'];
			$metal_sold = $metal_sold + $user_ship['metal'];
			$organ_sold = $organ_sold + $user_ship['organ'];
#			if ($user_ship[metal] > 0) { $error_str .= "You sold $user_ship[metal] units of metal.<p>"; }
#			if ($user_ship[fuel] > 0) { $error_str .= "You sold $user_ship[fuel] units of fuel.<p>";
#			if ($user_ship[elect] > 0) { $error_str .= "You sold $user_ship[elect] units of electronics.<p>";
	 		charge_turns(1);
			$error_str .= "All cargo from this ship sold.<p>";
			$error_str .= "<p>Metal Sold: <b>$metal_sold</b><br>Fuel Sold: <b>$fuel_sold</b><br>Electronics Sold: <b>$elect_sold</b><br>Organics Sold: <b>$organ_sold</b>";
			$error_str .= "<p>Total goods sold: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold + $organ_sold;
			$error_str .= "$total_goods</b>";
			$error_str .= "<br>Total income: <b>$sold_worth</b>.<p>";
		}
	}
	if ($user['login_id'] != ADMIN_ID) {
		dbn("update ${db_name}_users set cash = cash + $sold_worth where login_id = $user[login_id]");
		$user['cash'] += $sold_worth;
	}
		$user_ship['metal'] = 0;
		$user_ship['fuel'] = 0;
		$user_ship['elect'] = 0;
	$user_ship['organ'] = 0;
#		$user_ship[colon] = 0;
}


empty_bays($user_ship);

// print page
$error_str .= "Starport in system #<b>$port[location]</b>";

$error_str .= "<p><b class=b1>Metal</b>";
if($alternate_play_1 == 0) { #can't buy metal in this style of play.
	$error_str .= "<br><a href=port.php?deal=1&buy_sell=0>Buy</a> - <b>$metal_buy</b> - <a href=port.php?deal=1&buy_sell=3>Fill Fleet</a>";
}
$error_str .= "<br><a href=port.php?deal=1&buy_sell=1>Sell</a> - <b>$metal_sell</b>";

$error_str .= "<p><b class=b1>Fuel</b>";

if($alternate_play_1 == 0) { #can't buy fuel in this style of play.
	$error_str .= "<br><a href=port.php?deal=2&buy_sell=0>Buy</a> - <b>$fuel_buy</b> - <a href=port.php?deal=2&buy_sell=3>Fill Fleet</a>";
}
$error_str .= "<br><a href=port.php?deal=2&buy_sell=1>Sell</a> - <b>$fuel_sell</b>";

$error_str .= "<p><b class=b1>Electronics</b>";
$error_str .= "<br><a href=port.php?deal=3&buy_sell=0>Buy</a> - <b>$elect_buy</b> - <a href=port.php?deal=3&buy_sell=3>Fill Fleet</a>";
$error_str .= "<br><a href=port.php?deal=3buy_sell=1>Sell</a> - <b>$elect_sell</b>";

$error_str .= "<p><b class=b1>Organics</b>";
$error_str .= "<br><a href=port.php?deal=4&buy_sell=0>Buy</a> - <b>$organ_buy</b> - <a href=port.php?deal=4&buy_sell=3>Fill Fleet</a>";
$error_str .= "<br><a href=port.php?deal=4&buy_sell=1>Sell</a> - <b>$organ_sell</b>";

$error_str .= "<p><a href=port.php?sell_all=1>Sell All</a>";
$error_str .= "<p><a href=port.php?sell_all=1&all_ships=1>Sell All from All Ships</a>";
if($port['location'] != 1){
	$error_str .= "<p>Teleconferance To <a href=bilkos.php>Bilkos Auction House</a>";
}

//$error_str .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

$rs = "<p><a href=location.php>Takeoff</a><br>";

print_page("Port",$error_str);
?>
