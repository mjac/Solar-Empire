<?php

require_once('inc/user.inc.php');

if (deathCheck($user) || $userShip === NULL) {
	print_page('Port', 'You do not have a ship!');
}

if (!isset($port_id)) {
	print_page('Port', 'Dock at which port?');
}

$pInfo = $db->query('SELECT * FROM [game]_ports WHERE location = %u AND ' .
 'port_id = %u', array($userShip['location'], $port_id));
$port = $db->fetchRow($pInfo);

if (empty($port)) {
	print_page('Port', 'That port is not in this system.');
}

$metal_buy = $gameOpt['buy_metal'] + $port['metal_bonus'];
$metal_sell = $gameOpt['buy_metal'] - round(($gameOpt['buy_metal'] / 100) * 20) + $port['metal_bonus'];
$fuel_buy = $gameOpt['buy_fuel'] + $port['fuel_bonus'];
$fuel_sell = $gameOpt['buy_fuel'] - round(($gameOpt['buy_fuel'] / 100) * 20) + $port['fuel_bonus'];
$elect_buy = $gameOpt['buy_elect'] + $port['elect_bonus'];
$elect_sell = $gameOpt['buy_elect'] - round(($gameOpt['buy_elect'] / 100) * 20) + $port['elect_bonus'];
$organ_buy = $gameOpt['buy_organ'] + $port['organ_bonus'];
$organ_sell = $gameOpt['buy_organ'] - round(($gameOpt['buy_organ'] / 100) * 20) + $port['organ_bonus'];

$rs = "<p><a href=shop_port.php>Return to Starport</a></p>\n";


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
		$error_str .= fill_fleet($resource_deal, $max, $resource_str, $buy_cost, $self, 1)."<p>";


	#find out how much of the material the user wants to deal in.
	} elseif($amount < 1) {
		if($buy_sell == 0) { #buy
			#figure out max capacity.
			$def = floor($user['cash'] / $metal_buy);
			if($def > $userShip['empty_bays']) {
				$def = $userShip['empty_bays'];
			}

			#not allowed to buy.
			if ($userShip['empty_bays'] > 0) {
				get_var("Buy $resource_str",$self,"How much $resource_str do you want to buy?",'amount',$def);
			} else { #no cargo cap
				$error_str .= "You have no cargo capacity left on this ship. As such you cannot buy anything.<p>";
			}

		} elseif ($userShip[$resource_deal]) {#sell commodity
			get_var("Sell $resource_str",$self,"How much $resource_str do you want to sell?",'amount',$userShip[$resource_deal]);
		} else { #no commodity to sell
			$error_str .= "You have no $resource_str to sell.<p>";
		}

	} else { #user has entered amount of resource to play with

		if($buy_sell == 0) { # buy continued
			$total = $amount * $buy_cost;
			if ($amount > $userShip['empty_bays']) {
				$error_str .= "Your ship can not hold that much $resource_str.<p>";
			} elseif (!giveMoneyPlayer(-$total)) {
				$error_str .= "<p>You need $total credits for $amount $resource_str.</p>\n";
			} else {
				$db->query("update [game]_ships set $resource_deal = $resource_deal + $amount where ship_id = $user[ship_id]");
				$userShip[$resource_deal] += $amount;
				$error_str .= "You have purchased <b>$amount</b> units of $resource_str, for the sum of <b>$total</b> Credits.<p>";
			}

		} elseif($buy_sell == 1) { #sell metal
			if($amount > $userShip[$resource_deal]) {
				$error_str .= "You do not have that much $resource_str.<p>";
			} else {
				giveMoneyPlayer($amount * $sell_cost);
				$db->query("update [game]_ships set $resource_deal = $resource_deal - $amount where ship_id = $user[ship_id]");
				$userShip[$resource_deal] -= $amount;
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
		$ships = $db->query("select elect,fuel,metal,organ,ship_id from [game]_ships where location = $userShip[location] and login_id = $user[login_id]");
		while ($current_ship = $db->fetchRow($ships)) {
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
			header('Location: shop_port.php?sell_all=1&changed=1');
			exit();
		} elseif ($user['turns'] < 5) {
			print_page("Port","You do not have enough turns to trade using this method. You will have to sell everything manually.");
		} elseif(!isset($sure)) {
			get_var('Sell all cargo',$self,"Are you sure you want to sell all cargo from all your ships currently in this star system with cargo (<b>$ship_count</b> of them)?<p>This will cost you <b>5</b> turns and will generate revenues of about <b>$sold_worth</b> Credits.",'sure','yes');
		} else {
			$db->query("update [game]_ships set elect = 0, metal = 0, fuel = 0, organ = 0 where location = '$userShip[location]' AND login_id = '$user[login_id]' AND cargo_bays > 0");
			giveTurnsPlayer(-5);
			$error_str .= "All cargo from all ships in this star system sold.";
			$error_str .= "<p>Metal Sold: <b>$metal_sold</b><br />Fuel Sold: <b>$fuel_sold</b><br />Electronics Sold: <b>$elect_sold</b><br />Organics Sold: <b>$organ_sold</b>";
			$error_str .= "<p>Total goods sold: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold + $organ_sold;
			$error_str .= "$total_goods</b>";
			$error_str .= "<br />Total income: <b>$sold_worth</b>.";
			$error_str .= "<br />From <b>$ship_count</b> ship(s).<p>";
		}

	} else { #all being sold from just the present ship.
		$sold_worth = (($userShip['elect'] * $elect_sell) + ($userShip['fuel'] * $fuel_sell) + ($userShip['metal'] * $metal_sell) + ($userShip['organ'] * $organ_sell));
		if ($user['turns'] < 1) {
			print_page("Port","You do not have enough turns to trade using this method. You will have to sell everything manually.");
			} elseif ($sold_worth < 1) {
			print_page("Port","This ship has no cargo that can be sold. Try a selling from a different ship.");
		} elseif(!isset($sure)) {
			if(isset($changed)){
				get_var('Sell all cargo',$self,"As you only have one ship in this system with cargo to sell you have been re-directed to the <b class=b1>sell from one ship</b> facility.<br />This will save you <b>4</b> turns. <p>Are you sure you want to sell all cargo from this ship?<p>This will cost you <b>1</b> turn, and add <b>$sold_worth</b> Credits to your funds.",'sure','yes');
			} else {
				get_var('Sell all cargo',$self,"Are you sure you want to sell all cargo from this ship?<p>This will cost you <b>1</b> turn, and add <b>$sold_worth</b> Credits to your funds.",'sure','yes');
			}
		} else {
			dbn("update [game]_ships set elect = 0, metal = 0, fuel = 0, organ = 0 where ship_id = '$user[ship_id]'");
			$elect_sold = $elect_sold + $userShip['elect'];
			$fuel_sold = $fuel_sold + $userShip['fuel'];
			$metal_sold = $metal_sold + $userShip['metal'];
			$organ_sold = $organ_sold + $userShip['organ'];
#			if ($userShip[metal] > 0) { $error_str .= "You sold $userShip[metal] units of metal.<p>"; }
#			if ($userShip[fuel] > 0) { $error_str .= "You sold $userShip[fuel] units of fuel.<p>";
#			if ($userShip[elect] > 0) { $error_str .= "You sold $userShip[elect] units of electronics.<p>";
	 		giveTurnsPlayer(-1);
			$error_str .= "All cargo from this ship sold.<p>";
			$error_str .= "<p>Metal Sold: <b>$metal_sold</b><br />Fuel Sold: <b>$fuel_sold</b><br />Electronics Sold: <b>$elect_sold</b><br />Organics Sold: <b>$organ_sold</b>";
			$error_str .= "<p>Total goods sold: <b>";
			$total_goods = $metal_sold + $fuel_sold + $elect_sold + $organ_sold;
			$error_str .= "$total_goods</b>";
			$error_str .= "<br />Total income: <b>$sold_worth</b>.<p>";
		}
	}
	if (!IS_ADMIN) {
		dbn("update [game]_users set cash = cash + $sold_worth where login_id = $user[login_id]");
		$user['cash'] += $sold_worth;
	}
	$userShip['metal'] = 0;
	$userShip['fuel'] = 0;
	$userShip['elect'] = 0;
	$userShip['organ'] = 0;
#		$userShip[colon] = 0;
}


empty_bays($userShip);

// print page
$error_str .= <<<END
<h1>Galactic port $port[port_id], system #$port[location]</h1>
<p><img src="img/places/port.jpg" alt="Galactic port" /></p>
<p>Teleconference to the galactic <a href="shop_auction_house.php">auction house</a></p>

<h2>All cargo</h2>
<p><a href="shop_port.php?sell_all=1&amp;port_id=$port_id">Sell everything</a>
(<a href="shop_port.php?sell_all=1&amp;all_ships=1&amp;port_id=$port_id">all ships</a>)</p>

<h2>Metal</h2>
<p><a href="shop_port.php?deal=1&amp;buy_sell=0&amp;port_id=$port_id">Buy</a> for
<em>$metal_buy</em> - <a href="shop_port.php?deal=1&amp;buy_sell=3&amp;port_id=$port_id">Fill Fleet</a></p>
<p><a href="shop_port.php?deal=1&amp;buy_sell=1&amp;port_id=$port_id">Sell</a> for
<em>$metal_sell</em></p>

<h2>Fuel</h2>
<p><a href="shop_port.php?deal=2&amp;buy_sell=0&amp;port_id=$port_id">Buy</a> for <em>$fuel_buy</em> -
<a href="shop_port.php?deal=2&amp;buy_sell=3&amp;port_id=$port_id">Fill Fleet</a></p>
<p><a href="shop_port.php?deal=2&amp;buy_sell=1&amp;port_id=$port_id">Sell</a> for <em>$fuel_sell</em></p>

<h2>Electronics</h2>
<p><a href="shop_port.php?deal=3&amp;buy_sell=0&amp;port_id=$port_id">Buy</a> for <em>$elect_buy</em> -
<a href="shop_port.php?deal=3&amp;buy_sell=3&amp;port_id=$port_id">Fill Fleet</a></p>
<p><a href="shop_port.php?deal=3&amp;buy_sell=1&amp;port_id=$port_id">Sell</a> for <em>$elect_sell</em></p>

<h2>Organics</h2>
<p><a href="shop_port.php?deal=4&amp;buy_sell=0&amp;port_id=$port_id">Buy</a> for <em>$organ_buy</em> -
<a href="shop_port.php?deal=4&amp;buy_sell=3&amp;port_id=$port_id">Fill Fleet</a></p>
<p><a href="shop_port.php?deal=4&amp;buy_sell=1&amp;port_id=$port_id">Sell</a> for <em>$organ_sell</em></p>

<h2><a href="system.php">Back to the stars</a></h2>

END;

print_page("Port",$error_str);
?>
